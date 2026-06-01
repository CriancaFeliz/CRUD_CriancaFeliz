FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev \
    && docker-php-ext-install mbstring mysqli pdo_mysql \
    && a2enmod rewrite headers \
    && sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
    && rm -rf /var/lib/apt/lists/*

RUN { \
        echo 'date.timezone=America/Sao_Paulo'; \
        echo 'upload_max_filesize=8M'; \
        echo 'post_max_size=10M'; \
        echo 'memory_limit=256M'; \
    } > /usr/local/etc/php/conf.d/criancafeliz.ini

WORKDIR /var/www/html

COPY . /var/www/html/

RUN mkdir -p /var/www/html/data /var/www/html/var/logs /var/www/html/uploads/profiles \
    && chown -R www-data:www-data /var/www/html/data /var/www/html/var /var/www/html/uploads
