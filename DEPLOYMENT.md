# Ethiopian Marketplace - Deployment Guide

## Prerequisites

### Server Requirements
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP**: 8.1 or higher
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **SSL Certificate**: Required for production
- **Memory**: Minimum 2GB RAM
- **Storage**: Minimum 10GB SSD

### PHP Extensions Required
```bash
# Required PHP extensions
php-mysql (or php-pgsql for PostgreSQL)
php-curl
php-gd
php-mbstring
php-xml
php-zip
php-json
php-openssl
php-fileinfo
php-intl
```

## Installation Steps

### 1. Clone Repository
```bash
git clone https://github.com/your-username/ethiopian-marketplace.git
cd ethiopian-marketplace
```

### 2. Set File Permissions
```bash
# Make directories writable
chmod 755 -R .
chmod 777 -R assets/uploads/
chmod 777 -R backend/logs/
chmod 644 .htaccess

# Secure sensitive files
chmod 600 config/config.php
chmod 600 database/schema.sql
```

### 3. Database Setup

#### MySQL Setup
```bash
# Create database
mysql -u root -p
CREATE DATABASE ethiopian_marketplace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'marketplace_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON ethiopian_marketplace.* TO 'marketplace_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import schema
mysql -u marketplace_user -p ethiopian_marketplace < database/schema.sql

# Import sample data (optional)
mysql -u marketplace_user -p ethiopian_marketplace < database/sample_data.sql
```

#### PostgreSQL Setup
```bash
# Create database and user
sudo -u postgres psql
CREATE DATABASE ethiopian_marketplace;
CREATE USER marketplace_user WITH ENCRYPTED PASSWORD 'strong_password_here';
GRANT ALL PRIVILEGES ON DATABASE ethiopian_marketplace TO marketplace_user;
\q

# Import schema (convert MySQL to PostgreSQL syntax first)
psql -U marketplace_user -d ethiopian_marketplace -f database/schema_postgresql.sql
```

### 4. Configuration

#### Update config/config.php
```php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'ethiopian_marketplace');
define('DB_USER', 'marketplace_user');
define('DB_PASS', 'your_strong_password');

// Site configuration
define('SITE_URL', 'https://yourdomain.com');
define('SITE_EMAIL', 'info@yourdomain.com');

// Security keys (generate new ones)
define('JWT_SECRET', 'generate-32-character-random-string');
define('ENCRYPTION_KEY', 'generate-32-character-random-string');

// Payment gateway settings
define('STRIPE_PUBLIC_KEY', 'pk_live_your_stripe_public_key');
define('STRIPE_SECRET_KEY', 'sk_live_your_stripe_secret_key');
define('PAYPAL_CLIENT_ID', 'your_paypal_client_id');
define('PAYPAL_CLIENT_SECRET', 'your_paypal_client_secret');
define('PAYPAL_MODE', 'live'); // Change from 'sandbox' to 'live'

// Telebirr settings (Ethiopian mobile payment)
define('TELEBIRR_APP_ID', 'your_telebirr_app_id');
define('TELEBIRR_APP_KEY', 'your_telebirr_app_key');

// Email settings
define('SMTP_HOST', 'smtp.yourdomain.com');
define('SMTP_USERNAME', 'noreply@yourdomain.com');
define('SMTP_PASSWORD', 'your_email_password');

// AI/OpenAI settings
define('OPENAI_API_KEY', 'your-openai-api-key');

// Set environment to production
define('ENVIRONMENT', 'production');
```

### 5. Web Server Configuration

