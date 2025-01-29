FROM php:8.4-cli

LABEL authors="tinesimenc"

RUN apt-get update && apt-get install -y \
    libicu-dev \
        libzip-dev \
        libxml2-dev \
        libonig-dev \
        libssl-dev \
        git \
        zip \
        unzip

RUN docker-php-ext-install pdo pdo_mysql intl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . /var/www

RUN composer install --no-dev --optimize-autoloader

EXPOSE 80
CMD ["php", "-S", "0.0.0.0:80", "-t", "public"]

