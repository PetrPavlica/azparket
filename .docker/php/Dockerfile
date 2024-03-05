ARG PHP_VERSION=""
FROM php:${PHP_VERSION:+${PHP_VERSION}-}fpm-alpine

RUN apk update; \
    apk upgrade; \
    apk add --no-cache \
    libzip-dev \
    shadow \
    autoconf \
    libtool \
    icu-dev \
    imap-dev \
    curl-dev \
    pcre-dev \
    freetype \
    freetype-dev \
    imagemagick-dev \
    imagemagick \
    libjpeg-turbo \
    libjpeg-turbo-dev \
    libpng \
    libpng-dev \
    libxml2-dev \
    libmcrypt-dev \
    bzip2-dev \
    gettext-dev \
    gmp-dev \
    libxslt-dev \
    tidyhtml-dev \
    g++ \
    make \
    git

RUN docker-php-ext-configure bcmath && \
    docker-php-ext-configure zip && \
    docker-php-ext-configure imap --with-imap --with-imap-ssl

RUN docker-php-ext-configure gd \
    --with-freetype=/usr/include/ \
    --with-jpeg=/usr/include/ && \
  NPROC=$(grep -c ^processor /proc/cpuinfo 2>/dev/null || 1) && \
  docker-php-ext-install -j${NPROC} gd

RUN docker-php-ext-install \
    mysqli \
    pdo_mysql \
    zip \
    exif \
    pcntl \
    opcache \
    bcmath \
    curl \
    intl \
    imap \
    bz2 \
    calendar \
    dba \
    gettext \
    shmop \
    soap \
    sockets \
    gmp \
    xmlrpc \
    xsl \
    tidy \
    sysvsem \
    sysvmsg \
    sysvshm

RUN pecl install imagick

RUN docker-php-ext-enable imagick

RUN docker-php-ext-enable imap opcache

RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

RUN apk del autoconf g++ libtool make pcre-dev

#RUN composer self-update 1.10.19

WORKDIR /var/www/html