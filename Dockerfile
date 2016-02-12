# Work derived from official PHP Docker Library:
# Copyright (c) 2014-2015 Docker, Inc.
# Credit: https://github.com/dockunit/docker-prebuilt
# Credit: https://hub.docker.com/r/phpunit/phpunit/

FROM debian:wheezy
MAINTAINER Pronto Tools

RUN apt-get update && apt-get install -y mysql-server libmysqlclient-dev --no-install-recommends \
    && rm -rf /var/lib/apt/lists/*

RUN apt-get update \
    && apt-get install -y ca-certificates \
    && rm -rf /var/lib/apt/lists/*

ENV PHP_INI_DIR /usr/local/etc/php
RUN mkdir -p $PHP_INI_DIR/conf.d

ENV PHP_EXTRA_CONFIGURE_ARGS --enable-fpm --with-fpm-user=www-data --with-fpm-group=www-data

ENV buildDeps=" \
        bzip2 \
        file \
        libcurl4-openssl-dev \
        libreadline6-dev \
        libssl-dev \
        libxml2-dev \
        curl \
        libxml2 \
        autoconf \
        gcc \
        libc-dev \
        make \
        patch \
        pkg-config \
    "

RUN set -x \
    && apt-get update && apt-get install -y $buildDeps --no-install-recommends && rm -rf /var/lib/apt/lists/* \
    && curl -SL "http://uk1.php.net/get/php-5.6.12.tar.gz/from/this/mirror" -o php.tar.bz \
    && mkdir -p /usr/src/php \
    && tar -xf php.tar.bz -C /usr/src/php --strip-components=1 \
    && rm php.tar.bz* \
    && cd /usr/src/php \
    && ./configure \
        --with-config-file-path="$PHP_INI_DIR" \
        --with-config-file-scan-dir="$PHP_INI_DIR/conf.d" \
        $PHP_EXTRA_CONFIGURE_ARGS \
        --disable-cgi \
        --enable-mysqlnd \
        --enable-pdo \
        --with-mysql \
        --with-pdo-mysql \
        --with-curl \
        --with-openssl \
        --with-readline \
        --with-zlib \
    && make -j"$(nproc)" \
    && make install \
    && { find /usr/local/bin /usr/local/sbin -type f -executable -exec strip --strip-all '{}' + || true; } \
    && make clean \
    && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false -o APT::AutoRemove::SuggestsImportant=false $buildDeps \
    && apt-get autoremove

COPY docker/docker-php-ext-* /usr/local/bin/

RUN chmod +x /usr/local/bin/docker-php-ext-configure \
    && chmod +x /usr/local/bin/docker-php-ext-install

ENV extensionDeps=" \
        autoconf \
        gcc \
        make \
        rsync \
        libpng12-dev \
        libmcrypt-dev \
        libxml2-dev \
        libssl-dev \
        curl \
    "

RUN extensions=" \
        gd \
        mysqli \
        soap \
        zip \
        mcrypt \
        mbstring \
    "; \
    apt-get update && apt-get install -y --no-install-recommends $extensionDeps \
    && docker-php-ext-install $extensions \
    && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false -o APT::AutoRemove::SuggestsImportant=false $extensionDeps \
    && apt-get autoremove

ENV peclDeps=" \
    curl \
    libssl-dev \
    libxml2-dev \
    make \
    autoconf \
    gcc \
    "

RUN apt-get update && apt-get install -y --no-install-recommends $peclDeps \
    && pecl install memcache && echo extension=memcache.so > $PHP_INI_DIR/conf.d/ext-memcache.ini \
    && pecl install redis && echo extension=redis.so > $PHP_INI_DIR/conf.d/ext-redis.ini \
    && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false -o APT::AutoRemove::SuggestsImportant=false $peclDeps \
    && apt-get autoremove

COPY docker/php-fpm.conf /usr/local/etc/

RUN apt-get update && apt-get install -y --no-install-recommends \
    libxml2 \
    libpng12-dev \
    mcrypt \
    curl \
    libmcrypt4 \
    less \
    && rm -rf /var/lib/apt/lists/*

RUN curl -SL --insecure "https://phar.phpunit.de/phpunit.phar" -o phpunit.phar \
    && chmod +x phpunit.phar \
    && mv phpunit.phar /usr/bin/phpunit

RUN apt-get update \
    && apt-get install -y subversion git wget ssh --no-install-recommends \
    && rm -rf /var/lib/apt/lists/*

RUN ln -s /var/run/mysqld/mysqld.sock /tmp/mysql.sock

RUN curl --insecure -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x wp-cli.phar \
    && mv wp-cli.phar /usr/local/bin/wp

RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

RUN service mysql start \
    && mysql --user="root" --execute="CREATE DATABASE wordpress_test;"

ADD bin/install-wp-tests-docker.sh .
RUN bash install-wp-tests-docker.sh wordpress_test root '' localhost latest \
    && rm install-wp-tests-docker.sh

ENV APPLICATION_ROOT /app/
WORKDIR $APPLICATION_ROOT
