FROM wordpress:6.6.2-apache

# Install required PHP extensions
RUN apt-get update && \
    apt-get install -y libpq-dev && \
    docker-php-ext-install mysqli pdo_pgsql pgsql && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy WordPress files into container
COPY ./wp/ /var/www/html/

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80
