FROM php:7.3-cli-stretch

LABEL maintainer="Sam Burney <sburney@sifnt.net.au>"

RUN apt-get -y update \
    && apt-get -y dist-upgrade \
    && apt-get -y install \
    git unzip \
    zlib1g-dev libzip-dev libxml2-dev \
    && apt-get clean

RUN docker-php-ext-install -j$(nproc) \
    zip xml pdo_mysql

RUN cd /usr/local/bin \
    && curl --silent --show-error https://getcomposer.org/installer | php \
    && chmod +x composer.phar 

ADD . /app

WORKDIR /app

RUN composer.phar install

ENTRYPOINT ["/usr/local/bin/php"]

CMD ["--version"]
