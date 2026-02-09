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
    npm \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

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
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# エントリポイントスクリプトの作成
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
echo "🚀 Starting Sorairo Note deployment..."\n\
\n\
cd /var/www/html\n\
\n\
echo "📦 Running migrations..."\n\
php artisan migrate --force\n\
\n\
echo "⚡ Caching configuration..."\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
\n\
echo "✅ Starting web server on port ${PORT:-8000}..."\n\
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}\n\
' > /entrypoint.sh && chmod +x /entrypoint.sh

# ポート公開
EXPOSE ${PORT:-8000}

# ユーザー切り替え（セキュリティのため��
USER www-data

# エントリポイント
ENTRYPOINT ["/entrypoint.sh"]