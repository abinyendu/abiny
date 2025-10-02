<?php
/**
 * Product Card Component
 * Reusable product card for displaying products in grids and carousels
 */

if (!isset($product)) {
    return;
}

$currentLanguage = getCurrentLanguage();
$currentCurrency = getCurrentCurrency();
$currentUser = (new User())->getCurrentUser();

// Convert price to current currency
$displayPrice = convertCurrency($product['price'], 'ETB', $currentCurrency);
$displayComparePrice = $product['compare_price'] ? convertCurrency($product['compare_price'], 'ETB', $currentCurrency) : null;

// Get main product image
$mainImage = '/assets/images/products/placeholder.jpg';
if (!empty($product['images']) && is_array($product['images'])) {
    $mainImage = '/assets/uploads/products/' . $product['images'][0];
}

// Generate star rating
$fullStars = floor($product['rating']);
$hasHalfStar = ($product['rating'] - $fullStars) >= 0.5;
$emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
?>

<div class="product-card" data-product-id="<?php echo $product['id']; ?>">
    <!-- Product Image -->
    <div class="product-image">
        <a href="/products/<?php echo $product['slug']; ?>">
            <img src="<?php echo $mainImage; ?>" 
                 alt="<?php echo sanitizeOutput($product['name']); ?>"
                 loading="lazy"
                 onerror="this.src='/assets/images/products/placeholder.jpg'">
        </a>
        
        <!-- Product Badges -->
        <div class="product-badges">
            <?php if ($product['is_featured']): ?>
            <span class="product-badge badge-featured"><?php echo getLanguageText('featured', $currentLanguage); ?></span>
            <?php endif; ?>
            
            <?php if ($product['discount_percentage'] > 0): ?>
            <span class="product-badge badge-sale">-<?php echo $product['discount_percentage']; ?>%</span>
            <?php endif; ?>
            
            <?php if (strtotime($product['created_at']) > strtotime('-7 days')): ?>
            <span class="product-badge badge-new"><?php echo getLanguageText('new', $currentLanguage); ?></span>
            <?php endif; ?>
            
            <?php if (!$product['in_stock']): ?>
            <span class="product-badge badge-out-of-stock"><?php echo getLanguageText('out_of_stock', $currentLanguage); ?></span>
            <?php endif; ?>
        </div>
        
        <!-- Product Actions -->
        <div class="product-actions">
            <button class="product-action" 
                    onclick="quickView(<?php echo $product['id']; ?>)"
                    title="<?php echo getLanguageText('quick_view', $currentLanguage); ?>">
                <i class="fas fa-eye"></i>
            </button>
            
            <?php if ($currentUser): ?>
            <button class="product-action wishlist-btn" 
                    onclick="toggleWishlist(<?php echo $product['id']; ?>)"
                    title="<?php echo getLanguageText('add_to_wishlist', $currentLanguage); ?>">
                <i class="fas fa-heart"></i>
            </button>
            <?php endif; ?>
            
            <button class="product-action" 
                    onclick="compareProduct(<?php echo $product['id']; ?>)"
                    title="<?php echo getLanguageText('compare', $currentLanguage); ?>">
                <i class="fas fa-balance-scale"></i>
            </button>
        </div>
    </div>
    
    <!-- Product Info -->
    <div class="product-info">
        <!-- Category -->
        <div class="product-category">
            <?php echo sanitizeOutput($product['category_name'] ?? 'Product'); ?>
        </div>
        
        <!-- Title -->
        <h3 class="product-title">
            <a href="/products/<?php echo $product['slug']; ?>">
                <?php echo sanitizeOutput($product['name']); ?>
            </a>
        </h3>
        
        <!-- Rating -->
        <div class="product-rating">
            <div class="stars">
                <?php for ($i = 0; $i < $fullStars; $i++): ?>
                <i class="fas fa-star star filled"></i>
                <?php endfor; ?>
                
                <?php if ($hasHalfStar): ?>
                <i class="fas fa-star-half-alt star filled"></i>
                <?php endif; ?>
                
                <?php for ($i = 0; $i < $emptyStars; $i++): ?>
                <i class="fas fa-star star"></i>
                <?php endfor; ?>
            </div>
            
            <span class="rating-count">
                (<?php echo $product['total_reviews']; ?> <?php echo getLanguageText('reviews', $currentLanguage); ?>)
            </span>
        </div>
        
        <!-- Price -->
        <div class="product-price">
            <span class="price-current">
                <?php echo formatPrice($displayPrice, $currentCurrency); ?>
            </span>
            
            <?php if ($displayComparePrice && $displayComparePrice > $displayPrice): ?>
            <span class="price-original">
                <?php echo formatPrice($displayComparePrice, $currentCurrency); ?>
            </span>
            
            <span class="price-discount">
                <?php echo getLanguageText('save', $currentLanguage); ?> <?php echo $product['discount_percentage']; ?>%
            </span>
            <?php endif; ?>
        </div>
        
        <!-- Stock Status -->
        <div class="product-stock">
            <?php if ($product['in_stock']): ?>
                <?php if ($product['low_stock']): ?>
                <span class="stock-status low-stock">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo getLanguageText('low_stock', $currentLanguage); ?> (<?php echo $product['stock_quantity']; ?> <?php echo getLanguageText('left', $currentLanguage); ?>)
                </span>
                <?php else: ?>
                <span class="stock-status in-stock">
                    <i class="fas fa-check-circle"></i>
                    <?php echo getLanguageText('in_stock', $currentLanguage); ?>
                </span>
                <?php endif; ?>
            <?php else: ?>
            <span class="stock-status out-of-stock">
                <i class="fas fa-times-circle"></i>
                <?php echo getLanguageText('out_of_stock', $currentLanguage); ?>
            </span>
            <?php endif; ?>
        </div>
        
        <!-- Seller Info -->
        <div class="product-seller">
            <span class="seller-label"><?php echo getLanguageText('sold_by', $currentLanguage); ?>:</span>
            <a href="/sellers/<?php echo $product['seller_id']; ?>" class="seller-name">
                <?php echo sanitizeOutput($product['seller_name'] ?? 'Seller'); ?>
            </a>
            <?php if (isset($product['seller_rating']) && $product['seller_rating'] > 0): ?>
            <span class="seller-rating">
                <i class="fas fa-star"></i>
                <?php echo number_format($product['seller_rating'], 1); ?>
            </span>
            <?php endif; ?>
        </div>
        
        <!-- Action Buttons -->
        <div class="product-buttons">
            <?php if ($product['in_stock']): ?>
            <button class="btn btn-primary btn-add-cart" 
                    onclick="addToCart(<?php echo $product['id']; ?>)"
                    <?php echo !$currentUser ? 'data-login-required="true"' : ''; ?>>
                <i class="fas fa-shopping-cart"></i>
                <?php echo getLanguageText('add_to_cart', $currentLanguage); ?>
            </button>
            
            <button class="btn btn-secondary btn-quick-view" 
                    onclick="quickView(<?php echo $product['id']; ?>)"
                    title="<?php echo getLanguageText('quick_view', $currentLanguage); ?>">
                <i class="fas fa-eye"></i>
            </button>
            <?php else: ?>
            <button class="btn btn-outline" disabled>
                <i class="fas fa-times-circle"></i>
                <?php echo getLanguageText('out_of_stock', $currentLanguage); ?>
            </button>
            <?php endif; ?>
        </div>
        
        <!-- Shipping Info -->
        <?php if ($product['requires_shipping']): ?>
        <div class="product-shipping">
            <?php if ($displayPrice >= convertCurrency(FREE_SHIPPING_THRESHOLD, 'ETB', $currentCurrency)): ?>
            <span class="shipping-free">
                <i class="fas fa-shipping-fast"></i>
                <?php echo getLanguageText('free_shipping', $currentLanguage); ?>
            </span>
            <?php else: ?>
            <span class="shipping-paid">
                <i class="fas fa-truck"></i>
                <?php echo getLanguageText('shipping', $currentLanguage); ?>: 
                <?php echo formatPrice(convertCurrency(DEFAULT_SHIPPING_COST, 'ETB', $currentCurrency), $currentCurrency); ?>
            </span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Additional styles for product card components */
