<?php
/**
 * Initialization file
 * Loads configuration and required classes
 */

// Load configuration
require_once __DIR__ . '/../../config/config.php';

// Load classes
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Security.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Product.php';
require_once __DIR__ . '/../classes/Order.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set error handling
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Set exception handler
set_exception_handler(function($exception) {
    if (ENVIRONMENT === 'development') {
        echo "Uncaught exception: " . $exception->getMessage() . "\n";
        echo "Stack trace:\n" . $exception->getTraceAsString();
    } else {
        error_log("Uncaught exception: " . $exception->getMessage());
        echo "An error occurred. Please try again later.";
    }
});

// Helper functions
function formatPrice($amount, $currency = 'ETB') {
    switch ($currency) {
        case 'USD':
            return '$' . number_format($amount, 2);
        case 'EUR':
            return '€' . number_format($amount, 2);
        default:
            return 'ETB ' . number_format($amount, 2);
    }
}

function formatDate($date, $format = 'M j, Y') {
    return date($format, strtotime($date));
}

function formatDateTime($datetime, $format = 'M j, Y g:i A') {
    return date($format, strtotime($datetime));
}

function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

function generateUrl($path, $params = []) {
    $url = SITE_URL . '/' . ltrim($path, '/');
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    return $url;
}

function isActiveRoute($route) {
    $currentRoute = $_SERVER['REQUEST_URI'];
    return strpos($currentRoute, $route) !== false;
}

