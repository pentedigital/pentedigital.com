FROM php:8.3-apache

# Enable Apache modules
RUN apt-get update && apt-get install -y \
    libpng-dev \
    msmtp \
    msmtp-mta \
    curl \
    && docker-php-ext-install opcache \
    && a2enmod rewrite headers expires \
    && rm -rf /var/lib/apt/lists/*

# Configure PHP
RUN { \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=4000'; \
    echo 'opcache.revalidate_freq=2'; \
    echo 'opcache.fast_shutdown=1'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

# Apache configuration for security headers and compression
RUN { \
    echo '<IfModule mod_headers.c>'; \
    echo '    Header always set X-Frame-Options "SAMEORIGIN"'; \
    echo '    Header always set X-Content-Type-Options "nosniff"'; \
    echo '    Header always set X-XSS-Protection "1; mode=block"'; \
    echo '    Header always set Referrer-Policy "strict-origin-when-cross-origin"'; \
    echo '</IfModule>'; \
    echo '<IfModule mod_deflate.c>'; \
    echo '    AddOutputFilterByType DEFLATE text/html text/plain text/css application/javascript application/json'; \
    echo '</IfModule>'; \
    } > /etc/apache2/conf-available/custom.conf \
    && a2enconf custom

# Copy site files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1
