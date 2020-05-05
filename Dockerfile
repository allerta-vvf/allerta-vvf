FROM trafex/alpine-nginx-php7:latest AS webserver

LABEL maintainer="matteo@matteogheza.it"
LABEL version="1.2"
LABEL description="Docker project for open source firefighter management software"

COPY ./server /var/www/html

# Install composer from the official image
COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
USER root
RUN apk add --no-cache bash sed php-pdo php-pdo_mysql php-pdo_sqlite php-pdo_pgsql
RUN composer install --optimize-autoloader --no-interaction --no-progress

USER nobody
EXPOSE 8080