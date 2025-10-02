<?php
/**
 * Ethiopian Marketplace - Homepage
 * Main landing page with featured products and categories
 */

require_once '../backend/includes/init.php';

$product = new Product();
$currentUser = (new User())->getCurrentUser();
$categories = getCategories();
$currentLanguage = getCurrentLanguage();
$currentCurrency = getCurrentCurrency();

// Get featured products
$featuredProducts = $product->getProducts(['is_featured' => 1], 1, 8);

// Get new arrivals
$newProducts = $product->getProducts(['sort' => 'newest'], 1, 8);

// Get best sellers
$bestSellers = $product->getProducts(['sort' => 'popular'], 1, 8);

// Get categories with product counts
$categoriesWithCounts = [];
foreach ($categories as $category) {
    $count = $product->getProducts(['category_id' => $category['id']], 1, 1);
    $category['product_count'] = $count['pagination']['total'];
    $categoriesWithCounts[] = $category;
}

$pageTitle = 'Ethiopian Marketplace - Premium Products from Ethiopia';
$pageDescription = 'Discover authentic Ethiopian products including coffee, spices, honey, handicrafts, textiles, and jewelry. Shop from verified local sellers with global shipping.';
?>
<!DOCTYPE html>
<html lang="<?php echo $currentLanguage; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="<?php echo $pageDescription; ?>">
    <meta name="keywords" content="Ethiopian marketplace, Ethiopian products, coffee, spices, handicrafts, textiles, jewelry, honey, organic foods">
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="<?php echo $pageTitle; ?>">
    <meta property="og:description" content="<?php echo $pageDescription; ?>">
    <meta property="og:image" content="<?php echo SITE_URL; ?>/assets/images/og-image.jpg">
    <meta property="og:url" content="<?php echo SITE_URL; ?>">
    <meta property="og:type" content="website">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/icons/favicon.ico">
    <link rel="apple-touch-icon" href="/assets/icons/apple-touch-icon.png">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Styles -->
    <link rel="stylesheet" href="/frontend/css/main.css">
    <link rel="stylesheet" href="/frontend/css/components.css">
    <link rel="stylesheet" href="/frontend/css/responsive.css">
