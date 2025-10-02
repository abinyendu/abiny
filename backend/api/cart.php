<?php
/**
 * Shopping Cart API Endpoints
 * Handles cart operations
 */

header('Content-Type: application/json');
require_once '../includes/init.php';

$security = new Security();
$user = new User();
$product = new Product();

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Check authentication
$currentUser = $user->getCurrentUser();
if (!$currentUser) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit;
}

$db = Database::getInstance();

try {
    switch ($method) {
        case 'GET':
            switch ($action) {
                case 'list':
                    // Get cart items with product details
                    $cartItems = $db->fetchAll(
                        "SELECT ci.*, p.name, p.slug, p.price, p.images, p.stock_quantity,
                                pv.name as variant_name, pv.value as variant_value, pv.price_adjustment,
                                s.business_name as seller_name
                         FROM cart_items ci
                         JOIN products p ON ci.product_id = p.id
                         LEFT JOIN product_variants pv ON ci.variant_id = pv.id
                         JOIN sellers s ON p.seller_id = s.id
                         WHERE ci.user_id = ?
                         ORDER BY ci.created_at DESC",
                        [$currentUser['id']]
                    );
                    
                    // Format cart items
                    $formattedItems = [];
                    $subtotal = 0;
                    
                    foreach ($cartItems as $item) {
                        $itemPrice = $item['price'];
                        if ($item['variant_id'] && $item['price_adjustment']) {
                            $itemPrice += $item['price_adjustment'];
                        }
                        
                        $itemTotal = $itemPrice * $item['quantity'];
                        $subtotal += $itemTotal;
                        
                        $images = json_decode($item['images'], true);
                        $mainImage = !empty($images) ? $images[0] : 'placeholder.jpg';
                        
                        $formattedItems[] = [
                            'id' => $item['id'],
                            'product_id' => $item['product_id'],
                            'variant_id' => $item['variant_id'],
                            'name' => $item['name'],
                            'slug' => $item['slug'],
                            'price' => $itemPrice,
                            'quantity' => $item['quantity'],
                            'total' => $itemTotal,
                            'image' => $mainImage,
                            'stock_quantity' => $item['stock_quantity'],
                            'variant_name' => $item['variant_name'],
                            'variant_value' => $item['variant_value'],
                            'seller_name' => $item['seller_name'],
                            'in_stock' => $item['stock_quantity'] >= $item['quantity']
                        ];
                    }
                    
                    // Calculate totals
                    $taxRate = 0.15; // 15% VAT
                    $taxAmount = $subtotal * $taxRate;
                    $shippingAmount = $subtotal >= FREE_SHIPPING_THRESHOLD ? 0 : DEFAULT_SHIPPING_COST;
                    $total = $subtotal + $taxAmount + $shippingAmount;
                    
                    echo json_encode([
                        'success' => true,
                        'items' => $formattedItems,
                        'summary' => [
                            'subtotal' => $subtotal,
                            'tax_amount' => $taxAmount,
                            'shipping_amount' => $shippingAmount,
                            'total' => $total,
                            'item_count' => count($formattedItems)
                        ]
                    ]);
                    break;
                    
                case 'count':
                    $result = $db->fetchOne(
                        "SELECT SUM(quantity) as count FROM cart_items WHERE user_id = ?",
                        [$currentUser['id']]
                    );
                    
                    echo json_encode([
                        'success' => true,
                        'count' => (int)($result['count'] ?? 0)
                    ]);
                    break;
                    
                default:
                    throw new Exception('Invalid action');
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            switch ($action) {
                case 'add':
                    $productId = isset($input['product_id']) ? (int)$input['product_id'] : 0;
                    $quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;
                    $variantId = isset($input['variant_id']) ? (int)$input['variant_id'] : null;
                    
                    if (!$productId || $quantity <= 0) {
                        throw new Exception('Valid product ID and quantity required');
                    }
                    
                    // Check if product exists and is available
                    $productData = $product->getById($productId);
                    if (!$productData) {
                        throw new Exception('Product not found');
                    }
                    
                    if ($productData['status'] !== 'active') {
                        throw new Exception('Product is not available');
                    }
                    
                    if ($productData['stock_quantity'] < $quantity) {
                        throw new Exception('Insufficient stock available');
                    }
                    
                    // Check if variant exists (if specified)
                    if ($variantId) {
                        $variant = $db->fetchOne(
                            "SELECT * FROM product_variants WHERE id = ? AND product_id = ? AND is_active = 1",
                            [$variantId, $productId]
                        );
                        
                        if (!$variant) {
                            throw new Exception('Product variant not found');
                        }
                        
                        if ($variant['stock_quantity'] < $quantity) {
                            throw new Exception('Insufficient variant stock available');
                        }
                    }
                    
                    // Check if item already exists in cart
                    $existingItem = $db->fetchOne(
                        "SELECT * FROM cart_items WHERE user_id = ? AND product_id = ? AND variant_id " . 
                        ($variantId ? "= ?" : "IS NULL"),
                        $variantId ? [$currentUser['id'], $productId, $variantId] : [$currentUser['id'], $productId]
                    );
                    
                    if ($existingItem) {
                        // Update quantity
                        $newQuantity = $existingItem['quantity'] + $quantity;
                        
                        // Check stock again
                        $availableStock = $variantId ? $variant['stock_quantity'] : $productData['stock_quantity'];
                        if ($newQuantity > $availableStock) {
                            throw new Exception('Cannot add more items. Insufficient stock.');
                        }
                        
                        $db->update('cart_items', [
                            'quantity' => $newQuantity,
                            'updated_at' => date('Y-m-d H:i:s')
                        ], 'id = ?', [$existingItem['id']]);
                        
                        $message = 'Cart updated successfully';
                    } else {
                        // Add new item
                        $itemPrice = $productData['price'];
                        if ($variantId && isset($variant['price_adjustment'])) {
                            $itemPrice += $variant['price_adjustment'];
                        }
                        
                        $db->insert('cart_items', [
                            'user_id' => $currentUser['id'],
                            'product_id' => $productId,
                            'variant_id' => $variantId,
                            'quantity' => $quantity,
                            'price' => $itemPrice
                        ]);
                        
                        $message = 'Item added to cart successfully';
                    }
                    
                    // Log activity
                    logActivity('add_to_cart', [
                        'product_id' => $productId,
                        'variant_id' => $variantId,
                        'quantity' => $quantity
                    ]);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => $message
                    ]);
                    break;
                    
                case 'update':
                    $itemId = isset($input['item_id']) ? (int)$input['item_id'] : 0;
                    $quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;
                    
                    if (!$itemId || $quantity <= 0) {
                        throw new Exception('Valid item ID and quantity required');
                    }
                    
                    // Check if item belongs to user
                    $cartItem = $db->fetchOne(
                        "SELECT ci.*, p.stock_quantity, pv.stock_quantity as variant_stock
                         FROM cart_items ci
                         JOIN products p ON ci.product_id = p.id
                         LEFT JOIN product_variants pv ON ci.variant_id = pv.id
                         WHERE ci.id = ? AND ci.user_id = ?",
                        [$itemId, $currentUser['id']]
                    );
                    
                    if (!$cartItem) {
                        throw new Exception('Cart item not found');
                    }
                    
                    // Check stock availability
                    $availableStock = $cartItem['variant_id'] ? $cartItem['variant_stock'] : $cartItem['stock_quantity'];
                    if ($quantity > $availableStock) {
                        throw new Exception('Insufficient stock available');
                    }
                    
                    // Update quantity
                    $db->update('cart_items', [
                        'quantity' => $quantity,
                        'updated_at' => date('Y-m-d H:i:s')
                    ], 'id = ?', [$itemId]);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Cart item updated successfully'
                    ]);
                    break;
                    
                case 'remove':
                    $itemId = isset($input['item_id']) ? (int)$input['item_id'] : 0;
                    
                    if (!$itemId) {
                        throw new Exception('Item ID required');
                    }
                    
                    // Check if item belongs to user
                    $cartItem = $db->fetchOne(
                        "SELECT * FROM cart_items WHERE id = ? AND user_id = ?",
                        [$itemId, $currentUser['id']]
                    );
                    
                    if (!$cartItem) {
                        throw new Exception('Cart item not found');
                    }
                    
                    // Remove item
                    $db->hardDelete('cart_items', 'id = ?', [$itemId]);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Item removed from cart'
                    ]);
                    break;
                    
                case 'clear':
                    // Clear all cart items for user
                    $db->hardDelete('cart_items', 'user_id = ?', [$currentUser['id']]);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Cart cleared successfully'
                    ]);
                    break;
                    
                case 'apply-coupon':
                    $couponCode = isset($input['coupon_code']) ? trim($input['coupon_code']) : '';
                    
                    if (empty($couponCode)) {
                        throw new Exception('Coupon code required');
                    }
                    
                    // Get cart total
                    $cartItems = $db->fetchAll(
                        "SELECT ci.*, p.price FROM cart_items ci
                         JOIN products p ON ci.product_id = p.id
                         WHERE ci.user_id = ?",
                        [$currentUser['id']]
                    );
                    
                    $subtotal = 0;
                    foreach ($cartItems as $item) {
                        $subtotal += $item['price'] * $item['quantity'];
                    }
                    
                    // Validate coupon
                    $coupon = $db->fetchOne(
                        "SELECT * FROM coupons 
                         WHERE code = ? AND is_active = 1 
                         AND start_date <= NOW() AND end_date >= NOW()",
                        [$couponCode]
                    );
                    
                    if (!$coupon) {
                        throw new Exception('Invalid or expired coupon code');
                    }
                    
                    if ($coupon['minimum_amount'] > $subtotal) {
                        throw new Exception('Minimum order amount not met for this coupon');
                    }
                    
                    // Check usage limits
                    if ($coupon['usage_limit'] && $coupon['usage_count'] >= $coupon['usage_limit']) {
                        throw new Exception('Coupon usage limit exceeded');
                    }
                    
                    // Check user usage limit
                    $userUsage = $db->count('coupon_usage', 'coupon_id = ? AND user_id = ?', [$coupon['id'], $currentUser['id']]);
                    if ($coupon['user_usage_limit'] && $userUsage >= $coupon['user_usage_limit']) {
                        throw new Exception('You have already used this coupon the maximum number of times');
                    }
                    
                    // Calculate discount
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
                    
                    // Store coupon in session for checkout
                    $_SESSION['applied_coupon'] = [
                        'id' => $coupon['id'],
                        'code' => $coupon['code'],
                        'discount' => $discount
                    ];
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Coupon applied successfully',
                        'discount' => $discount,
                        'coupon' => [
                            'code' => $coupon['code'],
                            'name' => $coupon['name'],
                            'discount' => $discount
                        ]
                    ]);
                    break;
                    
                case 'remove-coupon':
                    unset($_SESSION['applied_coupon']);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Coupon removed successfully'
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
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>