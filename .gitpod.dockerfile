#Inspired from https://github.com/koel/koel/blob/master/.gitpod.dockerfile

FROM gitpod/workspace-mysql:latest

ENV APACHE_DOCROOT_IN_REPO="server"

USER root
RUN apt-get remove composer -y \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer
USER gitpod