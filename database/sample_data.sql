-- Ethiopian Marketplace Sample Data
-- Sample data for testing and demonstration

USE ethiopian_marketplace;

-- Insert sample categories
INSERT INTO categories (name, name_amharic, slug, description, image, icon, sort_order, is_active) VALUES
('Coffee', 'ቡና', 'coffee', 'Premium Ethiopian coffee beans from various regions', 'coffee.jpg', 'fas fa-coffee', 1, 1),
('Spices & Herbs', 'ቅመማ ቅመም', 'spices-herbs', 'Traditional Ethiopian spices and herbs', 'spices.jpg', 'fas fa-pepper-hot', 2, 1),
('Honey', 'ማር', 'honey', 'Pure Ethiopian honey from different regions', 'honey.jpg', 'fas fa-heart', 3, 1),
('Handicrafts', 'እጅ ሥራ', 'handicrafts', 'Traditional Ethiopian handicrafts and art', 'handicrafts.jpg', 'fas fa-palette', 4, 1),
('Textiles', 'ጨርቃ ጨርቅ', 'textiles', 'Ethiopian traditional clothing and fabrics', 'textiles.jpg', 'fas fa-tshirt', 5, 1),
('Jewelry', 'ጌጥ', 'jewelry', 'Traditional Ethiopian jewelry and accessories', 'jewelry.jpg', 'fas fa-gem', 6, 1),
('Organic Foods', 'ኦርጋኒክ ምግብ', 'organic-foods', 'Organic and natural food products', 'organic.jpg', 'fas fa-leaf', 7, 1),
('Books & Media', 'መጽሐፍ እና ሚዲያ', 'books-media', 'Ethiopian books, music, and media', 'books.jpg', 'fas fa-book', 8, 1);

-- Insert sample users
INSERT INTO users (email, password_hash, first_name, last_name, phone, role, email_verified, language_preference, currency_preference) VALUES
('admin@ethiopianmarketplace.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', '+251911234567', 'admin', 1, 'en', 'ETB'),
('seller1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Abebe', 'Kebede', '+251911234568', 'seller', 1, 'en', 'ETB'),
('seller2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Almaz', 'Tadesse', '+251911234569', 'seller', 1, 'am', 'ETB'),
('customer1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Smith', '+1234567890', 'customer', 1, 'en', 'USD'),
('customer2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Johnson', '+44123456789', 'customer', 1, 'en', 'EUR');

-- Insert sample sellers
INSERT INTO sellers (user_id, business_name, business_type, description, rating, total_reviews, total_sales, is_verified, is_featured) VALUES
(2, 'Ethiopian Coffee Masters', 'small_business', 'Premium coffee supplier from Sidamo region with 20 years of experience', 4.8, 156, 2340, 1, 1),
(3, 'Almaz Traditional Crafts', 'individual', 'Handmade traditional Ethiopian crafts and jewelry', 4.9, 89, 567, 1, 1);

-- Insert sample products
INSERT INTO products (seller_id, category_id, name, name_amharic, slug, short_description, description, price, compare_price, sku, stock_quantity, images, status, is_featured, tags, seo_title, seo_description) VALUES
(1, 1, 'Sidamo Coffee Beans - Premium Grade', 'ሲዳሞ ቡና - ከፍተኛ ደረጃ', 'sidamo-coffee-beans-premium', 'Premium Sidamo coffee beans with rich flavor', 'Our premium Sidamo coffee beans are sourced directly from small-scale farmers in the Sidamo region. Known for their wine-like acidity and floral notes, these beans represent the finest Ethiopian coffee tradition. Each batch is carefully selected and processed to ensure maximum flavor and quality.', 450.00, 520.00, 'EM-COFFEE-001', 150, '["coffee1.jpg", "coffee2.jpg", "coffee3.jpg"]', 'active', 1, '["coffee", "sidamo", "premium", "organic"]', 'Premium Sidamo Coffee Beans - Ethiopian Marketplace', 'Buy authentic premium Sidamo coffee beans from Ethiopia. Rich flavor, wine-like acidity, and floral notes. Direct from farmers.'),

