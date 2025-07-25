# Base image có PHP và Composer
FROM php:8.2-apache

# Cài Composer
RUN apt-get update && \
    apt-get install -y zip unzip git && \
    curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

# Copy source code vào container
COPY . /var/www/html

# Cài đặt thư viện PHP từ Composer
WORKDIR /var/www/html
RUN composer install

# Mở cổng 80
EXPOSE 80
