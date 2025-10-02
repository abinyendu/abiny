<?php
/**
 * Footer Component
 * Main site footer with links, newsletter, and company information
 */

$currentLanguage = getCurrentLanguage();
$categories = getCategories();
?>

<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <!-- Company Info -->
            <div class="footer-section">
                <h3>Ethiopian Marketplace</h3>
                <p><?php echo getLanguageText('footer_description', $currentLanguage); ?></p>
                
                <div class="footer-contact">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Addis Ababa, Ethiopia</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span>+251 11 123 4567</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>info@ethiopianmarketplace.com</span>
                    </div>
                </div>
                
                <div class="footer-social">
                    <a href="#" class="social-link" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-link" aria-label="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="social-link" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="social-link" aria-label="LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="#" class="social-link" aria-label="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="footer-section">
                <h3><?php echo getLanguageText('quick_links', $currentLanguage); ?></h3>
                <ul class="footer-links">
                    <li><a href="/about"><?php echo getLanguageText('about_us', $currentLanguage); ?></a></li>
                    <li><a href="/how-it-works"><?php echo getLanguageText('how_it_works', $currentLanguage); ?></a></li>
                    <li><a href="/shipping-info"><?php echo getLanguageText('shipping_info', $currentLanguage); ?></a></li>
                    <li><a href="/returns"><?php echo getLanguageText('returns_exchanges', $currentLanguage); ?></a></li>
                    <li><a href="/size-guide"><?php echo getLanguageText('size_guide', $currentLanguage); ?></a></li>
                    <li><a href="/track-order"><?php echo getLanguageText('track_order', $currentLanguage); ?></a></li>
                    <li><a href="/gift-cards"><?php echo getLanguageText('gift_cards', $currentLanguage); ?></a></li>
                </ul>
            </div>
            
            <!-- Categories -->
            <div class="footer-section">
                <h3><?php echo getLanguageText('categories', $currentLanguage); ?></h3>
                <ul class="footer-links">
                    <?php foreach (array_slice($categories, 0, 8) as $category): ?>
                    <li>
                        <a href="/categories/<?php echo $category['slug']; ?>">
                            <?php echo sanitizeOutput($category['name']); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <!-- Customer Service -->
            <div class="footer-section">
                <h3><?php echo getLanguageText('customer_service', $currentLanguage); ?></h3>
                <ul class="footer-links">
                    <li><a href="/contact"><?php echo getLanguageText('contact_us', $currentLanguage); ?></a></li>
                    <li><a href="/faq"><?php echo getLanguageText('faq', $currentLanguage); ?></a></li>
                    <li><a href="/support"><?php echo getLanguageText('support_center', $currentLanguage); ?></a></li>
                    <li><a href="/live-chat"><?php echo getLanguageText('live_chat', $currentLanguage); ?></a></li>
                    <li><a href="/feedback"><?php echo getLanguageText('feedback', $currentLanguage); ?></a></li>
                    <li><a href="/report-issue"><?php echo getLanguageText('report_issue', $currentLanguage); ?></a></li>
                </ul>
                
                <div class="customer-service-hours">
                    <h4><?php echo getLanguageText('service_hours', $currentLanguage); ?></h4>
                    <p><?php echo getLanguageText('monday_friday', $currentLanguage); ?>: 8:00 AM - 8:00 PM</p>
                    <p><?php echo getLanguageText('saturday', $currentLanguage); ?>: 9:00 AM - 6:00 PM</p>
                    <p><?php echo getLanguageText('sunday', $currentLanguage); ?>: 10:00 AM - 4:00 PM</p>
                </div>
            </div>
            
            <!-- Seller Info -->
            <div class="footer-section">
                <h3><?php echo getLanguageText('for_sellers', $currentLanguage); ?></h3>
                <ul class="footer-links">
                    <li><a href="/become-seller"><?php echo getLanguageText('become_seller', $currentLanguage); ?></a></li>
                    <li><a href="/seller-guide"><?php echo getLanguageText('seller_guide', $currentLanguage); ?></a></li>
                    <li><a href="/seller-fees"><?php echo getLanguageText('seller_fees', $currentLanguage); ?></a></li>
                    <li><a href="/seller-policies"><?php echo getLanguageText('seller_policies', $currentLanguage); ?></a></li>
                    <li><a href="/seller-support"><?php echo getLanguageText('seller_support', $currentLanguage); ?></a></li>
                </ul>
                
                <div class="seller-benefits">
                    <h4><?php echo getLanguageText('seller_benefits', $currentLanguage); ?></h4>
                    <ul class="benefits-list">
                        <li><i class="fas fa-check"></i> <?php echo getLanguageText('global_reach', $currentLanguage); ?></li>
                        <li><i class="fas fa-check"></i> <?php echo getLanguageText('secure_payments', $currentLanguage); ?></li>
                        <li><i class="fas fa-check"></i> <?php echo getLanguageText('marketing_tools', $currentLanguage); ?></li>
                        <li><i class="fas fa-check"></i> <?php echo getLanguageText('analytics_insights', $currentLanguage); ?></li>
                    </ul>
                </div>
            </div>
            
            <!-- Newsletter & App -->
            <div class="footer-section">
                <h3><?php echo getLanguageText('stay_connected', $currentLanguage); ?></h3>
                
                <!-- Newsletter Signup -->
                <div class="newsletter-signup">
                    <p><?php echo getLanguageText('newsletter_description', $currentLanguage); ?></p>
                    <form class="newsletter-form" id="footerNewsletterForm">
                        <div class="newsletter-input-group">
                            <input type="email" 
                                   class="newsletter-input" 
                                   placeholder="<?php echo getLanguageText('enter_email', $currentLanguage); ?>"
                                   required>
                            <button type="submit" class="newsletter-btn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        <div class="newsletter-checkbox">
                            <input type="checkbox" id="newsletter-consent" required>
                            <label for="newsletter-consent">
                                <?php echo getLanguageText('newsletter_consent', $currentLanguage); ?>
                            </label>
                        </div>
                    </form>
                </div>
                
                <!-- Mobile App Links -->
                <div class="app-download">
                    <h4><?php echo getLanguageText('download_app', $currentLanguage); ?></h4>
                    <div class="app-buttons">
                        <a href="#" class="app-button">
                            <img src="/assets/images/app-store.png" alt="Download on App Store" onerror="this.style.display='none'">
                        </a>
                        <a href="#" class="app-button">
                            <img src="/assets/images/google-play.png" alt="Get it on Google Play" onerror="this.style.display='none'">
                        </a>
                    </div>
                </div>
                
                <!-- Payment Methods -->
                <div class="payment-methods">
                    <h4><?php echo getLanguageText('payment_methods', $currentLanguage); ?></h4>
                    <div class="payment-icons">
                        <img src="/assets/images/payments/telebirr.png" alt="Telebirr" onerror="this.style.display='none'">
                        <img src="/assets/images/payments/visa.png" alt="Visa" onerror="this.style.display='none'">
                        <img src="/assets/images/payments/mastercard.png" alt="Mastercard" onerror="this.style.display='none'">
                        <img src="/assets/images/payments/paypal.png" alt="PayPal" onerror="this.style.display='none'">
                        <img src="/assets/images/payments/stripe.png" alt="Stripe" onerror="this.style.display='none'">
                    </div>
                </div>
                
                <!-- Security Badges -->
                <div class="security-badges">
                    <img src="/assets/images/security/ssl-secure.png" alt="SSL Secure" onerror="this.style.display='none'">
                    <img src="/assets/images/security/verified-merchant.png" alt="Verified Merchant" onerror="this.style.display='none'">
                </div>
            </div>
        </div>
        
        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <div class="footer-legal">
                    <p>&copy; 2024 Ethiopian Marketplace. <?php echo getLanguageText('all_rights_reserved', $currentLanguage); ?></p>
                    <div class="legal-links">
                        <a href="/privacy-policy"><?php echo getLanguageText('privacy_policy', $currentLanguage); ?></a>
                        <a href="/terms-of-service"><?php echo getLanguageText('terms_of_service', $currentLanguage); ?></a>
                        <a href="/cookie-policy"><?php echo getLanguageText('cookie_policy', $currentLanguage); ?></a>
                        <a href="/accessibility"><?php echo getLanguageText('accessibility', $currentLanguage); ?></a>
                    </div>
                </div>
                
                <div class="footer-certifications">
                    <div class="certification-item">
                        <i class="fas fa-shield-alt"></i>
                        <span><?php echo getLanguageText('secure_shopping', $currentLanguage); ?></span>
                    </div>
                    <div class="certification-item">
                        <i class="fas fa-truck"></i>
                        <span><?php echo getLanguageText('fast_delivery', $currentLanguage); ?></span>
                    </div>
                    <div class="certification-item">
                        <i class="fas fa-undo"></i>
                        <span><?php echo getLanguageText('easy_returns', $currentLanguage); ?></span>
                    </div>
                    <div class="certification-item">
                        <i class="fas fa-headset"></i>
                        <span><?php echo getLanguageText('24_7_support', $currentLanguage); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button class="scroll-top-btn" id="scrollTopBtn" onclick="scrollToTop()">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- Cookie Consent Banner -->
