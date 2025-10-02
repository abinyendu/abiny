<?php
/**
 * Header Component
 * Main site header with navigation, search, and user actions
 */

$currentUser = (new User())->getCurrentUser();
$categories = getCategories();
$cartCount = getCartCount();
$currentLanguage = getCurrentLanguage();
$currentCurrency = getCurrentCurrency();
?>

<header class="header">
    <!-- Header Top Bar -->
    <div class="header-top">
        <div class="container">
            <div class="header-top-left">
                <span><i class="fas fa-phone"></i> +251 11 123 4567</span>
                <span><i class="fas fa-envelope"></i> info@ethiopianmarketplace.com</span>
            </div>
            <div class="header-top-right">
                <!-- Language Selector -->
                <div class="language-selector">
                    <button class="selector-btn" onclick="toggleSelector('language')">
                        <i class="fas fa-globe"></i>
                        <span><?php echo strtoupper($currentLanguage); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="selector-dropdown" id="languageDropdown">
                        <a href="#" class="selector-option <?php echo $currentLanguage === 'en' ? 'active' : ''; ?>" 
                           onclick="changeLanguage('en')">English</a>
                        <a href="#" class="selector-option <?php echo $currentLanguage === 'am' ? 'active' : ''; ?>" 
                           onclick="changeLanguage('am')">አማርኛ</a>
                    </div>
                </div>
                
                <!-- Currency Selector -->
                <div class="currency-selector">
                    <button class="selector-btn" onclick="toggleSelector('currency')">
                        <i class="fas fa-money-bill-wave"></i>
                        <span><?php echo $currentCurrency; ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="selector-dropdown" id="currencyDropdown">
                        <a href="#" class="selector-option <?php echo $currentCurrency === 'ETB' ? 'active' : ''; ?>" 
                           onclick="changeCurrency('ETB')">ETB</a>
                        <a href="#" class="selector-option <?php echo $currentCurrency === 'USD' ? 'active' : ''; ?>" 
                           onclick="changeCurrency('USD')">USD</a>
                        <a href="#" class="selector-option <?php echo $currentCurrency === 'EUR' ? 'active' : ''; ?>" 
                           onclick="changeCurrency('EUR')">EUR</a>
                    </div>
                </div>
                
                <?php if ($currentUser): ?>
                    <span><?php echo getLanguageText('welcome', $currentLanguage); ?>, <?php echo sanitizeOutput($currentUser['first_name']); ?>!</span>
                <?php else: ?>
                    <a href="/login" class="text-white">
                        <i class="fas fa-sign-in-alt"></i>
                        <?php echo getLanguageText('login', $currentLanguage); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Main Header -->
    <div class="header-main">
        <div class="container">
            <div class="header-content">
                <!-- Logo -->
                <a href="/" class="logo">
                    <img src="/assets/images/logo.png" alt="Ethiopian Marketplace" onerror="this.style.display='none'">
                    <span>Ethiopian Marketplace</span>
                </a>
                
                <!-- Search Bar -->
                <div class="search-bar">
                    <form class="search-form" id="searchForm">
                        <input type="text" 
                               class="search-input" 
                               placeholder="<?php echo getLanguageText('search_products', $currentLanguage); ?>"
                               id="searchInput"
                               autocomplete="off">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                    <div class="search-suggestions" id="searchSuggestions">
                        <!-- Dynamic search suggestions will be loaded here -->
                    </div>
                </div>
                
                <!-- Header Actions -->
                <div class="header-actions">
                    <!-- Wishlist -->
                    <?php if ($currentUser): ?>
                    <a href="/wishlist" class="header-action">
                        <i class="fas fa-heart header-action-icon"></i>
                        <span class="header-action-text"><?php echo getLanguageText('wishlist', $currentLanguage); ?></span>
                    </a>
                    <?php endif; ?>
                    
                    <!-- Shopping Cart -->
                    <a href="/cart" class="header-action">
                        <i class="fas fa-shopping-cart header-action-icon"></i>
                        <span class="header-action-text"><?php echo getLanguageText('cart', $currentLanguage); ?></span>
                        <?php if ($cartCount > 0): ?>
                        <span class="cart-count"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <!-- User Account -->
                    <?php if ($currentUser): ?>
                    <div class="header-action dropdown">
                        <a href="/dashboard" class="header-action">
                            <i class="fas fa-user header-action-icon"></i>
                            <span class="header-action-text"><?php echo getLanguageText('account', $currentLanguage); ?></span>
                        </a>
                    </div>
                    <?php else: ?>
                    <a href="/login" class="header-action">
                        <i class="fas fa-sign-in-alt header-action-icon"></i>
                        <span class="header-action-text"><?php echo getLanguageText('login', $currentLanguage); ?></span>
                    </a>
                    <?php endif; ?>
                    
                    <!-- Seller Dashboard -->
                    <?php if ($currentUser && ($currentUser['role'] === 'seller' || $currentUser['role'] === 'admin')): ?>
                    <a href="/seller/dashboard" class="header-action">
                        <i class="fas fa-store header-action-icon"></i>
                        <span class="header-action-text"><?php echo getLanguageText('sell', $currentLanguage); ?></span>
                    </a>
                    <?php else: ?>
                    <a href="/become-seller" class="header-action">
                        <i class="fas fa-store header-action-icon"></i>
                        <span class="header-action-text"><?php echo getLanguageText('sell', $currentLanguage); ?></span>
                    </a>
                    <?php endif; ?>
                    
                    <!-- Mobile Menu Toggle -->
                    <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Navigation -->
    <nav class="navigation">
        <div class="container">
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="/" class="nav-link <?php echo isActiveRoute('/') ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i>
                        <?php echo getLanguageText('home', $currentLanguage); ?>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/categories" class="nav-link <?php echo isActiveRoute('/categories') ? 'active' : ''; ?>">
                        <i class="fas fa-th-large"></i>
                        <?php echo getLanguageText('categories', $currentLanguage); ?>
                        <i class="fas fa-chevron-down"></i>
                    </a>
                    <div class="nav-dropdown">
                        <?php foreach (array_slice($categories, 0, 8) as $category): ?>
                        <a href="/categories/<?php echo $category['slug']; ?>" class="nav-dropdown-item">
                            <i class="<?php echo $category['icon'] ?: 'fas fa-tag'; ?>"></i>
                            <?php echo sanitizeOutput($category['name']); ?>
                        </a>
                        <?php endforeach; ?>
                        <a href="/categories" class="nav-dropdown-item">
                            <i class="fas fa-list"></i>
                            <?php echo getLanguageText('view_all', $currentLanguage); ?>
                        </a>
                    </div>
                </li>
                
                <li class="nav-item">
                    <a href="/products" class="nav-link <?php echo isActiveRoute('/products') ? 'active' : ''; ?>">
                        <i class="fas fa-shopping-bag"></i>
                        <?php echo getLanguageText('products', $currentLanguage); ?>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/products?featured=1" class="nav-link">
                        <i class="fas fa-star"></i>
                        <?php echo getLanguageText('featured', $currentLanguage); ?>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/products?sort=newest" class="nav-link">
                        <i class="fas fa-clock"></i>
                        <?php echo getLanguageText('new_arrivals', $currentLanguage); ?>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/sellers" class="nav-link <?php echo isActiveRoute('/sellers') ? 'active' : ''; ?>">
                        <i class="fas fa-store"></i>
                        <?php echo getLanguageText('sellers', $currentLanguage); ?>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/about" class="nav-link <?php echo isActiveRoute('/about') ? 'active' : ''; ?>">
                        <i class="fas fa-info-circle"></i>
                        <?php echo getLanguageText('about', $currentLanguage); ?>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="/contact" class="nav-link <?php echo isActiveRoute('/contact') ? 'active' : ''; ?>">
                        <i class="fas fa-envelope"></i>
                        <?php echo getLanguageText('contact', $currentLanguage); ?>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</header>

