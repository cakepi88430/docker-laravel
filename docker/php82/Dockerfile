FROM php:8.2-fpm

RUN apt-get update \
    && apt-get install -y \
        libzip-dev \
        libxml2-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libpq-dev \
        libicu-dev \
        libxslt-dev \
        libonig-dev \
        zip \
        vim \
        unzip \
        curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        mysqli \
        pdo_mysql \
        pdo_pgsql \
        pgsql \
        bcmath \
        mbstring \
        xml \
        gd \
        exif \
        zip \
        soap \
        intl \
        xsl \
        pcntl \
        sysvmsg \
        sysvsem \
        sysvshm

RUN docker-php-ext-enable pdo_mysql mysqli zip mbstring soap

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www
COPY ./src ./src
RUN chown -R www-data:www-data /var/www/src

USER www-data

EXPOSE 9000