FROM php:8.2-cli

RUN docker-php-ext-install pdo_mysql \
    && echo "date.timezone=America/Lima" > /usr/local/etc/php/conf.d/timezone.ini

WORKDIR /app
COPY . .

EXPOSE 8000
CMD ["php", "-S", "0.0.0.0:8000", "index.php"]