<div class="cookie-banner" id="cookieBanner">
    <div class="cookie-content">
        <div class="cookie-text">
            <i class="fas fa-cookie-bite"></i>
            <span><?php echo getLanguageText('cookie_message', $currentLanguage); ?></span>
        </div>
        <div class="cookie-actions">
            <button class="btn btn-outline btn-small" onclick="manageCookies()">
                <?php echo getLanguageText('manage_cookies', $currentLanguage); ?>
            </button>
            <button class="btn btn-primary btn-small" onclick="acceptCookies()">
                <?php echo getLanguageText('accept_all', $currentLanguage); ?>
            </button>
        </div>
    </div>
</div>

<style>
/* Footer Specific Styles */
.footer-contact {
    margin: var(--spacing-lg) 0;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-sm);
    color: rgba(255, 255, 255, 0.8);
}

.contact-item i {
    width: 16px;
    color: var(--secondary-color);
}

.customer-service-hours {
    margin-top: var(--spacing-lg);
    padding-top: var(--spacing-lg);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.customer-service-hours h4 {
    color: var(--white);
    font-size: var(--font-sm);
    margin-bottom: var(--spacing-sm);
}

.customer-service-hours p {
    color: rgba(255, 255, 255, 0.8);
    font-size: var(--font-sm);
    margin-bottom: var(--spacing-xs);
}

.seller-benefits {
    margin-top: var(--spacing-lg);
    padding-top: var(--spacing-lg);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.seller-benefits h4 {
    color: var(--white);
    font-size: var(--font-sm);
    margin-bottom: var(--spacing-sm);
}

.benefits-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.benefits-list li {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-xs);
    color: rgba(255, 255, 255, 0.8);
    font-size: var(--font-sm);
}

.benefits-list i {
    color: var(--secondary-color);
    font-size: var(--font-xs);
}

.newsletter-signup {
    margin-bottom: var(--spacing-xl);
}

.newsletter-input-group {
    display: flex;
    margin-bottom: var(--spacing-sm);
}

.newsletter-input {
    flex: 1;
    padding: var(--spacing-sm) var(--spacing-md);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-right: none;
    border-radius: var(--radius-md) 0 0 var(--radius-md);
    background-color: rgba(255, 255, 255, 0.1);
    color: var(--white);
    font-size: var(--font-sm);
}

.newsletter-input::placeholder {
    color: rgba(255, 255, 255, 0.6);
}

.newsletter-btn {
    padding: var(--spacing-sm) var(--spacing-md);
    background-color: var(--secondary-color);
    color: var(--text-primary);
    border: 1px solid var(--secondary-color);
    border-radius: 0 var(--radius-md) var(--radius-md) 0;
    cursor: pointer;
    transition: background-color var(--transition-fast);
}

.newsletter-btn:hover {
    background-color: #e6c200;
}

.newsletter-checkbox {
    display: flex;
    align-items: flex-start;
    gap: var(--spacing-sm);
}

.newsletter-checkbox input {
    margin-top: 2px;
}

.newsletter-checkbox label {
    color: rgba(255, 255, 255, 0.8);
    font-size: var(--font-xs);
    line-height: 1.4;
}

.app-download {
    margin-bottom: var(--spacing-xl);
}

.app-download h4 {
    color: var(--white);
    font-size: var(--font-sm);
    margin-bottom: var(--spacing-sm);
}

.app-buttons {
    display: flex;
    gap: var(--spacing-sm);
}

.app-button img {
    height: 40px;
    width: auto;
    border-radius: var(--radius-sm);
    transition: transform var(--transition-fast);
}

.app-button:hover img {
    transform: scale(1.05);
}

.payment-methods {
    margin-bottom: var(--spacing-xl);
}

.payment-methods h4 {
    color: var(--white);
    font-size: var(--font-sm);
    margin-bottom: var(--spacing-sm);
}

.payment-icons {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-sm);
}

