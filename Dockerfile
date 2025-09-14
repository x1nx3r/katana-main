FROM wordpress:latest

# Install additional PHP extensions and required tools
RUN apt-get update && apt-get install -y \
    wget \
    unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite for WordPress permalinks
RUN a2enmod rewrite

# Download and extract wp-content from GitHub
RUN wget -O /tmp/main.zip https://github.com/x1nx3r/katana-main/archive/refs/heads/main.zip && \
    unzip /tmp/main.zip -d /tmp/ && \
    rm -rf /var/www/html/wp-content/* && \
    cp -r /tmp/katana-main-main/public_html/wp-content/* /var/www/html/wp-content/ && \
    rm -rf /tmp/main.zip /tmp/katana-main-main

# Copy any custom root files
COPY public_html/.htaccess /var/www/html/.htaccess
COPY public_html/access.php /var/www/html/access.php
COPY public_html/default.php /var/www/html/default.php
COPY public_html/nax.php /var/www/html/nax.php
COPY public_html/radio.php /var/www/html/radio.php

# Set proper permissions for wp-content and all files
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Expose port 80
EXPOSE 80
