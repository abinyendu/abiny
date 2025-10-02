<?php
/**
 * Security Class
 * Handles password hashing, token generation, and security utilities
 */

class Security {
    
    /**
     * Hash password using PHP's password_hash
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify password against hash
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Generate secure random token
     */
    public function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }

    /**
     * Generate JWT token
     */
    public function generateJWT($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode($payload);
        
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, JWT_SECRET, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }

    /**
     * Verify JWT token
     */
    public function verifyJWT($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }

        $header = $parts[0];
        $payload = $parts[1];
        $signature = $parts[2];

        $expectedSignature = hash_hmac('sha256', $header . "." . $payload, JWT_SECRET, true);
        $expectedSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($expectedSignature));

        if (!hash_equals($signature, $expectedSignature)) {
            return false;
        }

        $payloadData = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $payload)), true);
        
        // Check expiration
        if (isset($payloadData['exp']) && $payloadData['exp'] < time()) {
            return false;
        }

        return $payloadData;
    }

    /**
     * Sanitize input to prevent XSS
     */
    public function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }
        
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate CSRF token
     */
    public function validateCSRF($token) {
        session_start();
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Generate CSRF token
     */
    public function generateCSRF() {
        session_start();
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = $this->generateToken();
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Encrypt sensitive data
     */
    public function encrypt($data) {
        $key = ENCRYPTION_KEY;
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt sensitive data
     */
    public function decrypt($encryptedData) {
        $key = ENCRYPTION_KEY;
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }

    /**
     * Rate limiting check
     */
    public function checkRateLimit($identifier, $maxAttempts = 5, $timeWindow = 300) {
        session_start();
        $key = 'rate_limit_' . $identifier;
        $now = time();

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [];
        }

        // Clean old attempts
        $_SESSION[$key] = array_filter($_SESSION[$key], function($timestamp) use ($now, $timeWindow) {
            return ($now - $timestamp) < $timeWindow;
        });

        // Check if limit exceeded
        if (count($_SESSION[$key]) >= $maxAttempts) {
            return false;
        }

        // Add current attempt
        $_SESSION[$key][] = $now;
        return true;
    }

    /**
     * Validate file upload
     */
    public function validateFileUpload($file, $allowedTypes = null, $maxSize = null) {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new Exception('Invalid file upload');
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new Exception('No file sent');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new Exception('File size exceeds limit');
            default:
                throw new Exception('Unknown upload error');
        }

        $maxSize = $maxSize ?: MAX_FILE_SIZE;
        if ($file['size'] > $maxSize) {
            throw new Exception('File size exceeds maximum allowed size');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        $allowedTypes = $allowedTypes ?: ALLOWED_IMAGE_TYPES;
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        $allowedMimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp'
        ];

        if (!in_array($extension, $allowedTypes) || 
            !isset($allowedMimes[$extension]) || 
            $mimeType !== $allowedMimes[$extension]) {
            throw new Exception('Invalid file type');
        }

        return true;
    }

    /**
     * Generate secure filename
     */
    public function generateSecureFilename($originalName) {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        return $this->generateToken(16) . '.' . $extension;
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated() {
        session_start();
        return isset($_SESSION['user_id']);
    }

    /**
     * Check if user has specific role
     */
    public function hasRole($requiredRole) {
        session_start();
        if (!isset($_SESSION['user_role'])) {
            return false;
        }

        $roles = ['customer' => 1, 'seller' => 2, 'admin' => 3];
        $userRole = $_SESSION['user_role'];
        
        return isset($roles[$userRole]) && 
               isset($roles[$requiredRole]) && 
               $roles[$userRole] >= $roles[$requiredRole];
    }

    /**
     * Log security event
     */
    public function logSecurityEvent($event, $details = []) {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'user_id' => $_SESSION['user_id'] ?? null,
            'details' => $details
        ];

        // Log to file or database
        error_log('SECURITY: ' . json_encode($logData));
    }

    /**
     * Validate and sanitize phone number
     */
    public function validatePhone($phone) {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Ethiopian phone number validation
        if (preg_match('/^(251|0)?([79]\d{8})$/', $phone, $matches)) {
            return '251' . $matches[2]; // Normalize to international format
        }
        
        return false;
    }

    /**
     * Validate Ethiopian Birr amount
     */
    public function validateAmount($amount) {
        if (!is_numeric($amount) || $amount < 0) {
            return false;
        }
        
        return round($amount, 2);
    }

    /**
     * Generate order number
     */
    public function generateOrderNumber() {
        return 'EM' . date('Ymd') . strtoupper($this->generateToken(4));
    }

    /**
     * Generate ticket number
     */
    public function generateTicketNumber() {
        return 'TK' . date('Ymd') . strtoupper($this->generateToken(3));
    }
}
?>