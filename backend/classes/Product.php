<?php
/**
 * Product Management Class
 * Handles product CRUD operations, search, and catalog management
 */

require_once 'Database.php';
require_once 'Security.php';

class Product {
    private $db;
    private $security;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->security = new Security();
    }

    /**
     * Create new product
     */
    public function create($sellerId, $productData) {
        try {
            $this->validateProductData($productData);

            // Generate slug
            $slug = $this->generateSlug($productData['name']);

            $insertData = [
                'seller_id' => $sellerId,
                'category_id' => $productData['category_id'],
                'name' => trim($productData['name']),
                'name_amharic' => isset($productData['name_amharic']) ? trim($productData['name_amharic']) : null,
                'slug' => $slug,
                'short_description' => isset($productData['short_description']) ? trim($productData['short_description']) : null,
                'description' => isset($productData['description']) ? trim($productData['description']) : null,
                'price' => $this->security->validateAmount($productData['price']),
                'compare_price' => isset($productData['compare_price']) ? $this->security->validateAmount($productData['compare_price']) : null,
                'cost_price' => isset($productData['cost_price']) ? $this->security->validateAmount($productData['cost_price']) : null,
                'sku' => isset($productData['sku']) ? trim($productData['sku']) : $this->generateSKU(),
                'stock_quantity' => isset($productData['stock_quantity']) ? (int)$productData['stock_quantity'] : 0,
                'low_stock_threshold' => isset($productData['low_stock_threshold']) ? (int)$productData['low_stock_threshold'] : 5,
                'weight' => isset($productData['weight']) ? (float)$productData['weight'] : null,
                'dimensions' => isset($productData['dimensions']) ? json_encode($productData['dimensions']) : null,
                'images' => isset($productData['images']) ? json_encode($productData['images']) : null,
                'video_url' => isset($productData['video_url']) ? trim($productData['video_url']) : null,
                'status' => isset($productData['status']) ? $productData['status'] : 'draft',
                'is_featured' => isset($productData['is_featured']) ? (bool)$productData['is_featured'] : false,
                'is_digital' => isset($productData['is_digital']) ? (bool)$productData['is_digital'] : false,
                'requires_shipping' => isset($productData['requires_shipping']) ? (bool)$productData['requires_shipping'] : true,
                'tags' => isset($productData['tags']) ? json_encode($productData['tags']) : null,
                'attributes' => isset($productData['attributes']) ? json_encode($productData['attributes']) : null,
                'seo_title' => isset($productData['seo_title']) ? trim($productData['seo_title']) : null,
                'seo_description' => isset($productData['seo_description']) ? trim($productData['seo_description']) : null
            ];

            $productId = $this->db->insert('products', $insertData);

            // Create variants if provided
            if (isset($productData['variants']) && is_array($productData['variants'])) {
                foreach ($productData['variants'] as $variant) {
                    $this->createVariant($productId, $variant);
                }
            }

            return [
                'success' => true,
                'product_id' => $productId,
                'message' => 'Product created successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Update product
     */
    public function update($productId, $sellerId, $productData) {
        try {
            // Verify product belongs to seller
            $product = $this->getById($productId);
            if (!$product || $product['seller_id'] != $sellerId) {
                throw new Exception('Product not found or access denied');
            }

            $allowedFields = [
                'category_id', 'name', 'name_amharic', 'short_description', 'description',
                'price', 'compare_price', 'cost_price', 'sku', 'stock_quantity',
                'low_stock_threshold', 'weight', 'dimensions', 'images', 'video_url',
                'status', 'is_featured', 'is_digital', 'requires_shipping', 'tags',
                'attributes', 'seo_title', 'seo_description'
            ];

            $updateData = [];
            foreach ($allowedFields as $field) {
                if (isset($productData[$field])) {
                    if (in_array($field, ['dimensions', 'images', 'tags', 'attributes'])) {
                        $updateData[$field] = json_encode($productData[$field]);
                    } elseif (in_array($field, ['price', 'compare_price', 'cost_price'])) {
                        $updateData[$field] = $this->security->validateAmount($productData[$field]);
                    } else {
                        $updateData[$field] = $productData[$field];
                    }
                }
            }

            // Update slug if name changed
            if (isset($productData['name'])) {
                $updateData['slug'] = $this->generateSlug($productData['name'], $productId);
            }

            $updateData['updated_at'] = date('Y-m-d H:i:s');

            $this->db->update('products', $updateData, 'id = ?', [$productId]);

            return [
                'success' => true,
                'message' => 'Product updated successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get product by ID
     */
    public function getById($productId, $includeInactive = false) {
        $whereClause = 'id = ?';
        $params = [$productId];

        if (!$includeInactive) {
            $whereClause .= ' AND status != "inactive"';
        }

        $product = $this->db->fetchOne("SELECT * FROM products WHERE {$whereClause}", $params);

        if ($product) {
            $product = $this->formatProduct($product);
        }

        return $product;
    }

    /**
     * Get product by slug
     */
    public function getBySlug($slug) {
        $product = $this->db->fetchOne(
            "SELECT * FROM products WHERE slug = ? AND status = 'active'",
            [$slug]
        );

        if ($product) {
            $product = $this->formatProduct($product);
            // Increment views
            $this->incrementViews($product['id']);
        }

        return $product;
    }

    /**
     * Get products with filters
     */
    public function getProducts($filters = [], $page = 1, $limit = null) {
        $limit = $limit ?: PRODUCTS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        $whereConditions = ['status = "active"'];
        $params = [];

        // Apply filters
        if (isset($filters['category_id'])) {
            $whereConditions[] = 'category_id = ?';
            $params[] = $filters['category_id'];
        }

        if (isset($filters['seller_id'])) {
            $whereConditions[] = 'seller_id = ?';
            $params[] = $filters['seller_id'];
        }

        if (isset($filters['price_min'])) {
            $whereConditions[] = 'price >= ?';
            $params[] = $filters['price_min'];
        }

        if (isset($filters['price_max'])) {
            $whereConditions[] = 'price <= ?';
            $params[] = $filters['price_max'];
        }

        if (isset($filters['search'])) {
            $whereConditions[] = '(name LIKE ? OR description LIKE ? OR tags LIKE ?)';
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (isset($filters['is_featured'])) {
            $whereConditions[] = 'is_featured = ?';
            $params[] = $filters['is_featured'];
        }

        if (isset($filters['in_stock'])) {
            $whereConditions[] = 'stock_quantity > 0';
        }

        $whereClause = implode(' AND ', $whereConditions);

        // Order by
        $orderBy = 'created_at DESC';
        if (isset($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_low':
                    $orderBy = 'price ASC';
                    break;
                case 'price_high':
                    $orderBy = 'price DESC';
                    break;
                case 'rating':
                    $orderBy = 'rating DESC';
                    break;
                case 'popular':
                    $orderBy = 'total_sales DESC';
                    break;
                case 'newest':
                    $orderBy = 'created_at DESC';
                    break;
            }
        }

        // Get products
        $sql = "SELECT * FROM products WHERE {$whereClause} ORDER BY {$orderBy} LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $products = $this->db->fetchAll($sql, $params);

        // Format products
        $formattedProducts = [];
        foreach ($products as $product) {
            $formattedProducts[] = $this->formatProduct($product);
        }

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM products WHERE {$whereClause}";
        $countParams = array_slice($params, 0, -2); // Remove limit and offset
        $totalResult = $this->db->fetchOne($countSql, $countParams);
        $total = $totalResult['total'];

        return [
            'products' => $formattedProducts,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ];
    }

    /**
     * Search products
     */
    public function search($query, $filters = [], $page = 1, $limit = null) {
        // Log search query
        $this->logSearchQuery($query);

        $filters['search'] = $query;
        return $this->getProducts($filters, $page, $limit);
    }

    /**
     * Get related products
     */
    public function getRelatedProducts($productId, $limit = 6) {
        $product = $this->getById($productId);
        if (!$product) {
            return [];
        }

        $sql = "SELECT * FROM products 
                WHERE category_id = ? 
                AND id != ? 
                AND status = 'active' 
                ORDER BY rating DESC, total_sales DESC 
                LIMIT ?";

        $products = $this->db->fetchAll($sql, [$product['category_id'], $productId, $limit]);

        $formattedProducts = [];
        foreach ($products as $prod) {
            $formattedProducts[] = $this->formatProduct($prod);
        }

        return $formattedProducts;
    }

    /**
     * Get seller products
     */
    public function getSellerProducts($sellerId, $page = 1, $limit = null) {
        return $this->getProducts(['seller_id' => $sellerId], $page, $limit);
    }

    /**
     * Update stock quantity
     */
    public function updateStock($productId, $quantity, $operation = 'set') {
        try {
            $product = $this->getById($productId, true);
            if (!$product) {
                throw new Exception('Product not found');
            }

            $newQuantity = $quantity;
            if ($operation === 'add') {
                $newQuantity = $product['stock_quantity'] + $quantity;
            } elseif ($operation === 'subtract') {
                $newQuantity = $product['stock_quantity'] - $quantity;
            }

            if ($newQuantity < 0) {
                throw new Exception('Insufficient stock');
            }

            $updateData = ['stock_quantity' => $newQuantity];

            // Update status based on stock
            if ($newQuantity == 0) {
                $updateData['status'] = 'out_of_stock';
            } elseif ($product['status'] == 'out_of_stock' && $newQuantity > 0) {
                $updateData['status'] = 'active';
            }

            $this->db->update('products', $updateData, 'id = ?', [$productId]);

            return [
                'success' => true,
                'new_quantity' => $newQuantity
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create product variant
     */
    public function createVariant($productId, $variantData) {
        $insertData = [
            'product_id' => $productId,
            'name' => trim($variantData['name']),
            'value' => trim($variantData['value']),
            'price_adjustment' => isset($variantData['price_adjustment']) ? $variantData['price_adjustment'] : 0,
            'stock_quantity' => isset($variantData['stock_quantity']) ? (int)$variantData['stock_quantity'] : 0,
            'sku' => isset($variantData['sku']) ? trim($variantData['sku']) : null,
            'image' => isset($variantData['image']) ? trim($variantData['image']) : null,
            'sort_order' => isset($variantData['sort_order']) ? (int)$variantData['sort_order'] : 0
        ];

        return $this->db->insert('product_variants', $insertData);
    }

    /**
     * Get product variants
     */
    public function getVariants($productId) {
        return $this->db->fetchAll(
            "SELECT * FROM product_variants WHERE product_id = ? AND is_active = 1 ORDER BY sort_order ASC",
            [$productId]
        );
    }

    /**
     * Delete product (soft delete)
     */
    public function delete($productId, $sellerId) {
        try {
            $product = $this->getById($productId, true);
            if (!$product || $product['seller_id'] != $sellerId) {
                throw new Exception('Product not found or access denied');
            }

            $this->db->update('products', 
                ['status' => 'inactive', 'updated_at' => date('Y-m-d H:i:s')], 
                'id = ?', 
                [$productId]
            );

            return [
                'success' => true,
                'message' => 'Product deleted successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Increment product views
     */
    private function incrementViews($productId) {
        $this->db->query(
            "UPDATE products SET views_count = views_count + 1 WHERE id = ?",
            [$productId]
        );
    }

    /**
     * Generate unique slug
     */
    private function generateSlug($name, $excludeId = null) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $whereClause = 'slug = ?';
            $params = [$slug];

            if ($excludeId) {
                $whereClause .= ' AND id != ?';
                $params[] = $excludeId;
            }

            if (!$this->db->exists('products', $whereClause, $params)) {
                break;
            }

            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Generate SKU
     */
    private function generateSKU() {
        do {
            $sku = 'EM-' . strtoupper($this->security->generateToken(4));
        } while ($this->db->exists('products', 'sku = ?', [$sku]));

        return $sku;
    }

    /**
     * Validate product data
     */
    private function validateProductData($data) {
        if (empty($data['name']) || strlen(trim($data['name'])) < 3) {
            throw new Exception('Product name must be at least 3 characters long');
        }

        if (empty($data['category_id']) || !is_numeric($data['category_id'])) {
            throw new Exception('Valid category is required');
        }

        if (empty($data['price']) || !is_numeric($data['price']) || $data['price'] <= 0) {
            throw new Exception('Valid price is required');
        }

        // Check if category exists
        if (!$this->db->exists('categories', 'id = ? AND is_active = 1', [$data['category_id']])) {
            throw new Exception('Invalid category selected');
        }
    }

    /**
     * Format product data
     */
    private function formatProduct($product) {
        // Decode JSON fields
        $jsonFields = ['dimensions', 'images', 'tags', 'attributes'];
        foreach ($jsonFields as $field) {
            if ($product[$field]) {
                $product[$field] = json_decode($product[$field], true);
            }
        }

        // Format prices
        $product['price'] = (float)$product['price'];
        $product['compare_price'] = $product['compare_price'] ? (float)$product['compare_price'] : null;
        $product['cost_price'] = $product['cost_price'] ? (float)$product['cost_price'] : null;

        // Calculate discount percentage
        if ($product['compare_price'] && $product['compare_price'] > $product['price']) {
            $product['discount_percentage'] = round((($product['compare_price'] - $product['price']) / $product['compare_price']) * 100);
        } else {
            $product['discount_percentage'] = 0;
        }

        // Add stock status
        $product['in_stock'] = $product['stock_quantity'] > 0;
        $product['low_stock'] = $product['stock_quantity'] <= $product['low_stock_threshold'] && $product['stock_quantity'] > 0;

        // Get variants
        $product['variants'] = $this->getVariants($product['id']);

        return $product;
    }

    /**
     * Log search query
     */
    private function logSearchQuery($query) {
        session_start();
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        $this->db->insert('search_queries', [
            'user_id' => $userId,
            'query' => trim($query)
        ]);
    }
}
?>