#### Apache Configuration
```apache
<VirtualHost *:443>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/html/ethiopian-marketplace
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /path/to/your/certificate.crt
    SSLCertificateKeyFile /path/to/your/private.key
    SSLCertificateChainFile /path/to/your/ca-bundle.crt
    
    # Security Headers
    Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"
    Header set X-Content-Type-Options nosniff
    Header set X-Frame-Options DENY
    Header set X-XSS-Protection "1; mode=block"
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/marketplace_error.log
    CustomLog ${APACHE_LOG_DIR}/marketplace_access.log combined
    
    # PHP Configuration
    php_admin_value upload_max_filesize 10M
    php_admin_value post_max_size 10M
    php_admin_value memory_limit 256M
    php_admin_value max_execution_time 300
</VirtualHost>

# Redirect HTTP to HTTPS
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    Redirect permanent / https://yourdomain.com/
</VirtualHost>
```

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/html/ethiopian-marketplace;
    index index.php;
    
    # SSL Configuration
    ssl_certificate /path/to/your/certificate.crt;
    ssl_certificate_key /path/to/your/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;
    
    # Security Headers
    add_header Strict-Transport-Security "max-age=63072000; includeSubDomains; preload" always;
    add_header X-Content-Type-Options nosniff always;
    add_header X-Frame-Options DENY always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    # PHP Configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        
        fastcgi_param PHP_VALUE "upload_max_filesize=10M
                                 post_max_size=10M
                                 memory_limit=256M
                                 max_execution_time=300";
    }
    
    # URL Rewriting
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # Static files caching
    location ~* \.(css|js|png|jpg|jpeg|gif|webp|svg|ico|woff|woff2|ttf|otf)$ {
        expires 1M;
        add_header Cache-Control "public, immutable";
    }
    
    # Security - Block access to sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ \.(htaccess|htpasswd|ini|log|sh|inc|bak|sql)$ {
        deny all;
    }
}
```

### 6. SSL Certificate Setup

#### Using Let's Encrypt (Free)
```bash
# Install Certbot
sudo apt update
sudo apt install certbot python3-certbot-apache  # For Apache
# OR
sudo apt install certbot python3-certbot-nginx   # For Nginx

# Generate certificate
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com  # For Apache
# OR
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com   # For Nginx

# Auto-renewal
sudo crontab -e
# Add this line:
0 12 * * * /usr/bin/certbot renew --quiet
```

### 7. Create Required Directories
```bash
mkdir -p assets/uploads/products
mkdir -p assets/uploads/users
mkdir -p assets/uploads/sellers
mkdir -p assets/uploads/categories
mkdir -p backend/logs
mkdir -p backend/cache

# Set permissions
chmod 777 assets/uploads/
chmod 777 backend/logs/
chmod 777 backend/cache/
```

### 8. Setup Cron Jobs
```bash
# Edit crontab
crontab -e

# Add these cron jobs:
# Clean up expired sessions (every hour)
0 * * * * php /var/www/html/ethiopian-marketplace/backend/cron/cleanup_sessions.php

# Send newsletter emails (daily at 9 AM)
0 9 * * * php /var/www/html/ethiopian-marketplace/backend/cron/send_newsletters.php

# Update currency rates (daily at midnight)
0 0 * * * php /var/www/html/ethiopian-marketplace/backend/cron/update_currency_rates.php

# Generate analytics reports (daily at 2 AM)
0 2 * * * php /var/www/html/ethiopian-marketplace/backend/cron/generate_analytics.php

# Clean up old logs (weekly)
0 0 * * 0 find /var/www/html/ethiopian-marketplace/backend/logs/ -name "*.log" -mtime +30 -delete
```

### 9. Security Hardening

#### File Security
```bash
# Create .htaccess files to protect sensitive directories
echo "deny from all" > config/.htaccess
echo "deny from all" > backend/classes/.htaccess
echo "deny from all" > backend/includes/.htaccess
echo "deny from all" > database/.htaccess

# Set proper ownership
chown -R www-data:www-data /var/www/html/ethiopian-marketplace
```

#### Database Security
```sql
-- Remove default accounts
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');

-- Update root password
ALTER USER 'root'@'localhost' IDENTIFIED BY 'very_strong_root_password';

-- Remove test database
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';

-- Reload privileges
FLUSH PRIVILEGES;
```

### 10. Monitoring and Logging

#### Setup Log Rotation
```bash
# Create logrotate configuration
sudo nano /etc/logrotate.d/ethiopian-marketplace

# Add this content:
/var/www/html/ethiopian-marketplace/backend/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload apache2 > /dev/null 2>&1 || true
    endscript
}
```

#### Setup Monitoring
```bash
# Install monitoring tools
sudo apt install htop iotop nethogs

# Setup basic monitoring script
nano /usr/local/bin/marketplace-monitor.sh

#!/bin/bash
# Basic monitoring for Ethiopian Marketplace

