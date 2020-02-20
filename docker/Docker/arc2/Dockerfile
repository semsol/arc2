FROM php:7.3-apache

RUN apt-get update && apt-get install -y curl git gnupg libicu-dev libzip-dev make nano net-tools zip zlib1g-dev

RUN docker-php-ext-install intl mysqli pdo pdo_mysql zip \
    && docker-php-ext-enable intl mysqli pdo pdo_mysql zip

# install composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer selfupdate

# fix terminal error
RUN echo "export TERM=xterm" > /etc/bash.bashrc

# configure apache2
COPY ./arc2.conf /etc/apache2/sites-enabled/000-default.conf

# add custom PHP.ini settings
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

COPY ./arc2.sh /arc2.sh
RUN chmod +x /arc2.sh

RUN rm -rf /var/www/html/*
WORKDIR /var/www/html/

# Dummy file to test later on
RUN echo "html/index" >> /var/www/html/index.html

RUN a2enmod rewrite

# adds user "arc2", adds him to group "www-data" and sets his home folder
# for more background information see:
# https://medium.com/@mccode/understanding-how-uid-and-gid-work-in-docker-containers-c37a01d01cf
RUN useradd -r --home /home/arc2 -u 1000 arc2
RUN usermod -a -G www-data arc2
RUN mkdir /home/arc2
RUN chown arc2:www-data /home/arc2

EXPOSE 80 3306

CMD ["/arc2.sh"]
