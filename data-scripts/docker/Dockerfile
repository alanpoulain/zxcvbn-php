FROM php:8.2-cli

WORKDIR /usr/src/app

COPY --from=mlocati/php-extension-installer:latest --link /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions gd intl zip

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        git \
        mecab \
        mecab-ipadic-utf8 \
        mecab-utils \
        python3 \
    ; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*;

RUN set -eux; \
    buildDeps='unzip'; \
    apt-get update; \
    apt-get install -y $buildDeps --no-install-recommends; \
    curl -fsSL https://github.com/shono0027/php-mecab/archive/master.zip -o php-mecab.zip; \
    unzip php-mecab.zip; \
    apt-get purge -y --auto-remove $buildDeps; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*;

RUN set -eux; \
    buildDeps='libmecab-dev'; \
    apt-get update; \
    apt-get install -y $buildDeps --no-install-recommends; \
    cd php-mecab-master/mecab; \
    phpize; \
    ./configure; \
    make; \
    make install; \
    docker-php-ext-enable mecab; \
    cd ../..; \
    rm -rf php-mecab-master; \
    apt-get purge -y --auto-remove $buildDeps; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*;

COPY --from=composer/composer:2-bin --link /composer /usr/bin/composer

ENTRYPOINT []
