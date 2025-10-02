<?php
/**
 * Ethiopian Marketplace Configuration
 * Main configuration file for the e-commerce platform
 */

// Environment settings
define('ENVIRONMENT', 'development'); // development, staging, production

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'ethiopian_marketplace');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Site configuration
define('SITE_NAME', 'Ethiopian Marketplace');
define('SITE_URL', 'http://localhost');
define('SITE_EMAIL', 'info@ethiopianmarketplace.com');
define('ADMIN_EMAIL', 'admin@ethiopianmarketplace.com');

// Security settings
define('JWT_SECRET', 'your-super-secret-jwt-key-change-in-production');
define('ENCRYPTION_KEY', 'your-32-character-encryption-key-here');
define('SESSION_LIFETIME', 86400); // 24 hours
define('PASSWORD_RESET_EXPIRY', 3600); // 1 hour

// File upload settings
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Payment gateway settings
define('STRIPE_PUBLIC_KEY', 'pk_test_your_stripe_public_key');
define('STRIPE_SECRET_KEY', 'sk_test_your_stripe_secret_key');
define('PAYPAL_CLIENT_ID', 'your_paypal_client_id');
define('PAYPAL_CLIENT_SECRET', 'your_paypal_client_secret');
define('PAYPAL_MODE', 'sandbox'); // sandbox or live

// Telebirr settings (Ethiopian mobile payment)
define('TELEBIRR_APP_ID', 'your_telebirr_app_id');
define('TELEBIRR_APP_KEY', 'your_telebirr_app_key');
define('TELEBIRR_PUBLIC_KEY', 'your_telebirr_public_key');
define('TELEBIRR_PRIVATE_KEY', 'your_telebirr_private_key');

// Email settings (SMTP)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_ENCRYPTION', 'tls');

// AI/OpenAI settings
define('OPENAI_API_KEY', 'your-openai-api-key');
define('OPENAI_MODEL', 'gpt-3.5-turbo');

// Social media settings
define('FACEBOOK_APP_ID', 'your_facebook_app_id');
define('GOOGLE_CLIENT_ID', 'your_google_client_id');
define('TWITTER_API_KEY', 'your_twitter_api_key');

// Currency settings
define('DEFAULT_CURRENCY', 'ETB');
define('SUPPORTED_CURRENCIES', ['ETB', 'USD', 'EUR']);
define('CURRENCY_RATES', [
    'ETB' => 1.0,
    'USD' => 0.018, // 1 ETB = 0.018 USD (approximate)
    'EUR' => 0.017  // 1 ETB = 0.017 EUR (approximate)
]);

// Language settings
define('DEFAULT_LANGUAGE', 'en');
define('SUPPORTED_LANGUAGES', ['en', 'am']);

// Cache settings
define('CACHE_ENABLED', true);
define('CACHE_LIFETIME', 3600); // 1 hour

// Pagination settings
define('PRODUCTS_PER_PAGE', 20);
define('ORDERS_PER_PAGE', 10);
define('REVIEWS_PER_PAGE', 10);

// Commission settings
define('DEFAULT_COMMISSION_RATE', 5.0); // 5%
define('FEATURED_PRODUCT_FEE', 10.0); // ETB per month

// Shipping settings
define('FREE_SHIPPING_THRESHOLD', 1000); // ETB
define('DEFAULT_SHIPPING_COST', 50); // ETB

// Error reporting
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('Africa/Addis_Ababa');

// Session configuration
ini_set('session.cookie_lifetime', SESSION_LIFETIME);
ini_set('session.cookie_secure', ENVIRONMENT === 'production');
ini_set('session.cookie_httponly', true);
ini_set('session.use_strict_mode', true);

// CORS headers for API
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
}

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    }
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
    exit(0);
}
?>