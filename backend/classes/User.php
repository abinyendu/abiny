<?php
/**
 * User Management Class
 * Handles user registration, authentication, and profile management
 */

require_once 'Database.php';
require_once 'Security.php';

class User {
    private $db;
    private $security;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->security = new Security();
    }

    /**
     * Register a new user
     */
    public function register($userData) {
        try {
            // Validate input
            $this->validateRegistrationData($userData);

            // Check if email already exists
            if ($this->emailExists($userData['email'])) {
                throw new Exception('Email already registered');
            }

            // Hash password
            $hashedPassword = $this->security->hashPassword($userData['password']);

            // Generate email verification token
            $verificationToken = $this->security->generateToken();

            // Prepare user data
            $userInsertData = [
                'email' => strtolower(trim($userData['email'])),
                'password_hash' => $hashedPassword,
                'first_name' => trim($userData['first_name']),
                'last_name' => trim($userData['last_name']),
                'phone' => isset($userData['phone']) ? trim($userData['phone']) : null,
                'role' => isset($userData['role']) ? $userData['role'] : 'customer',
                'email_verification_token' => $verificationToken,
                'language_preference' => isset($userData['language']) ? $userData['language'] : 'en',
                'currency_preference' => isset($userData['currency']) ? $userData['currency'] : 'ETB'
            ];

            // Insert user
            $userId = $this->db->insert('users', $userInsertData);

            // Send verification email
            $this->sendVerificationEmail($userData['email'], $verificationToken);

            return [
                'success' => true,
                'user_id' => $userId,
                'message' => 'Registration successful. Please check your email to verify your account.'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Login user
     */
    public function login($email, $password, $rememberMe = false) {
        try {
            $email = strtolower(trim($email));
            
            // Get user by email
            $user = $this->db->fetchOne(
                "SELECT * FROM users WHERE email = ? AND is_active = 1",
                [$email]
            );

            if (!$user) {
                throw new Exception('Invalid email or password');
            }

            // Verify password
            if (!$this->security->verifyPassword($password, $user['password_hash'])) {
                throw new Exception('Invalid email or password');
            }

            // Update last login
            $this->db->update('users', 
                ['last_login' => date('Y-m-d H:i:s')], 
                'id = ?', 
                [$user['id']]
            );

            // Create session
            $this->createSession($user, $rememberMe);

            // Remove sensitive data
            unset($user['password_hash'], $user['email_verification_token'], $user['password_reset_token']);

            return [
                'success' => true,
                'user' => $user,
                'message' => 'Login successful'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Logout user
     */
    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        
        // Clear remember me cookie if exists
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }

        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    /**
     * Get current logged-in user
     */
    public function getCurrentUser() {
        session_start();
        
        if (isset($_SESSION['user_id'])) {
            $user = $this->getUserById($_SESSION['user_id']);
            if ($user) {
                unset($user['password_hash'], $user['email_verification_token'], $user['password_reset_token']);
                return $user;
            }
        }

        // Check remember me token
        if (isset($_COOKIE['remember_token'])) {
            $user = $this->getUserByRememberToken($_COOKIE['remember_token']);
            if ($user) {
                $this->createSession($user);
                unset($user['password_hash'], $user['email_verification_token'], $user['password_reset_token']);
                return $user;
            }
        }

        return null;
    }

    /**
     * Update user profile
     */
    public function updateProfile($userId, $data) {
        try {
            $allowedFields = [
                'first_name', 'last_name', 'phone', 'date_of_birth', 
                'gender', 'language_preference', 'currency_preference', 'profile_image'
            ];

            $updateData = [];
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = trim($data[$field]);
                }
            }

            if (empty($updateData)) {
                throw new Exception('No valid fields to update');
            }

            $updateData['updated_at'] = date('Y-m-d H:i:s');

            $rowsAffected = $this->db->update('users', $updateData, 'id = ?', [$userId]);

            return [
                'success' => true,
                'message' => 'Profile updated successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Change password
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // Get current user
            $user = $this->getUserById($userId);
            if (!$user) {
                throw new Exception('User not found');
            }

            // Verify current password
            if (!$this->security->verifyPassword($currentPassword, $user['password_hash'])) {
                throw new Exception('Current password is incorrect');
            }

            // Validate new password
            if (strlen($newPassword) < 8) {
                throw new Exception('New password must be at least 8 characters long');
            }

            // Hash new password
            $hashedPassword = $this->security->hashPassword($newPassword);

            // Update password
            $this->db->update('users', 
                ['password_hash' => $hashedPassword, 'updated_at' => date('Y-m-d H:i:s')], 
                'id = ?', 
                [$userId]
            );

            return [
                'success' => true,
                'message' => 'Password changed successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Request password reset
     */
    public function requestPasswordReset($email) {
        try {
            $email = strtolower(trim($email));
            
            $user = $this->db->fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
            
            if (!$user) {
                // Don't reveal if email exists or not
                return [
                    'success' => true,
                    'message' => 'If the email exists, a password reset link has been sent'
                ];
            }

            // Generate reset token
            $resetToken = $this->security->generateToken();
            $resetExpires = date('Y-m-d H:i:s', time() + PASSWORD_RESET_EXPIRY);

            // Update user with reset token
            $this->db->update('users', 
                [
                    'password_reset_token' => $resetToken,
                    'password_reset_expires' => $resetExpires
                ], 
                'id = ?', 
                [$user['id']]
            );

            // Send reset email
            $this->sendPasswordResetEmail($email, $resetToken);

            return [
                'success' => true,
                'message' => 'If the email exists, a password reset link has been sent'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred. Please try again later.'
            ];
        }
    }

    /**
     * Reset password with token
     */
    public function resetPassword($token, $newPassword) {
        try {
            // Validate new password
            if (strlen($newPassword) < 8) {
                throw new Exception('Password must be at least 8 characters long');
            }

            // Find user by reset token
            $user = $this->db->fetchOne(
                "SELECT * FROM users WHERE password_reset_token = ? AND password_reset_expires > NOW()",
                [$token]
            );

            if (!$user) {
                throw new Exception('Invalid or expired reset token');
            }

            // Hash new password
            $hashedPassword = $this->security->hashPassword($newPassword);

            // Update password and clear reset token
            $this->db->update('users', 
                [
                    'password_hash' => $hashedPassword,
                    'password_reset_token' => null,
                    'password_reset_expires' => null,
                    'updated_at' => date('Y-m-d H:i:s')
                ], 
                'id = ?', 
                [$user['id']]
            );

            return [
                'success' => true,
                'message' => 'Password reset successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify email address
     */
    public function verifyEmail($token) {
        try {
            $user = $this->db->fetchOne(
                "SELECT * FROM users WHERE email_verification_token = ?",
                [$token]
            );

            if (!$user) {
                throw new Exception('Invalid verification token');
            }

            // Update user as verified
            $this->db->update('users', 
                [
                    'email_verified' => 1,
                    'email_verification_token' => null,
                    'updated_at' => date('Y-m-d H:i:s')
                ], 
                'id = ?', 
                [$user['id']]
            );

            return [
                'success' => true,
                'message' => 'Email verified successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get user by ID
     */
    public function getUserById($userId) {
        return $this->db->fetchOne("SELECT * FROM users WHERE id = ? AND is_active = 1", [$userId]);
    }

    /**
     * Get user by email
     */
    public function getUserByEmail($email) {
        return $this->db->fetchOne("SELECT * FROM users WHERE email = ? AND is_active = 1", [strtolower(trim($email))]);
    }

    /**
     * Check if email exists
     */
    private function emailExists($email) {
        return $this->db->exists('users', 'email = ?', [strtolower(trim($email))]);
    }

    /**
     * Validate registration data
     */
    private function validateRegistrationData($data) {
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Valid email is required');
        }

        if (empty($data['password']) || strlen($data['password']) < 8) {
            throw new Exception('Password must be at least 8 characters long');
        }

        if (empty($data['first_name']) || strlen(trim($data['first_name'])) < 2) {
            throw new Exception('First name must be at least 2 characters long');
        }

        if (empty($data['last_name']) || strlen(trim($data['last_name'])) < 2) {
            throw new Exception('Last name must be at least 2 characters long');
        }
    }

    /**
     * Create user session
     */
    private function createSession($user, $rememberMe = false) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];

        if ($rememberMe) {
            $rememberToken = $this->security->generateToken();
            setcookie('remember_token', $rememberToken, time() + (30 * 24 * 60 * 60), '/'); // 30 days
            
            // Store remember token in database (you'd need to add this field to users table)
            // $this->db->update('users', ['remember_token' => $rememberToken], 'id = ?', [$user['id']]);
        }
    }

    /**
     * Get user by remember token
     */
    private function getUserByRememberToken($token) {
        // This would require adding remember_token field to users table
        // return $this->db->fetchOne("SELECT * FROM users WHERE remember_token = ? AND is_active = 1", [$token]);
        return null;
    }

    /**
     * Send verification email
     */
    private function sendVerificationEmail($email, $token) {
        // Implementation would depend on your email service
        // This is a placeholder for the email sending logic
        $verificationUrl = SITE_URL . "/verify-email.php?token=" . $token;
        
        // You would implement actual email sending here
        // For now, we'll just log it or store it for testing
        error_log("Verification email for {$email}: {$verificationUrl}");
    }

    /**
     * Send password reset email
     */
    private function sendPasswordResetEmail($email, $token) {
        // Implementation would depend on your email service
        $resetUrl = SITE_URL . "/reset-password.php?token=" . $token;
        
        // You would implement actual email sending here
        error_log("Password reset email for {$email}: {$resetUrl}");
    }
}
?>