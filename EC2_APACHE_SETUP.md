# EC2 Apache Configuration for Laravel Project

## Problem
When accessing EC2 IP, the default Apache page shows instead of your Laravel application.

## Solution Steps

### 1. Connect to Your EC2 Instance
```bash
ssh -i your-key.pem ubuntu@your-ec2-ip
```

### 2. Locate Your Project
Assuming your project is in `/var/www/html/tickets-manage-gemini/`

```bash
cd /var/www/html/tickets-manage-gemini/
pwd  # Verify the path
```

### 3. Set Proper File Permissions
```bash
sudo chown -R www-data:www-data /var/www/html/tickets-manage-gemini/
sudo chmod -R 755 /var/www/html/tickets-manage-gemini/
sudo chmod -R 775 /var/www/html/tickets-manage-gemini/storage
sudo chmod -R 775 /var/www/html/tickets-manage-gemini/bootstrap/cache
```

### 4. Configure Apache Virtual Host

#### Option A: Edit Default Configuration
```bash
sudo nano /etc/apache2/sites-available/000-default.conf
```

Replace the content with:
```apache
<VirtualHost *:80>
    ServerAdmin admin@example.com
    DocumentRoot /var/www/html/tickets-manage-gemini/public
    
    <Directory /var/www/html/tickets-manage-gemini/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/laravel-error.log
    CustomLog ${APACHE_LOG_DIR}/laravel-access.log combined
</VirtualHost>
```

#### Option B: Create New Virtual Host (Recommended)
```bash
sudo nano /etc/apache2/sites-available/laravel.conf
```

Paste the same configuration as above, then:
```bash
# Disable default site
sudo a2dissite 000-default.conf

# Enable your Laravel site
sudo a2ensite laravel.conf
```

### 5. Enable Required Apache Modules
```bash
sudo a2enmod rewrite
sudo a2enmod headers
```

### 6. Update .env File (if needed)
```bash
cd /var/www/html/tickets-manage-gemini/
sudo nano .env
```

Ensure these settings are correct:
```env
APP_URL=http://your-ec2-ip
APP_ENV=production
APP_DEBUG=false
```

### 7. Clear Laravel Cache
```bash
cd /var/www/html/tickets-manage-gemini/
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize
```

### 8. Restart Apache
```bash
sudo systemctl restart apache2
```

### 9. Check Apache Status
```bash
sudo systemctl status apache2
```

### 10. Check Apache Error Logs (if issues persist)
```bash
sudo tail -f /var/log/apache2/error.log
sudo tail -f /var/log/apache2/laravel-error.log
```

## Troubleshooting

### If You See "403 Forbidden"
```bash
# Check SE Linux (if enabled)
sudo setenforce 0

# Or set proper context
sudo chcon -R -t httpd_sys_rw_content_t /var/www/html/tickets-manage-gemini/storage
sudo chcon -R -t httpd_sys_rw_content_t /var/www/html/tickets-manage-gemini/bootstrap/cache
```

### If You See "500 Internal Server Error"
```bash
# Check permissions
ls -la /var/www/html/tickets-manage-gemini/storage
ls -la /var/www/html/tickets-manage-gemini/bootstrap/cache

# Check .env file exists
ls -la /var/www/html/tickets-manage-gemini/.env

# Generate app key if needed
php artisan key:generate
```

### Verify Apache Configuration
```bash
sudo apache2ctl configtest
```

### Check if mod_rewrite is enabled
```bash
apache2ctl -M | grep rewrite
```

## Quick Command Summary
```bash
# All in one - Run these commands in sequence
cd /var/www/html/tickets-manage-gemini/
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 775 storage bootstrap/cache
sudo nano /etc/apache2/sites-available/000-default.conf
# (Update DocumentRoot to /var/www/html/tickets-manage-gemini/public)
sudo a2enmod rewrite
sudo systemctl restart apache2
php artisan optimize
```

## Important Notes

1. **Document Root**: Must point to the `public` directory of your Laravel project
2. **AllowOverride All**: Required for `.htaccess` to work
3. **mod_rewrite**: Required for Laravel routing
4. **Permissions**: `www-data` must own the files and have write access to `storage` and `bootstrap/cache`

## Security Reminder for Production

After everything works:
```bash
# Update .env
APP_DEBUG=false
APP_ENV=production

# Clear and optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

<!-- Fixed host config -->
<VirtualHost *:80>
    ServerAdmin admin@example.com
    DocumentRoot /var/www/html/tickets-manage/public
    
    <Directory /var/www/html/tickets-manage/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/laravel-error.log
    CustomLog ${APACHE_LOG_DIR}/laravel-access.log combined
</VirtualHost>