# Check disk space
df -h | grep -E "/$|/var" | awk '{print $5 " " $1}' | while read output;
do
  usage=$(echo $output | awk '{print $1}' | sed 's/%//g')
  partition=$(echo $output | awk '{print $2}')
  if [ $usage -ge 90 ]; then
    echo "Warning: $partition is ${usage}% full" | mail -s "Disk Space Alert" admin@yourdomain.com
  fi
done

# Check MySQL connection
mysql -u marketplace_user -p'password' -e "SELECT 1;" > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo "MySQL connection failed" | mail -s "Database Alert" admin@yourdomain.com
fi

# Make executable and add to cron
chmod +x /usr/local/bin/marketplace-monitor.sh
echo "*/15 * * * * /usr/local/bin/marketplace-monitor.sh" | crontab -
```

## Performance Optimization

### 1. Enable PHP OPcache
```ini
# Add to php.ini
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

### 2. Database Optimization
```sql
-- Add indexes for better performance
CREATE INDEX idx_products_search ON products(name, description);
CREATE INDEX idx_orders_user_status ON orders(user_id, status);
CREATE INDEX idx_reviews_product_status ON reviews(product_id, status);

-- Optimize tables
OPTIMIZE TABLE products, orders, users, reviews;
```

### 3. CDN Setup (Optional)
```bash
# If using CloudFlare or similar CDN
# Update SITE_URL in config to use CDN domain
# Configure CDN to cache static assets
```

## Backup Strategy

### 1. Database Backup
```bash
#!/bin/bash
# Create backup script
nano /usr/local/bin/backup-marketplace.sh

#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/marketplace"
DB_NAME="ethiopian_marketplace"
DB_USER="marketplace_user"
DB_PASS="password"

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz

# Files backup
tar -czf $BACKUP_DIR/files_backup_$DATE.tar.gz /var/www/html/ethiopian-marketplace --exclude="*.log"

# Remove backups older than 30 days
find $BACKUP_DIR -type f -mtime +30 -delete

# Make executable
chmod +x /usr/local/bin/backup-marketplace.sh

# Add to cron (daily at 3 AM)
echo "0 3 * * * /usr/local/bin/backup-marketplace.sh" | crontab -
```

## Testing Checklist

### Pre-Launch Testing
- [ ] User registration and login
- [ ] Product browsing and search
- [ ] Shopping cart functionality
- [ ] Checkout process
- [ ] Payment processing (test mode)
- [ ] Order management
- [ ] Seller dashboard
- [ ] Admin panel
- [ ] Email notifications
- [ ] Mobile responsiveness
- [ ] SSL certificate
- [ ] Performance testing
- [ ] Security scanning

### Post-Launch Monitoring
- [ ] Server performance
- [ ] Database performance
- [ ] Error logs
- [ ] User feedback
- [ ] Payment processing
- [ ] Email delivery
- [ ] Backup verification

## Troubleshooting

### Common Issues

#### 1. Database Connection Error
```bash
# Check MySQL service
sudo systemctl status mysql
sudo systemctl restart mysql

# Check credentials in config.php
# Verify database user permissions
```

#### 2. File Upload Issues
```bash
# Check directory permissions
ls -la assets/uploads/
chmod 777 assets/uploads/

# Check PHP settings
php -i | grep upload_max_filesize
php -i | grep post_max_size
```

#### 3. Email Not Sending
```bash
# Check SMTP settings in config.php
# Test email configuration
php -r "mail('test@example.com', 'Test', 'Test message');"

# Check mail logs
tail -f /var/log/mail.log
```

#### 4. SSL Certificate Issues
```bash
# Check certificate validity
openssl x509 -in /path/to/certificate.crt -text -noout

# Test SSL configuration
curl -I https://yourdomain.com
```

## Support and Maintenance

### Regular Maintenance Tasks
1. **Weekly**: Review error logs, check backups
2. **Monthly**: Update dependencies, security patches
3. **Quarterly**: Performance review, security audit
4. **Annually**: SSL certificate renewal, full system backup

### Getting Help
- Check error logs in `backend/logs/`
- Review Apache/Nginx error logs
- Monitor database performance
- Contact support team for critical issues

## Security Updates

Keep the following components updated regularly:
- PHP and extensions
- MySQL/PostgreSQL
- Web server (Apache/Nginx)
- SSL certificates
- Third-party libraries
- Operating system packages

Remember to test updates in a staging environment before applying to production!