<!-- Mobile Menu -->
<div class="mobile-menu" id="mobileMenu">
    <div class="mobile-menu-header">
        <a href="/" class="logo">
            <img src="/assets/images/logo.png" alt="Ethiopian Marketplace" onerror="this.style.display='none'">
            <span>Ethiopian Marketplace</span>
        </a>
        <button class="mobile-menu-close" onclick="toggleMobileMenu()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <ul class="mobile-nav-menu">
        <li class="mobile-nav-item">
            <a href="/" class="mobile-nav-link">
                <span><i class="fas fa-home"></i> <?php echo getLanguageText('home', $currentLanguage); ?></span>
            </a>
        </li>
        
        <li class="mobile-nav-item">
            <a href="/categories" class="mobile-nav-link">
                <span><i class="fas fa-th-large"></i> <?php echo getLanguageText('categories', $currentLanguage); ?></span>
                <i class="fas fa-chevron-right"></i>
            </a>
        </li>
        
        <li class="mobile-nav-item">
            <a href="/products" class="mobile-nav-link">
                <span><i class="fas fa-shopping-bag"></i> <?php echo getLanguageText('products', $currentLanguage); ?></span>
            </a>
        </li>
        
        <li class="mobile-nav-item">
            <a href="/products?featured=1" class="mobile-nav-link">
                <span><i class="fas fa-star"></i> <?php echo getLanguageText('featured', $currentLanguage); ?></span>
            </a>
        </li>
        
        <li class="mobile-nav-item">
            <a href="/sellers" class="mobile-nav-link">
                <span><i class="fas fa-store"></i> <?php echo getLanguageText('sellers', $currentLanguage); ?></span>
            </a>
        </li>
        
        <?php if ($currentUser): ?>
        <li class="mobile-nav-item">
            <a href="/dashboard" class="mobile-nav-link">
                <span><i class="fas fa-user"></i> <?php echo getLanguageText('dashboard', $currentLanguage); ?></span>
            </a>
        </li>
        
        <li class="mobile-nav-item">
            <a href="/orders" class="mobile-nav-link">
                <span><i class="fas fa-box"></i> <?php echo getLanguageText('orders', $currentLanguage); ?></span>
            </a>
        </li>
        
        <li class="mobile-nav-item">
            <a href="/wishlist" class="mobile-nav-link">
                <span><i class="fas fa-heart"></i> <?php echo getLanguageText('wishlist', $currentLanguage); ?></span>
            </a>
        </li>
        
        <?php if ($currentUser['role'] === 'seller' || $currentUser['role'] === 'admin'): ?>
        <li class="mobile-nav-item">
            <a href="/seller/dashboard" class="mobile-nav-link">
                <span><i class="fas fa-store"></i> <?php echo getLanguageText('seller_dashboard', $currentLanguage); ?></span>
            </a>
        </li>
        <?php endif; ?>
        
        <li class="mobile-nav-item">
            <a href="/logout" class="mobile-nav-link">
                <span><i class="fas fa-sign-out-alt"></i> <?php echo getLanguageText('logout', $currentLanguage); ?></span>
            </a>
        </li>
        <?php else: ?>
        <li class="mobile-nav-item">
            <a href="/login" class="mobile-nav-link">
                <span><i class="fas fa-sign-in-alt"></i> <?php echo getLanguageText('login', $currentLanguage); ?></span>
            </a>
        </li>
        
        <li class="mobile-nav-item">
            <a href="/register" class="mobile-nav-link">
                <span><i class="fas fa-user-plus"></i> <?php echo getLanguageText('register', $currentLanguage); ?></span>
            </a>
        </li>
        <?php endif; ?>
        
        <li class="mobile-nav-item">
            <a href="/about" class="mobile-nav-link">
                <span><i class="fas fa-info-circle"></i> <?php echo getLanguageText('about', $currentLanguage); ?></span>
            </a>
        </li>
        
        <li class="mobile-nav-item">
            <a href="/contact" class="mobile-nav-link">
                <span><i class="fas fa-envelope"></i> <?php echo getLanguageText('contact', $currentLanguage); ?></span>
            </a>
        </li>
    </ul>
