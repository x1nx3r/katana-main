FROM wordpress:latest

# Install additional PHP extensions that might be needed
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite for WordPress permalinks
RUN a2enmod rewrite

# Copy custom WordPress files
COPY public_html/ /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 775 /var/www/html/wp-content

# Copy custom wp-config.php for Docker environment
COPY docker-wp-config.php /var/www/html/wp-config.php

# Expose port 80
EXPOSE 80