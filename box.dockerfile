FROM --platform=amd64 php:8.1-cli-alpine

RUN wget https://github.com/box-project/box/releases/download/4.0.2/box.phar \
    && mv box.phar /usr/local/bin/box \
    && chmod +x /usr/local/bin/box

RUN set -xe \
    apk update --no-cache \
    && apk add --no-cache icu icu-dev ca-certificates \
    && docker-php-ext-install intl

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# It validates requirements first, then print command list.
RUN box

ENTRYPOINT ["box"]
