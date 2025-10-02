<?php
/**
 * Order Management Class
 * Handles order creation, processing, and management
 */

require_once 'Database.php';
require_once 'Security.php';
require_once 'Product.php';

class Order {
    private $db;
    private $security;
    private $product;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->security = new Security();
        $this->product = new Product();
    }

    /**
     * Create new order
     */
    public function create($userId, $orderData) {
        try {
            $this->db->beginTransaction();

            // Validate order data
            $this->validateOrderData($orderData);

            // Generate order number
            $orderNumber = $this->security->generateOrderNumber();

            // Calculate totals
            $totals = $this->calculateOrderTotals($orderData['items'], $orderData);

            // Create order
            $orderInsertData = [
                'order_number' => $orderNumber,
                'user_id' => $userId,
                'status' => 'pending',
                'subtotal' => $totals['subtotal'],
                'tax_amount' => $totals['tax_amount'],
                'shipping_amount' => $totals['shipping_amount'],
                'discount_amount' => $totals['discount_amount'],
                'total_amount' => $totals['total_amount'],
                'currency' => isset($orderData['currency']) ? $orderData['currency'] : DEFAULT_CURRENCY,
                'payment_method' => isset($orderData['payment_method']) ? $orderData['payment_method'] : null,
                'shipping_method' => isset($orderData['shipping_method']) ? $orderData['shipping_method'] : 'standard',
                'notes' => isset($orderData['notes']) ? trim($orderData['notes']) : null,
                'billing_address' => json_encode($orderData['billing_address']),
                'shipping_address' => json_encode($orderData['shipping_address']),
                'estimated_delivery_date' => $this->calculateEstimatedDelivery($orderData['shipping_method'])
            ];

            $orderId = $this->db->insert('orders', $orderInsertData);

            // Create order items and update stock
            foreach ($orderData['items'] as $item) {
                $this->createOrderItem($orderId, $item);
                
                // Update product stock
                $this->product->updateStock($item['product_id'], $item['quantity'], 'subtract');
            }

            // Apply coupon if provided
            if (isset($orderData['coupon_code']) && !empty($orderData['coupon_code'])) {
                $this->applyCoupon($orderId, $userId, $orderData['coupon_code'], $totals['discount_amount']);
            }

            // Clear user's cart
            $this->clearCart($userId);

            $this->db->commit();

            return [
                'success' => true,
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'total_amount' => $totals['total_amount'],
                'message' => 'Order created successfully'
            ];

        } catch (Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get order by ID
     */
    public function getById($orderId, $userId = null) {
        $whereClause = 'id = ?';
        $params = [$orderId];

        if ($userId) {
            $whereClause .= ' AND user_id = ?';
            $params[] = $userId;
        }

        $order = $this->db->fetchOne("SELECT * FROM orders WHERE {$whereClause}", $params);

        if ($order) {
            $order = $this->formatOrder($order);
        }

        return $order;
    }

    /**
     * Get order by order number
     */
    public function getByOrderNumber($orderNumber, $userId = null) {
        $whereClause = 'order_number = ?';
        $params = [$orderNumber];

        if ($userId) {
            $whereClause .= ' AND user_id = ?';
            $params[] = $userId;
        }

        $order = $this->db->fetchOne("SELECT * FROM orders WHERE {$whereClause}", $params);

        if ($order) {
            $order = $this->formatOrder($order);
        }

        return $order;
    }

    /**
     * Get user orders
     */
    public function getUserOrders($userId, $page = 1, $limit = null) {
        $limit = $limit ?: ORDERS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        $orders = $this->db->fetchAll(
            "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$userId, $limit, $offset]
        );

        $formattedOrders = [];
        foreach ($orders as $order) {
            $formattedOrders[] = $this->formatOrder($order);
        }

        // Get total count
        $total = $this->db->count('orders', 'user_id = ?', [$userId]);

        return [
            'orders' => $formattedOrders,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ];
    }

    /**
     * Get seller orders
     */
    public function getSellerOrders($sellerId, $page = 1, $limit = null) {
        $limit = $limit ?: ORDERS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        $sql = "SELECT DISTINCT o.* FROM orders o 
                JOIN order_items oi ON o.id = oi.order_id 
                WHERE oi.seller_id = ? 
                ORDER BY o.created_at DESC 
                LIMIT ? OFFSET ?";

        $orders = $this->db->fetchAll($sql, [$sellerId, $limit, $offset]);

        $formattedOrders = [];
        foreach ($orders as $order) {
            $order = $this->formatOrder($order);
            // Filter items to only show seller's items
            $order['items'] = array_filter($order['items'], function($item) use ($sellerId) {
                return $item['seller_id'] == $sellerId;
            });
            $formattedOrders[] = $order;
        }

        // Get total count
        $countSql = "SELECT COUNT(DISTINCT o.id) as total FROM orders o 
                     JOIN order_items oi ON o.id = oi.order_id 
                     WHERE oi.seller_id = ?";
        $totalResult = $this->db->fetchOne($countSql, [$sellerId]);
        $total = $totalResult['total'];

        return [
            'orders' => $formattedOrders,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ];
    }

    /**
     * Update order status
     */
    public function updateStatus($orderId, $status, $notes = null) {
        try {
            $validStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
            
            if (!in_array($status, $validStatuses)) {
                throw new Exception('Invalid order status');
            }

            $updateData = [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($notes) {
                $updateData['notes'] = $notes;
            }

            // Set specific timestamps
            if ($status === 'shipped') {
                $updateData['shipped_at'] = date('Y-m-d H:i:s');
            } elseif ($status === 'delivered') {
                $updateData['delivered_at'] = date('Y-m-d H:i:s');
            }

            $this->db->update('orders', $updateData, 'id = ?', [$orderId]);

            // Send notification to user
            $this->sendOrderStatusNotification($orderId, $status);

            return [
                'success' => true,
                'message' => 'Order status updated successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Add tracking number
     */
    public function addTrackingNumber($orderId, $trackingNumber) {
        try {
            $this->db->update('orders', 
                [
                    'tracking_number' => $trackingNumber,
                    'updated_at' => date('Y-m-d H:i:s')
                ], 
                'id = ?', 
                [$orderId]
            );

            // Send tracking notification
            $this->sendTrackingNotification($orderId, $trackingNumber);

            return [
                'success' => true,
                'message' => 'Tracking number added successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Cancel order
     */
    public function cancel($orderId, $userId, $reason = null) {
        try {
            $order = $this->getById($orderId, $userId);
            
            if (!$order) {
                throw new Exception('Order not found');
            }

            if (!in_array($order['status'], ['pending', 'confirmed'])) {
                throw new Exception('Order cannot be cancelled at this stage');
            }

            $this->db->beginTransaction();

            // Update order status
            $this->updateStatus($orderId, 'cancelled', $reason);

            // Restore product stock
            foreach ($order['items'] as $item) {
                $this->product->updateStock($item['product_id'], $item['quantity'], 'add');
            }

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Order cancelled successfully'
            ];

        } catch (Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get order statistics
     */
    public function getOrderStats($sellerId = null, $dateFrom = null, $dateTo = null) {
        $whereConditions = ['1=1'];
        $params = [];

        if ($sellerId) {
            $whereConditions[] = 'oi.seller_id = ?';
            $params[] = $sellerId;
        }

        if ($dateFrom) {
            $whereConditions[] = 'o.created_at >= ?';
            $params[] = $dateFrom;
        }

        if ($dateTo) {
            $whereConditions[] = 'o.created_at <= ?';
            $params[] = $dateTo;
        }

        $whereClause = implode(' AND ', $whereConditions);
        $joinClause = $sellerId ? 'JOIN order_items oi ON o.id = oi.order_id' : '';

        $sql = "SELECT 
                    COUNT(DISTINCT o.id) as total_orders,
                    SUM(CASE WHEN o.status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                    SUM(CASE WHEN o.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_orders,
                    SUM(CASE WHEN o.status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
                    SUM(CASE WHEN o.status = 'shipped' THEN 1 ELSE 0 END) as shipped_orders,
                    SUM(CASE WHEN o.status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
                    SUM(CASE WHEN o.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                    SUM(o.total_amount) as total_revenue,
                    AVG(o.total_amount) as average_order_value
                FROM orders o {$joinClause}
                WHERE {$whereClause}";

        return $this->db->fetchOne($sql, $params);
    }

    /**
     * Create order item
     */
    private function createOrderItem($orderId, $itemData) {
        $product = $this->product->getById($itemData['product_id']);
        
        if (!$product) {
            throw new Exception('Product not found: ' . $itemData['product_id']);
        }

        if ($product['stock_quantity'] < $itemData['quantity']) {
            throw new Exception('Insufficient stock for product: ' . $product['name']);
        }

        $price = $product['price'];
        
        // Handle variant pricing
        if (isset($itemData['variant_id']) && $itemData['variant_id']) {
            $variant = $this->db->fetchOne(
                "SELECT * FROM product_variants WHERE id = ? AND product_id = ?",
                [$itemData['variant_id'], $itemData['product_id']]
            );
            
            if ($variant) {
                $price += $variant['price_adjustment'];
            }
        }

        $insertData = [
            'order_id' => $orderId,
            'product_id' => $itemData['product_id'],
            'variant_id' => isset($itemData['variant_id']) ? $itemData['variant_id'] : null,
            'seller_id' => $product['seller_id'],
            'quantity' => $itemData['quantity'],
            'price' => $price,
            'total' => $price * $itemData['quantity'],
            'product_name' => $product['name'],
            'product_image' => is_array($product['images']) && !empty($product['images']) ? $product['images'][0] : null,
            'product_sku' => $product['sku']
        ];

        return $this->db->insert('order_items', $insertData);
    }

    /**
     * Calculate order totals
     */
    private function calculateOrderTotals($items, $orderData) {
        $subtotal = 0;

        foreach ($items as $item) {
            $product = $this->product->getById($item['product_id']);
            $price = $product['price'];
            
            // Handle variant pricing
            if (isset($item['variant_id']) && $item['variant_id']) {
                $variant = $this->db->fetchOne(
                    "SELECT * FROM product_variants WHERE id = ? AND product_id = ?",
                    [$item['variant_id'], $item['product_id']]
                );
                
                if ($variant) {
                    $price += $variant['price_adjustment'];
                }
            }

            $subtotal += $price * $item['quantity'];
        }

        // Calculate tax (if applicable)
        $taxRate = 0.15; // 15% VAT for Ethiopia
        $taxAmount = $subtotal * $taxRate;

        // Calculate shipping
        $shippingAmount = $this->calculateShipping($orderData, $subtotal);

        // Calculate discount
        $discountAmount = 0;
        if (isset($orderData['coupon_code']) && !empty($orderData['coupon_code'])) {
            $discountAmount = $this->calculateCouponDiscount($orderData['coupon_code'], $subtotal);
        }

        $totalAmount = $subtotal + $taxAmount + $shippingAmount - $discountAmount;

        return [
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount
        ];
    }

    /**
     * Calculate shipping cost
     */
    private function calculateShipping($orderData, $subtotal) {
        // Free shipping threshold
        if ($subtotal >= FREE_SHIPPING_THRESHOLD) {
            return 0;
        }

        $shippingMethod = isset($orderData['shipping_method']) ? $orderData['shipping_method'] : 'standard';
        
        switch ($shippingMethod) {
            case 'express':
                return DEFAULT_SHIPPING_COST * 2;
            case 'overnight':
                return DEFAULT_SHIPPING_COST * 3;
            default:
                return DEFAULT_SHIPPING_COST;
        }
    }

    /**
     * Calculate estimated delivery date
     */
    private function calculateEstimatedDelivery($shippingMethod) {
        $days = 7; // Default 7 days
        
        switch ($shippingMethod) {
            case 'express':
                $days = 3;
                break;
            case 'overnight':
                $days = 1;
                break;
        }

        return date('Y-m-d', strtotime("+{$days} days"));
    }

    /**
     * Calculate coupon discount
     */
    private function calculateCouponDiscount($couponCode, $subtotal) {
        $coupon = $this->db->fetchOne(
            "SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND start_date <= NOW() AND end_date >= NOW()",
            [$couponCode]
        );

        if (!$coupon) {
            return 0;
        }

        if ($coupon['minimum_amount'] > $subtotal) {
            return 0;
        }

        $discount = 0;
        
        if ($coupon['type'] === 'percentage') {
            $discount = ($subtotal * $coupon['value']) / 100;
        } elseif ($coupon['type'] === 'fixed_amount') {
            $discount = $coupon['value'];
        }

        // Apply maximum discount limit
        if ($coupon['maximum_discount'] && $discount > $coupon['maximum_discount']) {
            $discount = $coupon['maximum_discount'];
        }

        return $discount;
    }

    /**
     * Apply coupon to order
     */
    private function applyCoupon($orderId, $userId, $couponCode, $discountAmount) {
        $coupon = $this->db->fetchOne(
            "SELECT * FROM coupons WHERE code = ?",
            [$couponCode]
        );

        if ($coupon && $discountAmount > 0) {
            $this->db->insert('coupon_usage', [
                'coupon_id' => $coupon['id'],
                'user_id' => $userId,
                'order_id' => $orderId,
                'discount_amount' => $discountAmount
            ]);

            // Update coupon usage count
            $this->db->query(
                "UPDATE coupons SET usage_count = usage_count + 1 WHERE id = ?",
                [$coupon['id']]
            );
        }
    }

    /**
     * Clear user cart
     */
    private function clearCart($userId) {
        $this->db->hardDelete('cart_items', 'user_id = ?', [$userId]);
    }

    /**
     * Validate order data
     */
    private function validateOrderData($data) {
        if (empty($data['items']) || !is_array($data['items'])) {
            throw new Exception('Order must contain at least one item');
        }

        if (empty($data['billing_address']) || !is_array($data['billing_address'])) {
            throw new Exception('Billing address is required');
        }

        if (empty($data['shipping_address']) || !is_array($data['shipping_address'])) {
            throw new Exception('Shipping address is required');
        }

        foreach ($data['items'] as $item) {
            if (empty($item['product_id']) || empty($item['quantity']) || $item['quantity'] <= 0) {
                throw new Exception('Invalid item data');
            }
        }
    }

    /**
     * Format order data
     */
    private function formatOrder($order) {
        // Decode JSON fields
        $order['billing_address'] = json_decode($order['billing_address'], true);
        $order['shipping_address'] = json_decode($order['shipping_address'], true);

        // Format amounts
        $order['subtotal'] = (float)$order['subtotal'];
        $order['tax_amount'] = (float)$order['tax_amount'];
        $order['shipping_amount'] = (float)$order['shipping_amount'];
        $order['discount_amount'] = (float)$order['discount_amount'];
        $order['total_amount'] = (float)$order['total_amount'];

        // Get order items
        $order['items'] = $this->db->fetchAll(
            "SELECT * FROM order_items WHERE order_id = ?",
            [$order['id']]
        );

        return $order;
    }

    /**
     * Send order status notification
     */
    private function sendOrderStatusNotification($orderId, $status) {
        $order = $this->getById($orderId);
        
        if ($order) {
            $message = "Your order #{$order['order_number']} status has been updated to: " . ucfirst($status);
            
            $this->db->insert('notifications', [
                'user_id' => $order['user_id'],
                'type' => 'order',
                'title' => 'Order Status Update',
                'message' => $message,
                'data' => json_encode(['order_id' => $orderId, 'status' => $status])
            ]);
        }
    }

    /**
     * Send tracking notification
     */
    private function sendTrackingNotification($orderId, $trackingNumber) {
        $order = $this->getById($orderId);
        
        if ($order) {
            $message = "Your order #{$order['order_number']} has been shipped. Tracking number: {$trackingNumber}";
            
            $this->db->insert('notifications', [
                'user_id' => $order['user_id'],
                'type' => 'shipping',
                'title' => 'Order Shipped',
                'message' => $message,
                'data' => json_encode(['order_id' => $orderId, 'tracking_number' => $trackingNumber])
            ]);
        }
    }
}
?>