function sanitizeOutput($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function convertCurrency($amount, $fromCurrency, $toCurrency) {
    if ($fromCurrency === $toCurrency) {
        return $amount;
    }
    
    $rates = CURRENCY_RATES;
    
    // Convert to ETB first if not already
    if ($fromCurrency !== 'ETB') {
        $amount = $amount / $rates[$fromCurrency];
    }
    
    // Convert from ETB to target currency
    if ($toCurrency !== 'ETB') {
        $amount = $amount * $rates[$toCurrency];
    }
    
    return round($amount, 2);
}

function getLanguageText($key, $language = 'en') {
    $translations = [
        'en' => [
            'add_to_cart' => 'Add to Cart',
            'buy_now' => 'Buy Now',
            'out_of_stock' => 'Out of Stock',
            'in_stock' => 'In Stock',
            'free_shipping' => 'Free Shipping',
            'search_products' => 'Search products...',
            'view_details' => 'View Details',
            'quick_view' => 'Quick View',
            'compare' => 'Compare',
            'wishlist' => 'Add to Wishlist',
            'remove_wishlist' => 'Remove from Wishlist',
            'rating' => 'Rating',
            'reviews' => 'Reviews',
            'price' => 'Price',
            'category' => 'Category',
            'brand' => 'Brand',
            'seller' => 'Seller',
            'shipping' => 'Shipping',
            'delivery' => 'Delivery',
            'payment' => 'Payment',
            'order' => 'Order',
            'account' => 'Account',
            'login' => 'Login',
            'register' => 'Register',
            'logout' => 'Logout',
            'dashboard' => 'Dashboard',
            'profile' => 'Profile',
            'orders' => 'Orders',
            'settings' => 'Settings',
            'help' => 'Help',
            'contact' => 'Contact',
            'about' => 'About',
            'terms' => 'Terms of Service',
            'privacy' => 'Privacy Policy',
            'newsletter' => 'Newsletter',
            'subscribe' => 'Subscribe',
            'follow_us' => 'Follow Us'
        ],
        'am' => [
            'add_to_cart' => 'ወደ ጋሪ ጨምር',
            'buy_now' => 'አሁን ግዛ',
            'out_of_stock' => 'ከክምችት ወጥቷል',
            'in_stock' => 'በክምችት ውስጥ',
            'free_shipping' => 'ነፃ መላኪያ',
            'search_products' => 'ምርቶችን ፈልግ...',
            'view_details' => 'ዝርዝሮችን ይመልከቱ',
            'quick_view' => 'ፈጣን እይታ',
            'compare' => 'አወዳድር',
            'wishlist' => 'ወደ ምኞት ዝርዝር ጨምር',
            'remove_wishlist' => 'ከምኞት ዝርዝር አስወግድ',
            'rating' => 'ደረጃ',
            'reviews' => 'ግምገማዎች',
            'price' => 'ዋጋ',
            'category' => 'ምድብ',
            'brand' => 'ብራንድ',
            'seller' => 'ሻጭ',
            'shipping' => 'መላኪያ',
            'delivery' => 'ማድረስ',
            'payment' => 'ክፍያ',
            'order' => 'ትዕዛዝ',
            'account' => 'መለያ',
            'login' => 'ግባ',
            'register' => 'ተመዝገብ',
            'logout' => 'ውጣ',
            'dashboard' => 'ዳሽቦርድ',
            'profile' => 'መገለጫ',
            'orders' => 'ትዕዛዞች',
            'settings' => 'ቅንብሮች',
            'help' => 'እርዳታ',
            'contact' => 'ያግኙን',
            'about' => 'ስለእኛ',
            'terms' => 'የአገልግሎት ውሎች',
            'privacy' => 'የግላዊነት ፖሊሲ',
            'newsletter' => 'ዜና መልእክት',
            'subscribe' => 'ተመዝገብ',
            'follow_us' => 'ተከተሉን'
        ]
    ];
    
    return isset($translations[$language][$key]) ? $translations[$language][$key] : $key;
}

function getCurrentLanguage() {
    if (isset($_SESSION['language'])) {
        return $_SESSION['language'];
    }
    
    if (isset($_COOKIE['language'])) {
        return $_COOKIE['language'];
    }
    
    return DEFAULT_LANGUAGE;
}

function setLanguage($language) {
    if (in_array($language, SUPPORTED_LANGUAGES)) {
        $_SESSION['language'] = $language;
        setcookie('language', $language, time() + (365 * 24 * 60 * 60), '/');
        return true;
    }
    return false;
}

function getCurrentCurrency() {
    if (isset($_SESSION['currency'])) {
        return $_SESSION['currency'];
    }
    
    if (isset($_COOKIE['currency'])) {
        return $_COOKIE['currency'];
    }
    
    return DEFAULT_CURRENCY;
}

function setCurrency($currency) {
    if (in_array($currency, SUPPORTED_CURRENCIES)) {
        $_SESSION['currency'] = $currency;
        setcookie('currency', $currency, time() + (365 * 24 * 60 * 60), '/');
        return true;
    }
    return false;
}

function logActivity($action, $details = []) {
    $db = Database::getInstance();
    
    $data = [
        'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
        'session_id' => session_id(),
        'event_type' => $action,
        'event_data' => json_encode($details),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    
    $db->insert('analytics_events', $data);
}

function sendNotification($userId, $type, $title, $message, $data = []) {
    $db = Database::getInstance();
    
    $notificationData = [
        'user_id' => $userId,
        'type' => $type,
        'title' => $title,
        'message' => $message,
        'data' => json_encode($data)
    ];
    
    return $db->insert('notifications', $notificationData);
}

function uploadFile($file, $directory = 'products', $allowedTypes = null) {
    $security = new Security();
    
    try {
        // Validate file
        $security->validateFileUpload($file, $allowedTypes);
        
        // Create upload directory if it doesn't exist
        $uploadDir = UPLOAD_PATH . $directory . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate secure filename
        $filename = $security->generateSecureFilename($file['name']);
        $filepath = $uploadDir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => true,
                'filename' => $filename,
                'path' => $directory . '/' . $filename,
                'url' => SITE_URL . '/assets/uploads/' . $directory . '/' . $filename
            ];
        } else {
            throw new Exception('Failed to move uploaded file');
        }
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

// Auto-load categories for navigation
function getCategories() {
    static $categories = null;
    
    if ($categories === null) {
        $db = Database::getInstance();
        $categories = $db->fetchAll(
            "SELECT * FROM categories WHERE is_active = 1 AND parent_id IS NULL ORDER BY sort_order ASC, name ASC"
        );
        
        foreach ($categories as &$category) {
            $category['children'] = $db->fetchAll(
                "SELECT * FROM categories WHERE is_active = 1 AND parent_id = ? ORDER BY sort_order ASC, name ASC",
                [$category['id']]
            );
        }
    }
    
    return $categories;
}

// Initialize cart count for header
function getCartCount() {
    if (!isset($_SESSION['user_id'])) {
        return 0;
    }
    
    $db = Database::getInstance();
    $result = $db->fetchOne(
        "SELECT SUM(quantity) as count FROM cart_items WHERE user_id = ?",
        [$_SESSION['user_id']]
    );
    
    return (int)($result['count'] ?? 0);
}
?>