(1, 1, 'Yirgacheffe Coffee - Single Origin', 'ይርጋቸፈ ቡና', 'yirgacheffe-coffee-single-origin', 'Single origin Yirgacheffe coffee with bright acidity', 'Yirgacheffe coffee is renowned worldwide for its bright acidity, floral aroma, and tea-like body. Our single-origin Yirgacheffe beans are grown at high altitude in the birthplace of coffee, delivering a clean, bright cup with notes of citrus and flowers.', 380.00, 420.00, 'EM-COFFEE-002', 200, '["yirgacheffe1.jpg", "yirgacheffe2.jpg"]', 'active', 1, '["coffee", "yirgacheffe", "single-origin", "floral"]', 'Yirgacheffe Single Origin Coffee - Ethiopian Marketplace', 'Authentic Yirgacheffe coffee beans with bright acidity and floral notes. Single origin from the birthplace of coffee.'),

(1, 2, 'Berbere Spice Mix - Traditional Blend', 'በርበሬ ቅመማ ቅመም', 'berbere-spice-mix-traditional', 'Authentic Ethiopian berbere spice blend', 'Our traditional berbere spice mix is made from a secret family recipe passed down through generations. This complex blend of over 15 spices creates the signature flavor of Ethiopian cuisine. Perfect for stews, meat dishes, and traditional Ethiopian cooking.', 85.00, 100.00, 'EM-SPICE-001', 300, '["berbere1.jpg", "berbere2.jpg"]', 'active', 1, '["spices", "berbere", "traditional", "ethiopian-cuisine"]', 'Authentic Berbere Spice Mix - Ethiopian Marketplace', 'Traditional Ethiopian berbere spice blend made from family recipe. Perfect for authentic Ethiopian cooking.'),

(1, 3, 'Wild Forest Honey - Pure & Natural', 'የደን ማር', 'wild-forest-honey-pure-natural', 'Pure wild forest honey from Ethiopian highlands', 'Harvested from the pristine forests of the Ethiopian highlands, our wild forest honey is completely pure and natural. Rich in antioxidants and minerals, this honey has a complex flavor profile with hints of wildflowers and herbs. No processing, no additives - just pure Ethiopian honey.', 320.00, null, 'EM-HONEY-001', 80, '["honey1.jpg", "honey2.jpg", "honey3.jpg"]', 'active', 1, '["honey", "wild", "natural", "organic", "pure"]', 'Pure Wild Forest Honey from Ethiopia - Ethiopian Marketplace', 'Pure wild forest honey from Ethiopian highlands. Natural, unprocessed honey rich in antioxidants and minerals.'),

(2, 4, 'Traditional Habesha Dress', 'ባህላዊ ሐበሻ ልብስ', 'traditional-habesha-dress', 'Handwoven traditional Ethiopian dress', 'Beautiful handwoven traditional Habesha dress made from high-quality cotton. Features intricate traditional patterns and embroidery. Perfect for cultural events, weddings, and special occasions. Each dress is unique and made by skilled artisans.', 1200.00, 1400.00, 'EM-DRESS-001', 25, '["dress1.jpg", "dress2.jpg", "dress3.jpg"]', 'active', 1, '["clothing", "traditional", "habesha", "handwoven", "cultural"]', 'Traditional Habesha Dress - Handwoven Ethiopian Clothing', 'Authentic handwoven Habesha dress with traditional patterns. Perfect for cultural events and special occasions.'),

(2, 6, 'Ethiopian Cross Pendant - Silver', 'የኢትዮጵያ መስቀል', 'ethiopian-cross-pendant-silver', 'Handcrafted silver Ethiopian cross pendant', 'Exquisite handcrafted Ethiopian cross pendant made from sterling silver. Features traditional Ethiopian Orthodox cross design with intricate details. Comes with a silver chain. Perfect as a gift or personal jewelry piece representing Ethiopian heritage.', 850.00, 950.00, 'EM-JEWELRY-001', 45, '["cross1.jpg", "cross2.jpg"]', 'active', 1, '["jewelry", "cross", "silver", "traditional", "orthodox", "pendant"]', 'Ethiopian Cross Silver Pendant - Traditional Jewelry', 'Handcrafted sterling silver Ethiopian Orthodox cross pendant with traditional design and intricate details.'),

