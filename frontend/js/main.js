/**
 * Ethiopian Marketplace - Main JavaScript
 * Core functionality and utilities
 */

// Global variables
let currentUser = null;
let cartCount = 0;
let wishlistItems = [];
let compareItems = [];

// Initialize application
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

/**
 * Initialize the application
 */
function initializeApp() {
    // Get current user
    getCurrentUser();
    
    // Initialize components
    initializeModals();
    initializeTooltips();
    initializeNotifications();
    
    // Set up event listeners
    setupEventListeners();
    
    // Initialize lazy loading
    initializeLazyLoading();
    
    // Initialize scroll effects
    initializeScrollEffects();
    
    console.log('Ethiopian Marketplace initialized successfully');
}

/**
 * Get current user information
 */
async function getCurrentUser() {
    try {
        const response = await fetch('/backend/api/auth.php?action=me');
        const data = await response.json();
        
        if (data.success) {
            currentUser = data.user;
            updateUIForUser();
        }
    } catch (error) {
        console.error('Error fetching user data:', error);
    }
}

/**
 * Update UI based on user authentication status
 */
function updateUIForUser() {
    const loginRequiredElements = document.querySelectorAll('[data-login-required]');
    
    loginRequiredElements.forEach(element => {
        if (!currentUser) {
            element.addEventListener('click', function(e) {
                e.preventDefault();
                showLoginModal();
            });
        }
    });
}

/**
 * Setup global event listeners
 */
function setupEventListeners() {
    // Handle AJAX form submissions
    document.addEventListener('submit', handleFormSubmission);
    
    // Handle click events
    document.addEventListener('click', handleClickEvents);
    
    // Handle scroll events
    window.addEventListener('scroll', handleScrollEvents);
    
    // Handle resize events
    window.addEventListener('resize', handleResizeEvents);
    
    // Handle keyboard events
    document.addEventListener('keydown', handleKeyboardEvents);
}

/**
 * Handle form submissions
 */
async function handleFormSubmission(e) {
    const form = e.target;
    
    if (form.classList.contains('ajax-form')) {
        e.preventDefault();
        await submitAjaxForm(form);
    }
}

/**
 * Submit AJAX form
 */
async function submitAjaxForm(form) {
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Show loading state
    if (submitBtn) {
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
    }
    
    try {
        const response = await fetch(form.action, {
            method: form.method || 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message || 'Success!', 'success');
            
            // Handle specific form types
            if (form.classList.contains('login-form')) {
                window.location.reload();
            } else if (form.classList.contains('contact-form')) {
                form.reset();
            }
        } else {
            showNotification(data.message || 'An error occurred', 'error');
        }
    } catch (error) {
        console.error('Form submission error:', error);
        showNotification('Network error. Please try again.', 'error');
    } finally {
        // Remove loading state
        if (submitBtn) {
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
        }
    }
}

/**
 * Handle click events
 */
function handleClickEvents(e) {
    const target = e.target.closest('[data-action]');
    
    if (target) {
        const action = target.dataset.action;
        
        switch (action) {
            case 'add-to-cart':
                e.preventDefault();
                addToCart(target.dataset.productId);
                break;
            case 'remove-from-cart':
                e.preventDefault();
                removeFromCart(target.dataset.itemId);
                break;
            case 'toggle-wishlist':
                e.preventDefault();
                toggleWishlist(target.dataset.productId);
                break;
            case 'quick-view':
                e.preventDefault();
                quickView(target.dataset.productId);
                break;
            case 'compare':
                e.preventDefault();
                compareProduct(target.dataset.productId);
                break;
        }
    }
}

/**
 * Add product to cart
 */
async function addToCart(productId, quantity = 1, variantId = null) {
    if (!currentUser) {
        showLoginModal();
        return;
    }
    
    try {
        const response = await fetch('/backend/api/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add',
                product_id: productId,
                quantity: quantity,
                variant_id: variantId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Product added to cart!', 'success');
            updateCartCount();
            
            // Update cart icon animation
            animateCartIcon();
        } else {
            showNotification(data.message || 'Failed to add to cart', 'error');
        }
    } catch (error) {
        console.error('Add to cart error:', error);
        showNotification('Network error. Please try again.', 'error');
    }
}

/**
 * Remove item from cart
 */
