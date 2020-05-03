FROM trafex/alpine-nginx-php7:latest

LABEL maintainer="matteo@matteogheza.it"
LABEL version="1.0"
LABEL description="Docker project for open source firefighter management software"

# Install composer from the official image
COPY --from=composer /usr/bin/composer /usr/bin/composer
# Run composer install to install the dependencies
#RUN composer install --optimize-autoloader --no-interaction --no-progress

EXPOSE 8080