</div>

<!-- Mobile Menu Overlay -->
<div class="mobile-menu-overlay" id="mobileMenuOverlay" onclick="toggleMobileMenu()"></div>

<script>
// Header functionality
function toggleSelector(type) {
    const dropdown = document.getElementById(type + 'Dropdown');
    const isActive = dropdown.classList.contains('active');
    
    // Close all dropdowns
    document.querySelectorAll('.selector-dropdown').forEach(d => d.classList.remove('active'));
    
    // Toggle current dropdown
    if (!isActive) {
        dropdown.classList.add('active');
    }
}

function changeLanguage(lang) {
    fetch('/api/settings.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'change_language',
            language: lang
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function changeCurrency(currency) {
    fetch('/api/settings.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'change_currency',
            currency: currency
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function toggleMobileMenu() {
    const mobileMenu = document.getElementById('mobileMenu');
    const overlay = document.getElementById('mobileMenuOverlay');
    
    mobileMenu.classList.toggle('active');
    
    if (mobileMenu.classList.contains('active')) {
        overlay.style.display = 'block';
        document.body.style.overflow = 'hidden';
    } else {
        overlay.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Search functionality
let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    const query = e.target.value.trim();
    
    if (query.length >= 2) {
        searchTimeout = setTimeout(() => {
            fetchSearchSuggestions(query);
        }, 300);
    } else {
        hideSearchSuggestions();
    }
});

function fetchSearchSuggestions(query) {
    fetch(`/api/search.php?action=suggestions&q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.suggestions.length > 0) {
                showSearchSuggestions(data.suggestions);
            } else {
                hideSearchSuggestions();
            }
        })
        .catch(() => hideSearchSuggestions());
}

function showSearchSuggestions(suggestions) {
    const suggestionsContainer = document.getElementById('searchSuggestions');
    suggestionsContainer.innerHTML = '';
    
    suggestions.forEach(suggestion => {
        const div = document.createElement('div');
        div.className = 'search-suggestion';
        div.textContent = suggestion;
        div.onclick = () => {
            document.getElementById('searchInput').value = suggestion;
            hideSearchSuggestions();
            document.getElementById('searchForm').submit();
        };
        suggestionsContainer.appendChild(div);
    });
    
    suggestionsContainer.style.display = 'block';
}

function hideSearchSuggestions() {
    document.getElementById('searchSuggestions').style.display = 'none';
}

// Search form submission
document.getElementById('searchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const query = document.getElementById('searchInput').value.trim();
    if (query) {
        window.location.href = `/search?q=${encodeURIComponent(query)}`;
    }
});

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.language-selector') && !e.target.closest('.currency-selector')) {
        document.querySelectorAll('.selector-dropdown').forEach(d => d.classList.remove('active'));
    }
    
    if (!e.target.closest('.search-bar')) {
        hideSearchSuggestions();
    }
});
</script>

<style>
.mobile-menu-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: calc(var(--z-modal) - 1);
}
</style>