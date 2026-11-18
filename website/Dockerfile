FROM php:8.2-apache

RUN docker-php-ext-install pdo_mysql \
 && a2enmod rewrite headers

RUN { \
    echo "upload_max_filesize=10M"; \
    echo "post_max_size=12M"; \
    echo "memory_limit=128M"; \
    echo "display_errors=Off"; \
    echo "log_errors=On"; \
  } > /usr/local/etc/php/conf.d/app.ini

COPY docker/apache-site.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html