</head>
<body>
    <!-- Header -->
    <?php include 'components/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-slider">
            <div class="hero-slide active" style="background-image: url('/assets/images/hero-1.jpg');">
                <div class="hero-content">
                    <div class="container">
                        <h1 class="hero-title"><?php echo getLanguageText('discover_ethiopia', $currentLanguage); ?></h1>
                        <p class="hero-subtitle"><?php echo getLanguageText('authentic_products', $currentLanguage); ?></p>
                        <div class="hero-buttons">
                            <a href="/products" class="btn btn-primary btn-large">
                                <i class="fas fa-shopping-bag"></i>
                                <?php echo getLanguageText('shop_now', $currentLanguage); ?>
                            </a>
                            <a href="/categories" class="btn btn-secondary btn-large">
                                <i class="fas fa-list"></i>
                                <?php echo getLanguageText('browse_categories', $currentLanguage); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="hero-slide" style="background-image: url('/assets/images/hero-2.jpg');">
                <div class="hero-content">
                    <div class="container">
                        <h1 class="hero-title"><?php echo getLanguageText('premium_coffee', $currentLanguage); ?></h1>
                        <p class="hero-subtitle"><?php echo getLanguageText('worlds_best_coffee', $currentLanguage); ?></p>
                        <div class="hero-buttons">
                            <a href="/categories/coffee" class="btn btn-primary btn-large">
                                <i class="fas fa-coffee"></i>
                                <?php echo getLanguageText('shop_coffee', $currentLanguage); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="hero-slide" style="background-image: url('/assets/images/hero-3.jpg');">
                <div class="hero-content">
                    <div class="container">
                        <h1 class="hero-title"><?php echo getLanguageText('handmade_crafts', $currentLanguage); ?></h1>
                        <p class="hero-subtitle"><?php echo getLanguageText('unique_handicrafts', $currentLanguage); ?></p>
                        <div class="hero-buttons">
                            <a href="/categories/handicrafts" class="btn btn-primary btn-large">
                                <i class="fas fa-palette"></i>
                                <?php echo getLanguageText('explore_crafts', $currentLanguage); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Hero Navigation -->
        <div class="hero-nav">
            <button class="hero-nav-btn prev" onclick="changeSlide(-1)">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="hero-nav-btn next" onclick="changeSlide(1)">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        
        <!-- Hero Indicators -->
        <div class="hero-indicators">
            <span class="indicator active" onclick="currentSlide(1)"></span>
            <span class="indicator" onclick="currentSlide(2)"></span>
            <span class="indicator" onclick="currentSlide(3)"></span>
        </div>
    </section>
    
    <!-- Categories Section -->
    <section class="categories-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title"><?php echo getLanguageText('shop_by_category', $currentLanguage); ?></h2>
                <p class="section-subtitle"><?php echo getLanguageText('discover_authentic_products', $currentLanguage); ?></p>
            </div>
            
            <div class="categories-grid">
                <?php foreach (array_slice($categoriesWithCounts, 0, 8) as $category): ?>
                <div class="category-card">
                    <a href="/categories/<?php echo $category['slug']; ?>" class="category-link">
                        <div class="category-image">
                            <img src="/assets/images/categories/<?php echo $category['image'] ?: 'default.jpg'; ?>" 
                                 alt="<?php echo sanitizeOutput($category['name']); ?>"
                                 loading="lazy">
                            <div class="category-overlay">
                                <i class="<?php echo $category['icon'] ?: 'fas fa-tag'; ?>"></i>
                            </div>
                        </div>
                        <div class="category-info">
                            <h3 class="category-name"><?php echo sanitizeOutput($category['name']); ?></h3>
                            <p class="category-count"><?php echo $category['product_count']; ?> <?php echo getLanguageText('products', $currentLanguage); ?></p>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="section-footer">
                <a href="/categories" class="btn btn-outline">
                    <?php echo getLanguageText('view_all_categories', $currentLanguage); ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>
    
    <!-- Featured Products Section -->
    <section class="featured-products-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title"><?php echo getLanguageText('featured_products', $currentLanguage); ?></h2>
                <p class="section-subtitle"><?php echo getLanguageText('handpicked_premium_products', $currentLanguage); ?></p>
            </div>
            
            <div class="products-grid">
                <?php foreach ($featuredProducts['products'] as $product): ?>
                    <?php include 'components/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
            
            <div class="section-footer">
                <a href="/products?featured=1" class="btn btn-primary">
                    <?php echo getLanguageText('view_all_featured', $currentLanguage); ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>
    
    <!-- New Arrivals Section -->
    <section class="new-arrivals-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title"><?php echo getLanguageText('new_arrivals', $currentLanguage); ?></h2>
                <p class="section-subtitle"><?php echo getLanguageText('latest_products_from_sellers', $currentLanguage); ?></p>
            </div>
            
            <div class="products-carousel" id="newArrivalsCarousel">
                <div class="products-track">
                    <?php foreach ($newProducts['products'] as $product): ?>
                        <div class="product-slide">
                            <?php include 'components/product-card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <button class="carousel-btn prev" onclick="moveCarousel('newArrivalsCarousel', -1)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="carousel-btn next" onclick="moveCarousel('newArrivalsCarousel', 1)">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </section>
    
    <!-- Best Sellers Section -->
    <section class="best-sellers-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title"><?php echo getLanguageText('best_sellers', $currentLanguage); ?></h2>
                <p class="section-subtitle"><?php echo getLanguageText('most_popular_products', $currentLanguage); ?></p>
            </div>
            
            <div class="products-grid">
                <?php foreach ($bestSellers['products'] as $product): ?>
                    <?php include 'components/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <h3 class="feature-title"><?php echo getLanguageText('free_shipping', $currentLanguage); ?></h3>
                    <p class="feature-description"><?php echo getLanguageText('free_shipping_over', $currentLanguage); ?> <?php echo formatPrice(FREE_SHIPPING_THRESHOLD, $currentCurrency); ?></p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="feature-title"><?php echo getLanguageText('secure_payment', $currentLanguage); ?></h3>
                    <p class="feature-description"><?php echo getLanguageText('100_secure_payment', $currentLanguage); ?></p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-undo"></i>
                    </div>
                    <h3 class="feature-title"><?php echo getLanguageText('easy_returns', $currentLanguage); ?></h3>
                    <p class="feature-description"><?php echo getLanguageText('30_day_return_policy', $currentLanguage); ?></p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="feature-title"><?php echo getLanguageText('24_7_support', $currentLanguage); ?></h3>
                    <p class="feature-description"><?php echo getLanguageText('dedicated_customer_support', $currentLanguage); ?></p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Newsletter Section -->
    <section class="newsletter-section">
        <div class="container">
            <div class="newsletter-content">
                <div class="newsletter-text">
                    <h2 class="newsletter-title"><?php echo getLanguageText('stay_updated', $currentLanguage); ?></h2>
                    <p class="newsletter-subtitle"><?php echo getLanguageText('get_latest_offers', $currentLanguage); ?></p>
                </div>
                
                <form class="newsletter-form" id="newsletterForm">
                    <div class="form-group">
                        <input type="email" 
                               class="form-control" 
                               placeholder="<?php echo getLanguageText('enter_email', $currentLanguage); ?>" 
                               required>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i>
                            <?php echo getLanguageText('subscribe', $currentLanguage); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <?php include 'components/footer.php'; ?>
    
    <!-- Quick View Modal -->
    <div id="quickViewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?php echo getLanguageText('quick_view', $currentLanguage); ?></h3>
                <button class="modal-close" onclick="closeModal('quickViewModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="quickViewContent">
                <!-- Content loaded dynamically -->
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="/frontend/js/main.js"></script>
    <script src="/frontend/js/carousel.js"></script>
    <script src="/frontend/js/modal.js"></script>
    <script src="/frontend/js/newsletter.js"></script>
    
    <script>
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize hero slider
            initHeroSlider();
            
            // Initialize product carousels
            initCarousels();
            
            // Initialize newsletter form
            initNewsletterForm();
            
            // Log page view
            logActivity('page_view', {
                page: 'homepage',
                user_agent: navigator.userAgent
            });
        });
    </script>
</body>
</html>