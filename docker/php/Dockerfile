FROM php:8.4-cli

# システムパッケージのインストール
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm

# PHP拡張機能のインストール
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

# Composerのインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 作業ディレクトリ
WORKDIR /var/www/html

# アプリケーションファイルのコピー
COPY src/ /var/www/html/

# 依存関係のインストール
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Node依存関係のインストールとビルド
RUN npm ci && npm run build

# 権限設定
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# エントリポイントスクリプトの作成
RUN echo '#!/bin/bash\n\
set -e\n\
cd /var/www/html\n\
php artisan migrate --force\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}\n\
' > /entrypoint.sh && chmod +x /entrypoint.sh

# ポート公開
EXPOSE ${PORT:-8000}

# エントリポイント
ENTRYPOINT ["/entrypoint.sh"]