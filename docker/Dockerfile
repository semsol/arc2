FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    curl git gnupg libicu-dev libzip-dev make nano net-tools zip zlib1g-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install intl pdo pdo_mysql zip \
    && docker-php-ext-enable intl pdo pdo_mysql zip

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN groupadd -g 1000 arc2 && \
    useradd -m -u 1000 -g arc2 -G www-data -s /bin/bash arc2

COPY ./entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /home/arc2/code

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