.payment-icons img {
    height: 24px;
    width: auto;
    opacity: 0.8;
    transition: opacity var(--transition-fast);
}

.payment-icons img:hover {
    opacity: 1;
}

.security-badges {
    display: flex;
    gap: var(--spacing-sm);
}

.security-badges img {
    height: 32px;
    width: auto;
    opacity: 0.8;
}

.footer-bottom-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: var(--spacing-lg);
}

.footer-legal {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.legal-links {
    display: flex;
    gap: var(--spacing-lg);
    flex-wrap: wrap;
}

.legal-links a {
    color: rgba(255, 255, 255, 0.6);
    font-size: var(--font-xs);
    text-decoration: none;
    transition: color var(--transition-fast);
}

.legal-links a:hover {
    color: var(--white);
}

.footer-certifications {
    display: flex;
    gap: var(--spacing-xl);
    flex-wrap: wrap;
}

.certification-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    color: rgba(255, 255, 255, 0.8);
    font-size: var(--font-xs);
}

.certification-item i {
    color: var(--secondary-color);
}

/* Scroll to Top Button */
.scroll-top-btn {
    position: fixed;
    bottom: var(--spacing-xl);
    right: var(--spacing-xl);
    width: 50px;
    height: 50px;
    background-color: var(--primary-color);
    color: var(--white);
    border: none;
    border-radius: var(--radius-full);
    font-size: var(--font-lg);
    cursor: pointer;
    box-shadow: var(--shadow-lg);
    transition: all var(--transition-fast);
    opacity: 0;
    visibility: hidden;
    z-index: var(--z-fixed);
}

