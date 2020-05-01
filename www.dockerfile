FROM composer:1.9 AS builder

WORKDIR /app
COPY . .
RUN composer install

FROM php:7.2-apache

EXPOSE 80
RUN a2enmod rewrite
COPY --from=builder /app /root

WORKDIR /root
RUN chown -R www-data: /root
RUN vendor/bin/asbestos install --port 80
RUN a2dissite 000-default
RUN a2ensite 100-torrent-machine
RUN sed -i "s/^Listen/#Listen/" /etc/apache2/sites-enabled/100-torrent-machine.conf
