FROM php:8.4-apache

RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libwebp-dev libfreetype-dev \
    imagemagick libmagickwand-dev \
    libzip-dev libxml2-dev libcurl4-openssl-dev \
    libonig-dev libicu-dev libexif-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-configure intl \
    && docker-php-ext-install gd zip mbstring dom xml curl intl exif opcache \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite headers expires

ENV APACHE_DOCUMENT_ROOT /var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

COPY . /var/www/html/

RUN cd /var/www/html && composer install --no-dev --optimize-autoloader --no-interaction

RUN mkdir -p /var/www/html/media
RUN chown -R www-data:www-data /var/www/html

CMD ["bash", "-lc", "set -eux; \
  a2dismod mpm_event mpm_worker || true; \
  rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* || true; \
  a2enmod mpm_prefork; \
  sed -i \"s/Listen 80/Listen ${PORT:-80}/g\" /etc/apache2/ports.conf; \
  sed -i \"s/*:80/*:${PORT:-80}/g\" /etc/apache2/sites-available/000-default.conf; \
  apache2ctl -t; \
  exec apache2-foreground"]