.product-stock {
    margin-bottom: var(--spacing-sm);
    font-size: var(--font-sm);
}

.stock-status {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    font-weight: var(--font-weight-medium);
}

.stock-status.in-stock {
    color: var(--success-color);
}

.stock-status.low-stock {
    color: var(--warning-color);
}

.stock-status.out-of-stock {
    color: var(--danger-color);
}

.product-seller {
    margin-bottom: var(--spacing-md);
    font-size: var(--font-sm);
    color: var(--text-secondary);
}

.seller-label {
    margin-right: var(--spacing-xs);
}

.seller-name {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: var(--font-weight-medium);
}

.seller-name:hover {
    text-decoration: underline;
}

.seller-rating {
    margin-left: var(--spacing-sm);
    color: var(--secondary-color);
    font-size: var(--font-xs);
}

.product-shipping {
    margin-top: var(--spacing-sm);
    font-size: var(--font-xs);
}

.shipping-free {
    color: var(--success-color);
    font-weight: var(--font-weight-medium);
}

.shipping-paid {
    color: var(--text-secondary);
}

.product-shipping i {
    margin-right: var(--spacing-xs);
}

.badge-out-of-stock {
    background-color: var(--danger-color);
    color: var(--white);
}

/* Hover effects for product actions */
.product-card:hover .product-actions {
    opacity: 1;
}

.wishlist-btn.active {
    background-color: var(--danger-color);
    color: var(--white);
}

.wishlist-btn.active:hover {
    background-color: #c82333;
}

/* Loading state for buttons */
.btn.loading {
    position: relative;
    color: transparent;
}

.btn.loading::after {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin-left: -8px;
    margin-top: -8px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .product-buttons {
        flex-direction: column;
        gap: var(--spacing-sm);
    }
    
    .btn-quick-view {
        width: 100%;
        height: auto;
    }
    
    .product-actions {
        opacity: 1;
        position: static;
        flex-direction: row;
        justify-content: center;
        margin-top: var(--spacing-sm);
    }
}
</style>