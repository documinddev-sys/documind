# Use official PHP image with Apache
FROM php:8.1-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies and PHP extensions needed for the application
RUN apt-get update && apt-get install -y \
    libxml2-dev \
    libpdf-dev \
    zip \
    unzip \
    curl \
    git \
    && rm -rf /var/lib/apt/lists/*

# Install required PHP extensions
RUN docker-php-ext-install \
    dom \
    mbstring \
    pdo \
    pdo_mysql

# Enable Apache rewrite module (needed for routing)
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . /var/www/html

# Install PHP dependencies
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Set proper permissions for storage and uploads
RUN chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/public/assets

# Create Apache configuration for public directory
RUN echo '<Directory /var/www/html/public>\n\
    Options -Indexes +FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/documind.conf \
    && a2enconf documind

# Update Apache DocumentRoot to point to public directory
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-enabled/000-default.conf

# Copy entrypoint script (will be created)
COPY docker-entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Expose the port
EXPOSE 8080

# Start Apache with dynamic port support
ENTRYPOINT ["/entrypoint.sh"]
