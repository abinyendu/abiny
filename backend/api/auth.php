<?php
/**
 * Authentication API Endpoints
 * Handles user registration, login, logout, and authentication
 */

header('Content-Type: application/json');
require_once '../includes/init.php';

$user = new User();
$security = new Security();

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch ($method) {
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            switch ($action) {
                case 'register':
                    if (!$security->validateCSRF($input['csrf_token'] ?? '')) {
                        throw new Exception('Invalid CSRF token');
                    }
                    
                    $result = $user->register($input);
                    echo json_encode($result);
                    break;
                    
                case 'login':
                    if (!$security->checkRateLimit($_SERVER['REMOTE_ADDR'] . '_login', 5, 300)) {
                        throw new Exception('Too many login attempts. Please try again later.');
                    }
                    
                    $result = $user->login(
                        $input['email'] ?? '',
                        $input['password'] ?? '',
                        $input['remember_me'] ?? false
                    );
                    
                    if (!$result['success']) {
                        $security->logSecurityEvent('failed_login', [
                            'email' => $input['email'] ?? '',
                            'ip' => $_SERVER['REMOTE_ADDR']
                        ]);
                    }
                    
                    echo json_encode($result);
                    break;
                    
                case 'logout':
                    $result = $user->logout();
                    echo json_encode($result);
                    break;
                    
                case 'forgot-password':
                    if (!$security->checkRateLimit($_SERVER['REMOTE_ADDR'] . '_forgot', 3, 300)) {
                        throw new Exception('Too many password reset requests. Please try again later.');
                    }
                    
                    $result = $user->requestPasswordReset($input['email'] ?? '');
                    echo json_encode($result);
                    break;
                    
                case 'reset-password':
                    $result = $user->resetPassword(
                        $input['token'] ?? '',
                        $input['password'] ?? ''
                    );
                    echo json_encode($result);
                    break;
                    
                case 'verify-email':
                    $result = $user->verifyEmail($input['token'] ?? '');
                    echo json_encode($result);
                    break;
                    
                case 'change-password':
                    if (!$security->isAuthenticated()) {
                        throw new Exception('Authentication required');
                    }
                    
                    $currentUser = $user->getCurrentUser();
                    $result = $user->changePassword(
                        $currentUser['id'],
                        $input['current_password'] ?? '',
                        $input['new_password'] ?? ''
                    );
                    echo json_encode($result);
                    break;
                    
                default:
                    throw new Exception('Invalid action');
            }
            break;
            
        case 'GET':
            switch ($action) {
                case 'me':
                    $currentUser = $user->getCurrentUser();
                    if ($currentUser) {
                        echo json_encode([
                            'success' => true,
                            'user' => $currentUser
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Not authenticated'
                        ]);
                    }
                    break;
                    
                case 'csrf-token':
                    echo json_encode([
                        'success' => true,
                        'csrf_token' => $security->generateCSRF()
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