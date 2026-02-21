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
RUN echo '#!/bin/bash
set -e

echo "========================================"
echo "🚀 Sorairo Note - Starting Deployment"
echo "========================================"

cd /var/www/html

# ====== ここから 追記: アップロード用ディスクのパスを用意 ======
# 永続ディスクのパス (Render の環境変数 RENDER_DISK_PATH を優先)
UPLOADS_PATH="${RENDER_DISK_PATH:-/var/uploads}"
UPLOADS_PUBLIC="${UPLOADS_PATH}/public"

echo "📂 Ensuring uploads directory exists at: ${UPLOADS_PUBLIC}"

mkdir -p "${UPLOADS_PUBLIC}"

# www-data で実行している前提なので、sudo は不要
chown -R www-data:www-data "${UPLOADS_PATH}" || echo "⚠️ chown failed (maybe not permitted)"
chmod -R 775 "${UPLOADS_PATH}" || echo "⚠️ chmod failed (maybe not permitted)"

# Laravel 側の設定:
#   FILESYSTEM_DISK_ROOT=/var/uploads/public
#   RENDER_DISK_PATH=/var/uploads
# にしておくと、disk("public") と storage:link の両方がここを指す
echo "📌 FILESYSTEM_DISK_ROOT=${FILESYSTEM_DISK_ROOT:-not set}"
echo "📌 RENDER_DISK_PATH=${RENDER_DISK_PATH:-not set}"
# ====== ここまで 追記 ======

echo "📦 Checking frontend assets..."
if [ ! -d "public/build" ]; then
  echo "❌ ERROR: public/build directory not found!"
else
  echo "✅ Build directory exists"
  if [ -f "public/build/manifest.json" ]; then
    echo "✅ Manifest at root (Vite <5)"
  elif [ -f "public/build/.vite/manifest.json" ]; then
    echo "✅ Manifest in .vite/ (Vite 5+)"
  else
    echo "⚠️  WARNING: Manifest not found"
  fi
  echo "Built assets:"
  ls -lh public/build/assets/ 2>/dev/null || echo "No assets"
fi

echo "📦 Running database migrations..."
php artisan migrate --force || { echo "❌ Migration failed"; exit 1; }

echo "⚡ Caching configuration files..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🔗 Creating storage symlink..."
php artisan storage:link || echo "⚠️ storage:link failed (maybe already exists)"

echo "✅ Deployment complete!"
echo "🌐 Starting web server on port ${PORT:-8000}..."
echo "========================================"

exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
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