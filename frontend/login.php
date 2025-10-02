<?php
/**
 * Login Page
 * User authentication and login form
 */

require_once '../backend/includes/init.php';

$user = new User();
$security = new Security();

// Redirect if already logged in
$currentUser = $user->getCurrentUser();
if ($currentUser) {
    header('Location: /dashboard');
    exit;
}

$currentLanguage = getCurrentLanguage();
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$security->validateCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $result = $user->login(
            $_POST['email'] ?? '',
            $_POST['password'] ?? '',
            isset($_POST['remember_me'])
        );
        
        if ($result['success']) {
            $redirectUrl = $_GET['redirect'] ?? '/dashboard';
            header('Location: ' . $redirectUrl);
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

$pageTitle = getLanguageText('login', $currentLanguage) . ' - Ethiopian Marketplace';
?>
<!DOCTYPE html>
<html lang="<?php echo $currentLanguage; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="Login to your Ethiopian Marketplace account to access your orders, wishlist, and seller dashboard.">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Styles -->
    <link rel="stylesheet" href="/frontend/css/main.css">
    <link rel="stylesheet" href="/frontend/css/components.css">
    <link rel="stylesheet" href="/frontend/css/auth.css">
</head>
<body class="auth-page">
    <!-- Header (Simplified) -->
    <header class="auth-header">
        <div class="container">
            <a href="/" class="logo">
                <img src="/assets/images/logo.png" alt="Ethiopian Marketplace" onerror="this.style.display='none'">
                <span>Ethiopian Marketplace</span>
            </a>
            
            <div class="auth-header-links">
                <a href="/register"><?php echo getLanguageText('register', $currentLanguage); ?></a>
                <a href="/help"><?php echo getLanguageText('help', $currentLanguage); ?></a>
            </div>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="auth-main">
        <div class="container">
            <div class="auth-container">
                <!-- Left Side - Login Form -->
                <div class="auth-form-section">
                    <div class="auth-form-container">
                        <h1 class="auth-title"><?php echo getLanguageText('welcome_back', $currentLanguage); ?></h1>
                        <p class="auth-subtitle"><?php echo getLanguageText('sign_in_to_account', $currentLanguage); ?></p>
                        
                        <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo sanitizeOutput($error); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo sanitizeOutput($success); ?>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" class="auth-form">
                            <input type="hidden" name="csrf_token" value="<?php echo $security->generateCSRF(); ?>">
                            
                            <div class="form-group">
                                <label for="email" class="form-label">
                                    <?php echo getLanguageText('email_address', $currentLanguage); ?>
                                </label>
                                <div class="input-group">
                                    <i class="fas fa-envelope input-icon"></i>
                                    <input type="email" 
                                           id="email" 
                                           name="email" 
                                           class="form-control" 
                                           placeholder="<?php echo getLanguageText('enter_email', $currentLanguage); ?>"
                                           value="<?php echo sanitizeOutput($_POST['email'] ?? ''); ?>"
                                           required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="password" class="form-label">
                                    <?php echo getLanguageText('password', $currentLanguage); ?>
                                </label>
                                <div class="input-group">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input type="password" 
                                           id="password" 
                                           name="password" 
                                           class="form-control" 
                                           placeholder="<?php echo getLanguageText('enter_password', $currentLanguage); ?>"
                                           required>
                                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-check">
                                    <input type="checkbox" id="remember_me" name="remember_me" class="form-check-input">
                                    <label for="remember_me" class="form-check-label">
                                        <?php echo getLanguageText('remember_me', $currentLanguage); ?>
                                    </label>
                                </div>
                                
                                <a href="/forgot-password" class="forgot-password-link">
                                    <?php echo getLanguageText('forgot_password', $currentLanguage); ?>?
                                </a>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-large btn-block">
                                <i class="fas fa-sign-in-alt"></i>
                                <?php echo getLanguageText('sign_in', $currentLanguage); ?>
                            </button>
                        </form>
                        
                        <!-- Social Login -->
                        <div class="social-login">
                            <div class="divider">
                                <span><?php echo getLanguageText('or_continue_with', $currentLanguage); ?></span>
                            </div>
                            
                            <div class="social-buttons">
                                <button class="btn btn-social btn-google">
                                    <i class="fab fa-google"></i>
                                    Google
                                </button>
                                
                                <button class="btn btn-social btn-facebook">
                                    <i class="fab fa-facebook-f"></i>
                                    Facebook
                                </button>
                            </div>
                        </div>
                        
                        <!-- Register Link -->
                        <div class="auth-footer">
                            <p>
                                <?php echo getLanguageText('dont_have_account', $currentLanguage); ?>
                                <a href="/register" class="register-link">
                                    <?php echo getLanguageText('create_account', $currentLanguage); ?>
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Right Side - Benefits -->
                <div class="auth-benefits-section">
                    <div class="auth-benefits-container">
                        <h2><?php echo getLanguageText('why_join_us', $currentLanguage); ?></h2>
                        
                        <div class="benefits-list">
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <div class="benefit-content">
                                    <h3><?php echo getLanguageText('authentic_products', $currentLanguage); ?></h3>
                                    <p><?php echo getLanguageText('authentic_products_desc', $currentLanguage); ?></p>
                                </div>
                            </div>
                            
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="fas fa-shipping-fast"></i>
                                </div>
                                <div class="benefit-content">
                                    <h3><?php echo getLanguageText('global_shipping', $currentLanguage); ?></h3>
                                    <p><?php echo getLanguageText('global_shipping_desc', $currentLanguage); ?></p>
                                </div>
                            </div>
                            
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div class="benefit-content">
                                    <h3><?php echo getLanguageText('secure_payments', $currentLanguage); ?></h3>
                                    <p><?php echo getLanguageText('secure_payments_desc', $currentLanguage); ?></p>
                                </div>
                            </div>
                            
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="fas fa-headset"></i>
                                </div>
                                <div class="benefit-content">
                                    <h3><?php echo getLanguageText('customer_support', $currentLanguage); ?></h3>
                                    <p><?php echo getLanguageText('customer_support_desc', $currentLanguage); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Testimonial -->
                        <div class="testimonial">
                            <div class="testimonial-content">
                                <p>"<?php echo getLanguageText('testimonial_text', $currentLanguage); ?>"</p>
                                <div class="testimonial-author">
                                    <img src="/assets/images/testimonials/customer1.jpg" alt="Customer" onerror="this.style.display='none'">
                                    <div>
                                        <strong>Sarah Johnson</strong>
                                        <span><?php echo getLanguageText('verified_buyer', $currentLanguage); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Footer (Simplified) -->
    <footer class="auth-footer-section">
        <div class="container">
            <div class="auth-footer-content">
                <div class="footer-links">
                    <a href="/about"><?php echo getLanguageText('about', $currentLanguage); ?></a>
                    <a href="/privacy"><?php echo getLanguageText('privacy', $currentLanguage); ?></a>
                    <a href="/terms"><?php echo getLanguageText('terms', $currentLanguage); ?></a>
                    <a href="/contact"><?php echo getLanguageText('contact', $currentLanguage); ?></a>
                </div>
                
                <div class="footer-social">
                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 Ethiopian Marketplace. <?php echo getLanguageText('all_rights_reserved', $currentLanguage); ?></p>
            </div>
        </div>
    </footer>
    
    <script>
        // Toggle password visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const toggle = input.nextElementSibling;
            const icon = toggle.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Form validation
        document.querySelector('.auth-form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('<?php echo getLanguageText('please_fill_all_fields', $currentLanguage); ?>');
                return;
            }
            
            if (!isValidEmail(email)) {
                e.preventDefault();
                alert('<?php echo getLanguageText('please_enter_valid_email', $currentLanguage); ?>');
                return;
            }
        });
        
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        // Auto-focus first input
        document.getElementById('email').focus();
    </script>
</body>
</html>