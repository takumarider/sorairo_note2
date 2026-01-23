FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
  git unzip libpq-dev \
  && docker-php-ext-install pdo pdo_pgsql

# Node.js 20 LTS を追加
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
  && apt-get install -y nodejs

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
