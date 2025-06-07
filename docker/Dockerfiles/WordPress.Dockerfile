FROM wordpress:6.6.2-apache


RUN apt-get update && \
    apt-get install -y libpq-dev && \
    docker-php-ext-install mysqli pdo_pgsql pgsql && \
    apt-get clean && rm -rf /var/lib/apt/lists/*


COPY ./wp/ /var/www/html/


RUN chown -R www-data:www-data /var/www/html


EXPOSE 80
