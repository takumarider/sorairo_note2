# ============================================
# ビルドステージ
# ============================================
FROM php:8.4-cli AS builder

# システムパッケージのインストール
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# PHP拡張機能のインストール
RUN docker-php-ext-install \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    zip

# Composerのインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 作業ディレクトリ
WORKDIR /var/www/html

# 依存関係ファイルを先にコピー（キャッシュ最適化）
COPY src/composer.json src/composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction

# Nodeの依存関係をインストール
COPY src/package*.json ./
RUN npm ci

# アプリケーションファイルをコピー
COPY src/ ./

# Composerのオートローダーを生成
RUN composer dump-autoload --optimize --no-dev

# フロントエンドアセットをビルド
RUN npm run build

# ビルド成果物を確認（Vite 5+ 対応）
RUN echo "=== 📦 Checking Vite build output ===" && \
    ls -laR public/build/ && \
    echo "=== 📄 Checking manifest location ===" && \
    if [ -f "public/build/manifest.json" ]; then \
        echo "✅ Manifest at root" && cat public/build/manifest.json; \
    elif [ -f "public/build/.vite/manifest.json" ]; then \
        echo "✅ Manifest in .vite/" && cat public/build/.vite/manifest.json; \
    else \
        echo "❌ Manifest not found!"; \
        exit 1; \
    fi

# 不要なNode modulesを削除
RUN rm -rf node_modules

# ============================================
# 本番ステージ
# ============================================
FROM php:8.4-cli

# 必要な実行時パッケージのみインストール
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# PHP拡張機能をbuilderステージからコピー
COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

# 作業ディレクトリ
WORKDIR /var/www/html

# ビルドステージからアプリケーションをコピー
COPY --from=builder --chown=www-data:www-data /var/www/html ./

# ビルド成果物の確認（Vite 5+ 対応）
RUN echo "=== 📦 Verifying build assets in production ===" && \
    ls -la public/build/ && \
    if [ -f "public/build/manifest.json" ] || [ -f "public/build/.vite/manifest.json" ]; then \
        echo "✅ Manifest found"; \
    else \
        echo "❌ Manifest missing!"; \
    fi

# ストレージディレクトリの作成と権限設定
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# エントリポイントスクリプト
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
echo "========================================"\n\
echo "🚀 Sorairo Note - Starting Deployment"\n\
echo "========================================"\n\
\n\
cd /var/www/html\n\
\n\
echo "📦 Checking frontend assets..."\n\
if [ ! -d "public/build" ]; then\n\
echo "❌ ERROR: public/build directory not found!"\n\
else\n\
echo "✅ Build directory exists"\n\
if [ -f "public/build/manifest.json" ]; then\n\
    echo "✅ Manifest at root (Vite <5)"\n\
elif [ -f "public/build/.vite/manifest.json" ]; then\n\
    echo "✅ Manifest in .vite/ (Vite 5+)"\n\
else\n\
    echo "⚠️  WARNING: Manifest not found"\n\
fi\n\
echo "Built assets:"\n\
ls -lh public/build/assets/ 2>/dev/null || echo "No assets"\n\
fi\n\
\n\
echo "📦 Running database migrations..."\n\
php artisan migrate --force || { echo "❌ Migration failed"; exit 1; }\n\
\n\
echo "⚡ Caching configuration files..."\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
\n\
echo "🔗 Creating storage directory..."\n\
UPLOAD_ROOT="${RENDER_DISK_PATH:-/var/www/html/storage/app}"\n\
mkdir -p "${UPLOAD_ROOT}/public"\n\
if [ ! -w "${UPLOAD_ROOT}/public" ]; then\n\
  echo "❌ ERROR: Cannot write to ${UPLOAD_ROOT}/public"\n\
  exit 1\n\
fi\n\
chmod -R 755 "${UPLOAD_ROOT}"\n\
echo "✅ Upload directory ready: ${UPLOAD_ROOT}/public"\n\
\n\
echo "🔗 Creating storage symlink..."\n\
php artisan storage:link --force || { echo "❌ storage:link failed"; exit 1; }\n\
\n\
if [ -L "public/storage" ]; then\n\
  echo "✅ Symlink verified: public/storage -> ${UPLOAD_ROOT}/public"\n\
else\n\
  echo "❌ ERROR: Symlink verification failed"\n\
  exit 1\n\
fi\n\
\n\
echo "✅ Deployment complete!"\n\
echo "🌐 Starting web server on port ${PORT:-8000}..."\n\
echo "========================================"\n\
\n\
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}\n\
' > /entrypoint.sh && chmod +x /entrypoint.sh

# ポート公開
EXPOSE ${PORT:-8000}

# ヘルスチェック
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
  CMD curl -f http://localhost:${PORT:-8000}/ || exit 1

# セキュリティ：www-dataユーザーで実行
USER www-data

# エントリポイント
ENTRYPOINT ["/entrypoint.sh"]