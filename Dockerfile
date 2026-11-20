FROM php:8.4-fpm-bookworm

ENV DEBIAN_FRONTEND=noninteractive \
    COMPOSER_ALLOW_SUPERUSER=1 \
    PORT=10000

# Extensions nécessaires à Laravel et à l'application :
#  - pdo_pgsql : base Neon
#  - pdo_mysql : lecture de la base locale lors de la migration des données
#  - gd, zip, intl, bcmath :Traitements applicatifs et import/export
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
      nginx supervisor gettext-base git unzip \
      libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
      libicu-dev libpq-dev libonig-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j"$(nproc)" \
      pdo_pgsql pdo_mysql gd zip intl bcmath opcache \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Installation des dépendances avant copie du code : meilleur cache Docker.
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts --optimize-autoloader

COPY . .

RUN composer dump-autoload --optimize --no-dev \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod +x artisan

COPY docker/nginx.conf.template /etc/nginx/templates/default.conf.template
COPY docker/supervisord.conf /etc/supervisor/conf.d/srsp.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 10000

CMD ["/usr/local/bin/entrypoint.sh"]
