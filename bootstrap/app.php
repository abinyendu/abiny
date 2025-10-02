<?php

declare(strict_types=1);

// Define base path for the application
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Simple .env loader
(function (): void {
    $envFile = BASE_PATH . '/.env';
    if (!is_file($envFile)) {
        return;
    }
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }
        [$key, $value] = $parts;
        $key = trim($key);
        $value = trim($value);
        // Remove optional quotes
        $value = trim($value, "\"' ");
        if ($key !== '') {
            $_ENV[$key] = $value;
            putenv($key . '=' . $value);
        }
    }
})();

// Error reporting based on APP_DEBUG
$debug = filter_var(getenv('APP_DEBUG') ?: '1', FILTER_VALIDATE_BOOL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('display_startup_errors', $debug ? '1' : '0');
error_reporting($debug ? E_ALL : E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

// Timezone
$timezone = getenv('APP_TIMEZONE') ?: 'Africa/Addis_Ababa';
date_default_timezone_set($timezone);

// Sessions
if (session_status() === PHP_SESSION_NONE) {
    session_name(getenv('SESSION_NAME') ?: 'eth_market_session');
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'cookie_samesite' => 'Lax',
    ]);
}

// Simple PSR-4 style autoloader for App\* namespace
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

// Load app config into a global for convenience (minimal, optional)
$GLOBALS['app_config'] = require BASE_PATH . '/config/app.php';