(1, 7, 'Teff Grain - Gluten Free Ancient Grain', 'ጤፍ', 'teff-grain-gluten-free', 'Organic teff grain - superfood from Ethiopia', 'Teff is an ancient Ethiopian grain that is naturally gluten-free and packed with nutrition. High in protein, fiber, and minerals, teff is perfect for making injera bread or adding to smoothies and baked goods. Our teff is organically grown and carefully processed.', 180.00, 200.00, 'EM-TEFF-001', 120, '["teff1.jpg", "teff2.jpg"]', 'active', 0, '["teff", "grain", "gluten-free", "organic", "superfood", "ancient-grain"]', 'Organic Teff Grain - Gluten Free Ethiopian Superfood', 'Organic teff grain from Ethiopia. Gluten-free ancient grain high in protein and fiber. Perfect for injera and baking.'),

(2, 4, 'Woven Basket Set - Traditional Design', 'የተሸመነ ቅርጫት', 'woven-basket-set-traditional', 'Set of 3 traditional Ethiopian woven baskets', 'Beautiful set of three traditional Ethiopian woven baskets in different sizes. Made from natural grass and colored with traditional dyes. Perfect for storage, decoration, or as unique gifts. Each basket showcases traditional Ethiopian weaving techniques.', 280.00, 320.00, 'EM-BASKET-001', 60, '["basket1.jpg", "basket2.jpg", "basket3.jpg"]', 'active', 0, '["baskets", "woven", "traditional", "handicraft", "storage", "decoration"]', 'Traditional Ethiopian Woven Basket Set - Handmade Crafts', 'Set of 3 traditional Ethiopian woven baskets made from natural grass with traditional dyes and techniques.');

-- Insert sample reviews
INSERT INTO reviews (product_id, user_id, order_id, rating, title, comment, is_verified_purchase, status) VALUES
(1, 4, 1, 5, 'Excellent Coffee!', 'The best Ethiopian coffee I have ever tasted. Rich flavor and amazing aroma. Will definitely order again!', 1, 'approved'),
(1, 5, 2, 5, 'Outstanding Quality', 'Exceptional quality coffee beans. The flavor profile is exactly as described - wine-like acidity with floral notes.', 1, 'approved'),
(2, 4, 3, 4, 'Great Yirgacheffe', 'Very good coffee with bright acidity. Packaging could be better but the coffee quality is excellent.', 1, 'approved'),
(3, 5, 4, 5, 'Authentic Berbere', 'This is the real deal! Tastes exactly like the berbere my Ethiopian friend makes. Highly recommended.', 1, 'approved'),
(4, 4, 5, 5, 'Pure and Delicious', 'Amazing honey! You can taste the wildflowers and herbs. Much better than store-bought honey.', 1, 'approved');

-- Insert sample orders (for review purposes)
INSERT INTO orders (order_number, user_id, status, subtotal, tax_amount, shipping_amount, total_amount, billing_address, shipping_address) VALUES
('EM20241001001', 4, 'delivered', 450.00, 67.50, 50.00, 567.50, '{"name":"John Smith","address":"123 Main St","city":"New York","country":"USA"}', '{"name":"John Smith","address":"123 Main St","city":"New York","country":"USA"}'),
('EM20241001002', 5, 'delivered', 380.00, 57.00, 50.00, 487.00, '{"name":"Sarah Johnson","address":"456 Oak Ave","city":"London","country":"UK"}', '{"name":"Sarah Johnson","address":"456 Oak Ave","city":"London","country":"UK"}'),
('EM20241001003', 4, 'delivered', 380.00, 57.00, 50.00, 487.00, '{"name":"John Smith","address":"123 Main St","city":"New York","country":"USA"}', '{"name":"John Smith","address":"123 Main St","city":"New York","country":"USA"}'),
('EM20241001004', 5, 'delivered', 85.00, 12.75, 50.00, 147.75, '{"name":"Sarah Johnson","address":"456 Oak Ave","city":"London","country":"UK"}', '{"name":"Sarah Johnson","address":"456 Oak Ave","city":"London","country":"UK"}'),
('EM20241001005', 4, 'delivered', 320.00, 48.00, 50.00, 418.00, '{"name":"John Smith","address":"123 Main St","city":"New York","country":"USA"}', '{"name":"John Smith","address":"123 Main St","city":"New York","country":"USA"}');