async function removeFromCart(itemId) {
    try {
        const response = await fetch('/backend/api/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'remove',
                item_id: itemId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Item removed from cart', 'success');
            updateCartCount();
            
            // Remove item from DOM if on cart page
            const itemElement = document.querySelector(`[data-item-id="${itemId}"]`);
            if (itemElement) {
                itemElement.remove();
            }
        } else {
            showNotification(data.message || 'Failed to remove item', 'error');
        }
    } catch (error) {
        console.error('Remove from cart error:', error);
        showNotification('Network error. Please try again.', 'error');
    }
}

/**
 * Toggle product in wishlist
 */
async function toggleWishlist(productId) {
    if (!currentUser) {
        showLoginModal();
        return;
    }
    
    try {
        const response = await fetch('/backend/api/wishlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'toggle',
                product_id: productId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            const isAdded = data.action === 'added';
            showNotification(
                isAdded ? 'Added to wishlist!' : 'Removed from wishlist!', 
                'success'
            );
            
            // Update wishlist button
            updateWishlistButton(productId, isAdded);
        } else {
            showNotification(data.message || 'Failed to update wishlist', 'error');
        }
    } catch (error) {
        console.error('Wishlist error:', error);
        showNotification('Network error. Please try again.', 'error');
    }
}

/**
 * Show product quick view modal
 */
async function quickView(productId) {
    try {
        const response = await fetch(`/backend/api/products.php?action=quick-view&id=${productId}`);
        const data = await response.json();
        
        if (data.success) {
            showModal('quickViewModal', data.html);
        } else {
            showNotification('Failed to load product details', 'error');
        }
    } catch (error) {
        console.error('Quick view error:', error);
        showNotification('Network error. Please try again.', 'error');
    }
}

/**
 * Add product to comparison
 */
function compareProduct(productId) {
    const maxCompareItems = 4;
    
    if (compareItems.includes(productId)) {
        showNotification('Product already in comparison', 'warning');
        return;
    }
    
    if (compareItems.length >= maxCompareItems) {
        showNotification(`You can compare up to ${maxCompareItems} products`, 'warning');
        return;
    }
    
    compareItems.push(productId);
    localStorage.setItem('compareItems', JSON.stringify(compareItems));
    
    showNotification('Product added to comparison!', 'success');
    updateCompareButton(productId, true);
}

/**
 * Update cart count in header
 */
async function updateCartCount() {
    try {
        const response = await fetch('/backend/api/cart.php?action=count');
        const data = await response.json();
        
        if (data.success) {
            cartCount = data.count;
            
            const cartCountElement = document.querySelector('.cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = cartCount;
                cartCountElement.style.display = cartCount > 0 ? 'block' : 'none';
            }
        }
    } catch (error) {
        console.error('Update cart count error:', error);
    }
}

/**
 * Animate cart icon when item is added
 */
function animateCartIcon() {
    const cartIcon = document.querySelector('.header-action .fa-shopping-cart');
    if (cartIcon) {
        cartIcon.style.animation = 'bounce 0.6s ease-in-out';
        setTimeout(() => {
            cartIcon.style.animation = '';
        }, 600);
    }
}

/**
 * Update wishlist button state
 */
function updateWishlistButton(productId, isInWishlist) {
    const buttons = document.querySelectorAll(`[data-product-id="${productId}"] .wishlist-btn`);
    
    buttons.forEach(button => {
        if (isInWishlist) {
            button.classList.add('active');
            button.title = 'Remove from wishlist';
        } else {
            button.classList.remove('active');
            button.title = 'Add to wishlist';
        }
    });
}

/**
 * Update compare button state
 */
function updateCompareButton(productId, isInCompare) {
    const buttons = document.querySelectorAll(`[data-product-id="${productId}"] .compare-btn`);
    
    buttons.forEach(button => {
        if (isInCompare) {
            button.classList.add('active');
            button.title = 'Remove from comparison';
        } else {
            button.classList.remove('active');
            button.title = 'Add to comparison';
        }
    });
}

/**
 * Show notification
 */
function showNotification(message, type = 'info', duration = 5000) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
            <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    // Add to notifications container
    let container = document.querySelector('.notifications-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'notifications-container';
        document.body.appendChild(container);
    }
    
    container.appendChild(notification);
    
    // Animate in
    setTimeout(() => notification.classList.add('show'), 100);
    
    // Auto remove
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, duration);
}

/**
 * Get notification icon based on type
 */
function getNotificationIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

/**
 * Show login modal
 */
function showLoginModal() {
    showModal('loginModal');
}

/**
 * Show modal
 */
