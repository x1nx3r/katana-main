FROM wordpress:latest

# Install additional PHP extensions that might be needed
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite for WordPress permalinks
RUN a2enmod rewrite

# Copy custom WordPress content more explicitly
# Create a fresh wp-content directory and copy our content
RUN mkdir -p /var/www/html/wp-content-custom
COPY public_html/wp-content/ /var/www/html/wp-content-custom/
RUN rm -rf /var/www/html/wp-content && \
    mv /var/www/html/wp-content-custom /var/www/html/wp-content

# Copy any custom root files that don't conflict with WordPress core
COPY public_html/.htaccess /var/www/html/.htaccess
COPY public_html/access.php /var/www/html/access.php
COPY public_html/default.php /var/www/html/default.php
COPY public_html/nax.php /var/www/html/nax.php
COPY public_html/radio.php /var/www/html/radio.php

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 775 /var/www/html/wp-content

# Copy custom wp-config.php for Docker environment
COPY docker-wp-config.php /var/www/html/wp-config.php

# Expose port 80
EXPOSE 80