.scroll-top-btn.show {
    opacity: 1;
    visibility: visible;
}

.scroll-top-btn:hover {
    background-color: #0a3278;
    transform: translateY(-2px);
}

/* Cookie Banner */
.cookie-banner {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: var(--white);
    border-top: 1px solid #dee2e6;
    box-shadow: var(--shadow-lg);
    z-index: var(--z-modal);
    transform: translateY(100%);
    transition: transform var(--transition-normal);
}

.cookie-banner.show {
    transform: translateY(0);
}

.cookie-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--spacing-lg);
    max-width: 1200px;
    margin: 0 auto;
    gap: var(--spacing-lg);
}

.cookie-text {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    flex: 1;
}

.cookie-text i {
    color: var(--secondary-color);
    font-size: var(--font-xl);
}

.cookie-actions {
    display: flex;
    gap: var(--spacing-sm);
}

/* Responsive Footer */
@media (max-width: 768px) {
    .footer-content {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .footer-bottom-content {
        flex-direction: column;
        text-align: center;
    }
    
    .footer-certifications {
        justify-content: center;
    }
    
    .cookie-content {
        flex-direction: column;
        text-align: center;
    }
    
    .scroll-top-btn {
        bottom: var(--spacing-lg);
        right: var(--spacing-lg);
        width: 45px;
        height: 45px;
    }
}
</style>

<script>
// Newsletter form submission
document.getElementById('footerNewsletterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = this.querySelector('.newsletter-input').value;
    const consent = this.querySelector('#newsletter-consent').checked;
    
    if (!consent) {
        showNotification('Please accept the newsletter terms', 'warning');
        return;
    }
    
    // Submit newsletter subscription
    fetch('/backend/api/newsletter.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'subscribe',
            email: email
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Successfully subscribed to newsletter!', 'success');
            this.reset();
        } else {
            showNotification(data.message || 'Subscription failed', 'error');
        }
    })
    .catch(error => {
        showNotification('Network error. Please try again.', 'error');
    });
});

// Scroll to top functionality
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Show/hide scroll to top button
window.addEventListener('scroll', function() {
    const scrollTopBtn = document.getElementById('scrollTopBtn');
    if (window.pageYOffset > 300) {
        scrollTopBtn.classList.add('show');
    } else {
        scrollTopBtn.classList.remove('show');
    }
});

// Cookie banner functionality
function showCookieBanner() {
    const banner = document.getElementById('cookieBanner');
    if (!localStorage.getItem('cookiesAccepted')) {
        banner.classList.add('show');
    }
}

function acceptCookies() {
    localStorage.setItem('cookiesAccepted', 'true');
    document.getElementById('cookieBanner').classList.remove('show');
}

function manageCookies() {
    // Open cookie management modal
    showModal('cookieManagementModal');
}

// Show cookie banner on page load
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(showCookieBanner, 2000);
});
</script>