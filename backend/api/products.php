<?php
/**
 * Products API Endpoints
 * Handles product-related API requests
 */

header('Content-Type: application/json');
require_once '../includes/init.php';

$product = new Product();
$security = new Security();
$user = new User();

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch ($method) {
        case 'GET':
            switch ($action) {
                case 'list':
                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : PRODUCTS_PER_PAGE;
                    
                    $filters = [];
                    if (isset($_GET['category_id'])) $filters['category_id'] = (int)$_GET['category_id'];
                    if (isset($_GET['seller_id'])) $filters['seller_id'] = (int)$_GET['seller_id'];
                    if (isset($_GET['price_min'])) $filters['price_min'] = (float)$_GET['price_min'];
                    if (isset($_GET['price_max'])) $filters['price_max'] = (float)$_GET['price_max'];
                    if (isset($_GET['search'])) $filters['search'] = $_GET['search'];
                    if (isset($_GET['is_featured'])) $filters['is_featured'] = (bool)$_GET['is_featured'];
                    if (isset($_GET['in_stock'])) $filters['in_stock'] = (bool)$_GET['in_stock'];
                    if (isset($_GET['sort'])) $filters['sort'] = $_GET['sort'];
                    
                    $result = $product->getProducts($filters, $page, $limit);
                    echo json_encode([
                        'success' => true,
                        'products' => $result['products'],
                        'pagination' => $result['pagination']
                    ]);
                    break;
                    
                case 'get':
                    $productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                    $slug = isset($_GET['slug']) ? $_GET['slug'] : '';
                    
                    if ($productId) {
                        $productData = $product->getById($productId);
                    } elseif ($slug) {
                        $productData = $product->getBySlug($slug);
                    } else {
                        throw new Exception('Product ID or slug required');
                    }
                    
                    if (!$productData) {
                        throw new Exception('Product not found');
                    }
                    
                    // Get related products
                    $relatedProducts = $product->getRelatedProducts($productData['id']);
                    
                    echo json_encode([
                        'success' => true,
                        'product' => $productData,
                        'related_products' => $relatedProducts
                    ]);
                    break;
                    
                case 'search':
                    $query = isset($_GET['q']) ? trim($_GET['q']) : '';
                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : PRODUCTS_PER_PAGE;
                    
                    if (empty($query)) {
                        throw new Exception('Search query is required');
                    }
                    
                    $filters = [];
                    if (isset($_GET['category_id'])) $filters['category_id'] = (int)$_GET['category_id'];
                    if (isset($_GET['price_min'])) $filters['price_min'] = (float)$_GET['price_min'];
                    if (isset($_GET['price_max'])) $filters['price_max'] = (float)$_GET['price_max'];
                    if (isset($_GET['sort'])) $filters['sort'] = $_GET['sort'];
                    
                    $result = $product->search($query, $filters, $page, $limit);
                    echo json_encode([
                        'success' => true,
                        'query' => $query,
                        'products' => $result['products'],
                        'pagination' => $result['pagination']
                    ]);
                    break;
                    
                case 'suggestions':
                    $query = isset($_GET['q']) ? trim($_GET['q']) : '';
                    
                    if (strlen($query) < 2) {
                        echo json_encode([
                            'success' => true,
                            'suggestions' => []
                        ]);
                        break;
                    }
                    
                    // Get search suggestions from products and categories
                    $db = Database::getInstance();
                    
                    $productSuggestions = $db->fetchAll(
                        "SELECT DISTINCT name FROM products 
                         WHERE name LIKE ? AND status = 'active' 
                         ORDER BY total_sales DESC 
                         LIMIT 5",
                        ['%' . $query . '%']
                    );
                    
                    $categorySuggestions = $db->fetchAll(
                        "SELECT DISTINCT name FROM categories 
                         WHERE name LIKE ? AND is_active = 1 
                         LIMIT 3",
                        ['%' . $query . '%']
                    );
                    
                    $suggestions = [];
                    foreach ($productSuggestions as $suggestion) {
                        $suggestions[] = $suggestion['name'];
                    }
                    foreach ($categorySuggestions as $suggestion) {
                        $suggestions[] = $suggestion['name'];
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'suggestions' => array_unique($suggestions)
                    ]);
                    break;
                    
                case 'quick-view':
                    $productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                    
                    if (!$productId) {
                        throw new Exception('Product ID required');
                    }
                    
                    $productData = $product->getById($productId);
                    if (!$productData) {
                        throw new Exception('Product not found');
                    }
                    
                    // Generate quick view HTML
                    ob_start();
                    include '../components/quick-view.php';
                    $html = ob_get_clean();
                    
                    echo json_encode([
                        'success' => true,
                        'html' => $html,
                        'product' => $productData
                    ]);
                    break;
                    
                case 'featured':
                    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;
                    
                    $result = $product->getProducts(['is_featured' => 1], 1, $limit);
                    echo json_encode([
                        'success' => true,
                        'products' => $result['products']
                    ]);
                    break;
                    
                case 'new-arrivals':
                    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;
                    
                    $result = $product->getProducts(['sort' => 'newest'], 1, $limit);
                    echo json_encode([
                        'success' => true,
                        'products' => $result['products']
                    ]);
                    break;
                    
                case 'best-sellers':
                    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;
                    
                    $result = $product->getProducts(['sort' => 'popular'], 1, $limit);
                    echo json_encode([
                        'success' => true,
                        'products' => $result['products']
                    ]);
                    break;
                    
                default:
                    throw new Exception('Invalid action');
            }
            break;
            
        case 'POST':
            // Check authentication for write operations
            $currentUser = $user->getCurrentUser();
            if (!$currentUser) {
                throw new Exception('Authentication required');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            switch ($action) {
                case 'create':
                    if ($currentUser['role'] !== 'seller' && $currentUser['role'] !== 'admin') {
                        throw new Exception('Seller access required');
                    }
                    
                    // Get seller ID
                    $db = Database::getInstance();
                    $seller = $db->fetchOne(
                        "SELECT id FROM sellers WHERE user_id = ?",
                        [$currentUser['id']]
                    );
                    
                    if (!$seller) {
                        throw new Exception('Seller profile not found');
                    }
                    
                    $result = $product->create($seller['id'], $input);
                    echo json_encode($result);
                    break;
                    
                case 'update':
                    if ($currentUser['role'] !== 'seller' && $currentUser['role'] !== 'admin') {
                        throw new Exception('Seller access required');
                    }
                    
                    $productId = isset($input['product_id']) ? (int)$input['product_id'] : 0;
                    if (!$productId) {
                        throw new Exception('Product ID required');
                    }
                    
                    // Get seller ID
                    $db = Database::getInstance();
                    $seller = $db->fetchOne(
                        "SELECT id FROM sellers WHERE user_id = ?",
                        [$currentUser['id']]
                    );
                    
                    if (!$seller) {
                        throw new Exception('Seller profile not found');
                    }
                    
                    $result = $product->update($productId, $seller['id'], $input);
                    echo json_encode($result);
                    break;
                    
                case 'delete':
                    if ($currentUser['role'] !== 'seller' && $currentUser['role'] !== 'admin') {
                        throw new Exception('Seller access required');
                    }
                    
                    $productId = isset($input['product_id']) ? (int)$input['product_id'] : 0;
                    if (!$productId) {
                        throw new Exception('Product ID required');
                    }
                    
                    // Get seller ID
                    $db = Database::getInstance();
                    $seller = $db->fetchOne(
                        "SELECT id FROM sellers WHERE user_id = ?",
                        [$currentUser['id']]
                    );
                    
                    if (!$seller) {
                        throw new Exception('Seller profile not found');
                    }
                    
                    $result = $product->delete($productId, $seller['id']);
                    echo json_encode($result);
                    break;
                    
                case 'update-stock':
                    if ($currentUser['role'] !== 'seller' && $currentUser['role'] !== 'admin') {
                        throw new Exception('Seller access required');
                    }
                    
                    $productId = isset($input['product_id']) ? (int)$input['product_id'] : 0;
                    $quantity = isset($input['quantity']) ? (int)$input['quantity'] : 0;
                    $operation = isset($input['operation']) ? $input['operation'] : 'set';
                    
                    if (!$productId) {
                        throw new Exception('Product ID required');
                    }
                    
                    $result = $product->updateStock($productId, $quantity, $operation);
                    echo json_encode($result);
                    break;
                    
                case 'rate':
                    // Add product rating/review
                    $productId = isset($input['product_id']) ? (int)$input['product_id'] : 0;
                    $rating = isset($input['rating']) ? (int)$input['rating'] : 0;
                    $comment = isset($input['comment']) ? trim($input['comment']) : '';
                    $title = isset($input['title']) ? trim($input['title']) : '';
                    
                    if (!$productId || !$rating || $rating < 1 || $rating > 5) {
                        throw new Exception('Valid product ID and rating (1-5) required');
                    }
                    
                    // Check if user has purchased this product
                    $db = Database::getInstance();
                    $purchase = $db->fetchOne(
                        "SELECT o.id FROM orders o 
                         JOIN order_items oi ON o.id = oi.order_id 
                         WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'delivered'",
                        [$currentUser['id'], $productId]
                    );
                    
                    if (!$purchase) {
                        throw new Exception('You can only review products you have purchased');
                    }
                    
                    // Check if already reviewed
                    $existingReview = $db->fetchOne(
                        "SELECT id FROM reviews WHERE product_id = ? AND user_id = ?",
                        [$productId, $currentUser['id']]
                    );
                    
                    if ($existingReview) {
                        throw new Exception('You have already reviewed this product');
                    }
                    
                    // Insert review
                    $reviewId = $db->insert('reviews', [
                        'product_id' => $productId,
                        'user_id' => $currentUser['id'],
                        'order_id' => $purchase['id'],
                        'rating' => $rating,
                        'title' => $title,
                        'comment' => $comment,
                        'is_verified_purchase' => 1,
                        'status' => 'pending'
                    ]);
                    
                    // Update product rating
                    $avgRating = $db->fetchOne(
                        "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
                         FROM reviews WHERE product_id = ? AND status = 'approved'",
                        [$productId]
                    );
                    
                    $db->update('products', [
                        'rating' => round($avgRating['avg_rating'], 2),
                        'total_reviews' => $avgRating['total_reviews']
                    ], 'id = ?', [$productId]);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Review submitted successfully and is pending approval'
                    ]);
                    break;
                    
                default:
                    throw new Exception('Invalid action');
            }
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
    
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>