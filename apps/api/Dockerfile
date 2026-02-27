FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts

COPY . .
RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi \
    && chown -R www-data:www-data storage bootstrap/cache

COPY .docker/apache/ports.conf /etc/apache2/ports.conf
COPY .docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY .docker/start.sh /usr/local/bin/start
RUN chmod +x /usr/local/bin/start

EXPOSE 10000

CMD ["/usr/local/bin/start"]