function showModal(modalId, content = null) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    
    if (content) {
        const modalBody = modal.querySelector('.modal-body');
        if (modalBody) {
            modalBody.innerHTML = content;
        }
    }
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

/**
 * Close modal
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

/**
 * Initialize modals
 */
function initializeModals() {
    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            closeModal(e.target.id);
        }
    });
    
    // Close modal with escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const activeModal = document.querySelector('.modal.active');
            if (activeModal) {
                closeModal(activeModal.id);
            }
        }
    });
}

/**
 * Initialize tooltips
 */
function initializeTooltips() {
    const tooltipElements = document.querySelectorAll('[title]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

/**
 * Show tooltip
 */
function showTooltip(e) {
    const element = e.target;
    const title = element.getAttribute('title');
    
    if (!title) return;
    
    // Remove title to prevent default tooltip
    element.setAttribute('data-original-title', title);
    element.removeAttribute('title');
    
    // Create tooltip
    const tooltip = document.createElement('div');
    tooltip.className = 'custom-tooltip';
    tooltip.textContent = title;
    document.body.appendChild(tooltip);
    
    // Position tooltip
    const rect = element.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
    
    // Show tooltip
    setTimeout(() => tooltip.classList.add('show'), 100);
    
    // Store reference
    element._tooltip = tooltip;
}

/**
 * Hide tooltip
 */
function hideTooltip(e) {
    const element = e.target;
    const tooltip = element._tooltip;
    
    if (tooltip) {
        tooltip.classList.remove('show');
        setTimeout(() => tooltip.remove(), 200);
        delete element._tooltip;
    }
    
    // Restore original title
    const originalTitle = element.getAttribute('data-original-title');
    if (originalTitle) {
        element.setAttribute('title', originalTitle);
        element.removeAttribute('data-original-title');
    }
}

/**
 * Initialize notifications system
 */
function initializeNotifications() {
    // Create notifications container if it doesn't exist
    if (!document.querySelector('.notifications-container')) {
        const container = document.createElement('div');
        container.className = 'notifications-container';
        document.body.appendChild(container);
    }
}

/**
 * Initialize lazy loading for images
 */
function initializeLazyLoading() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[loading="lazy"]').forEach(img => {
            imageObserver.observe(img);
        });
    }
}

/**
 * Initialize scroll effects
 */
function initializeScrollEffects() {
    // Scroll to top button
    const scrollTopBtn = document.createElement('button');
    scrollTopBtn.className = 'scroll-top-btn';
    scrollTopBtn.innerHTML = '<i class="fas fa-chevron-up"></i>';
    scrollTopBtn.onclick = () => window.scrollTo({ top: 0, behavior: 'smooth' });
    document.body.appendChild(scrollTopBtn);
    
    // Show/hide scroll to top button
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            scrollTopBtn.classList.add('show');
        } else {
            scrollTopBtn.classList.remove('show');
        }
    });
}

/**
 * Handle scroll events
 */
function handleScrollEvents() {
    // Add any scroll-based functionality here
}

/**
 * Handle resize events
 */
function handleResizeEvents() {
    // Add any resize-based functionality here
}

/**
 * Handle keyboard events
 */
function handleKeyboardEvents(e) {
    // Global keyboard shortcuts
    if (e.ctrlKey || e.metaKey) {
        switch (e.key) {
            case 'k':
                e.preventDefault();
                document.querySelector('.search-input')?.focus();
                break;
        }
    }
}

/**
 * Utility function to debounce function calls
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Utility function to throttle function calls
 */
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

/**
 * Format price with currency
 */
function formatPrice(amount, currency = 'ETB') {
    const formatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency === 'ETB' ? 'USD' : currency, // Fallback for ETB
        minimumFractionDigits: 2
    });
    
    if (currency === 'ETB') {
        return `ETB ${amount.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
    }
    
    return formatter.format(amount);
}

/**
 * Log activity for analytics
 */
async function logActivity(action, data = {}) {
    try {
        await fetch('/backend/api/analytics.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: action,
                data: data,
                timestamp: new Date().toISOString(),
                url: window.location.href
            })
        });
    } catch (error) {
        // Silently fail for analytics
        console.debug('Analytics error:', error);
    }
}

// Export functions for global use
window.addToCart = addToCart;
window.removeFromCart = removeFromCart;
window.toggleWishlist = toggleWishlist;
window.quickView = quickView;
window.compareProduct = compareProduct;
window.showModal = showModal;
window.closeModal = closeModal;
window.showNotification = showNotification;
window.logActivity = logActivity;