-- Insert order items
INSERT INTO order_items (order_id, product_id, seller_id, quantity, price, total, product_name, product_sku) VALUES
(1, 1, 1, 1, 450.00, 450.00, 'Sidamo Coffee Beans - Premium Grade', 'EM-COFFEE-001'),
(2, 2, 1, 1, 380.00, 380.00, 'Yirgacheffe Coffee - Single Origin', 'EM-COFFEE-002'),
(3, 2, 1, 1, 380.00, 380.00, 'Yirgacheffe Coffee - Single Origin', 'EM-COFFEE-002'),
(4, 3, 1, 1, 85.00, 85.00, 'Berbere Spice Mix - Traditional Blend', 'EM-SPICE-001'),
(5, 4, 1, 1, 320.00, 320.00, 'Wild Forest Honey - Pure & Natural', 'EM-HONEY-001');

-- Insert sample coupons
INSERT INTO coupons (code, name, description, type, value, minimum_amount, start_date, end_date, is_active) VALUES
('WELCOME10', 'Welcome Discount', '10% off for new customers', 'percentage', 10.00, 100.00, '2024-01-01 00:00:00', '2024-12-31 23:59:59', 1),
('FREESHIP', 'Free Shipping', 'Free shipping on orders over 500 ETB', 'free_shipping', 0.00, 500.00, '2024-01-01 00:00:00', '2024-12-31 23:59:59', 1),
('COFFEE20', 'Coffee Special', '20% off all coffee products', 'percentage', 20.00, 200.00, '2024-01-01 00:00:00', '2024-12-31 23:59:59', 1);

-- Insert sample site settings
INSERT INTO site_settings (setting_key, setting_value, setting_type, description) VALUES
('site_name', 'Ethiopian Marketplace', 'string', 'Website name'),
('site_description', 'Premium Ethiopian products for global customers', 'string', 'Website description'),
('contact_email', 'info@ethiopianmarketplace.com', 'string', 'Contact email address'),
('contact_phone', '+251 11 123 4567', 'string', 'Contact phone number'),
('currency_rates', '{"ETB": 1.0, "USD": 0.018, "EUR": 0.017}', 'json', 'Currency exchange rates'),
('shipping_zones', '{"local": {"name": "Local Delivery", "cost": 25}, "national": {"name": "National Shipping", "cost": 50}, "international": {"name": "International Shipping", "cost": 150}}', 'json', 'Shipping zones and costs'),
('featured_categories', '[1, 2, 3, 4, 5, 6]', 'json', 'Featured category IDs for homepage'),
('social_media', '{"facebook": "https://facebook.com/ethiopianmarketplace", "twitter": "https://twitter.com/ethiomarketplace", "instagram": "https://instagram.com/ethiopianmarketplace"}', 'json', 'Social media links');

-- Update product ratings and review counts
UPDATE products p SET 
    rating = (SELECT AVG(rating) FROM reviews r WHERE r.product_id = p.id AND r.status = 'approved'),
    total_reviews = (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.id AND r.status = 'approved');

-- Update seller ratings and stats
UPDATE sellers s SET 
    rating = (SELECT AVG(rating) FROM reviews r JOIN products p ON r.product_id = p.id WHERE p.seller_id = s.id AND r.status = 'approved'),
    total_reviews = (SELECT COUNT(*) FROM reviews r JOIN products p ON r.product_id = p.id WHERE p.seller_id = s.id AND r.status = 'approved');

COMMIT;