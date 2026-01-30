# Sorairo Note 2 - 予約管理システム

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat&logo=php)](https://php.net)
[![Vite](https://img.shields.io/badge/Vite-7-646CFF?style=flat&logo=vite)](https://vitejs.dev)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4-38B2AC?style=flat&logo=tailwind-css)](https://tailwindcss.com)

オンライン完結型の予約管理システム。ユーザーは施術メニューと時間枠（SLOT）を選択して予約を確定。管理者はFilamentで予約・メニュー・SLOTを管理します。

## 📋 目次

- [プロダクト概要](#プロダクト概要)
- [技術スタック](#技術スタック)
- [開発環境のセットア���プ](#開発環境のセットアップ)
- [プロジェクト構成](#プロジェクト構成)
- [機能仕様](#機能仕様)
- [画面設計](#画面設計)
- [データベース設計](#データベース設計)
- [デプロイ](#デプロイ)
- [開発ガイド](#開発ガイド)

---

## 🎯 プロダクト概要

### MVPスコープ

**目的**

- ✅ ユーザーがオンラインで予約を完結できる
- ✅ 管理者が翌月分の施術メニュー（参考画像込み）・時間枠（SLOT）を事前設定
- ✅ 予約の重複を防止し、先着順で予約を確定

### ターゲットユーザー

- **エンドユーザー**: 施術を予約したい一般ユーザー（スマホメイン）
- **管理者**: サロンオーナー・スタッフ（スマホ/タブレット対応）

---

## 🛠 技術スタック

### バックエンド

- **Laravel**: 12.x
- **PHP**: 8.4
- **データベース**: PostgreSQL 16
- **管理画面**: Filament 3.x

### フロントエンド

- **テンプレートエンジン**: Blade
- **ビルドツール**: Vite 7.x
- **CSS フレームワーク**: Tailwind CSS 4.x
- **JavaScript**: jQuery 3.x
- **カレンダーUI**: フルスクラッチ（Blade + JS）

### インフラ

- **開発環境**: Docker + Docker Compose
    - PHP 8.4 CLI コンテナ
    - PostgreSQL 16 コンテナ
    - Mailpit（開発用メールサーバー）
- **本番環境**: Render（予定）
- **バージョン管理**: Git + GitHub

### 開発ツール

- **パッケージ管理**: Composer (PHP), npm (Node.js)
- **コードフォーマット**: Laravel Pint（予定）
- **デバッグ**: Laravel Debugbar（開発環境のみ）

---

## 🚀 開発環境のセットアップ

### 前提条件

- Docker Desktop がインストールされていること
- Git がインストールされていること
- Node.js 20.x がインストールされていること（ホスト側）

### 1. リポジトリのクローン

```bash
git clone https://github.com/takumarider/sorairo_note2.git
cd sorairo_note2
```

### 2. 環境変数の設定

```bash
cd src
cp .env.example .env
```

`.env` を編集：

```bash
# データベース設定
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=sorairo_note2_development
DB_USERNAME=sorairo
DB_PASSWORD=password

# 管理者アカウント
ADMIN_NAME="izumi"
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=sorairo_admin

# アプリケーション設定
APP_NAME="Sorairo Note 2"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
```

### 3. Docker コンテナの起動

```bash
# プロジェクトルートに戻る
cd ..

# コンテナをビルド・起動
docker compose up -d

# 起動確認
docker ps
```

### 4. 依存関係のインストール

```bash
# PHP 依存関係（Composer）
docker exec -it sorairo_app composer install

# フロントエンド依存関係（npm）
cd src
npm install
```

### 5. アプリケーションキーの生成

```bash
docker exec -it sorairo_app php artisan key:generate
```

### 6. データベースのマイグレーション

```bash
# マイグレーション実行
docker exec -it sorairo_app php artisan migrate

# サンプルデータ投入（管理者アカウント作成）
docker exec -it sorairo_app php artisan db:seed
```

### 7. フロントエンドのビルド

```bash
# 開発サーバー起動（ホットリロード有効）
cd src
npm run dev

# または本番用ビルド
npm run build
```

### 8. アクセス確認

- **ユーザー画面**: http://localhost:8000
- **管理画面**: http://localhost:8000/admin
    - Email: `admin@example.com`
    - Password: `password`
- **メール確認**: http://localhost:8025

---

## 📁 プロジェクト構成

```
sorairo_note2/
├── docker/                      # Docker 関連
│   └── php/
│       └── Dockerfile           # PHP コンテナの設定
├── docker-compose.yml           # Docker Compose 設定
├── src/                         # Laravel プロジェクト
│   ├── app/
│   │   ├── Console/
│   │   ├── Filament/            # Filament 管理画面
│   │   │   ├── Resources/       # リソース（CRUD）
│   │   │   │   ├── MenuResource.php
│   │   │   │   ├── SlotResource.php
│   │   │   │   └── ReservationResource.php
│   │   │   └── Widgets/         # ダッシュボードウィジェット
│   │   ├── Http/
│   │   │   ├── Controllers/     # コントローラー
│   │   │   └── Middleware/
│   │   └── Models/              # Eloquent モデル
│   │       ├── User.php
│   │       ├── Menu.php
│   │       ├── Slot.php
│   │       └── Reservation.php
│   ├── database/
│   │   ├── migrations/          # マイグレーションファイル
│   │   └── seeders/             # シーダー
│   ├── resources/
│   │   ├── css/
│   │   │   └── app.css          # Tailwind CSS
│   │   ├── js/
│   │   │   └── app.js           # jQuery + Alpine.js
│   │   └── views/               # Blade テンプレート
│   │       ├── mypage.blade.php
│   │       ├── menus.blade.php
│   │       ├── slots.blade.php
│   │       └── reservations/
│   ├── routes/
│   │   └── web.php              # ルーティング
│   ├── package.json             # フロントエンド依存関係
│   ├── vite.config.js           # Vite 設定
│   ├── tailwind.config.js       # Tailwind CSS 設定
│   └── composer.json            # PHP 依存関係
├── .gitignore
└── README.md
```

---

## 📱 機能仕様

### 予約の基本ルール

1. **予約フロー**: メニュー選択 → 時間選択 → 確認 → 予約確定
2. **1 SLOT = 1予約**（先着順）
3. **予約確定した SLOT は他ユーザーは選択不可**
4. **管理者が設定していない月・時間枠は表示・選択不可**
5. **予約のキャンセルはマイページから可能**

### ユーザー機能

#### 1. 会員登録・ログイン

- メールアドレス + パスワードで登録
- Laravel Breeze による認証機能

#### 2. マイページ (`/mypage`)

- 自分の予約一覧（未来分のみ表示）
- 各予約に「キャンセル」ボタン
- 「新しく予約する」ボタン

#### 3. メニュー選択 (`/menus`)

- 施術メニュー一覧を表示
- 料金表示
- サンプル画像表示
- メニューを選択して次へ

#### 4. カレンダー（SLOT選択） (`/slots`)

- 月表示カレンダーUI
- 選択したメニューで予約可能な SLOT のみ表示
- 予約済み SLOT は非活性（グレーアウト）
- 空き SLOT をクリックして選択

#### 5. 予約確認 (`/reservations/confirm`)

- 選択内容の確認
    - メニュー名
    - 日時
    - 料金
- 「予約を確定する」ボタン
- 「戻る」ボタン

#### 6. 予約完了

- 予約完了メッセージ
- マイページへリダイレクト
- 確認メール送信（Mailpit経由）

### 管理者機能（Filament）

#### 1. ダッシュボード (`/admin`)

- 統計カード
    - 総予約数
    - 今月の予約数
    - キャンセル数
    - 登録ユーザー数
- 最近の予約一覧

#### 2. メニュー管理 (`/admin/menus`)

- メニューの CRUD
    - 名前
    - 説明
    - 料金
    - 所要時間
    - サンプル画像アップロード
- 有効/無効の切り替え

#### 3. SLOT管理 (`/admin/slots`)

- 月単位での時間枠作成
- カ���ンダーUIで作成・削除
- 時間枠の設定
    - 日付
    - 開始時間
    - 終了時間
    - 対応メニュー
- **予約済 SLOT は削除不可**（警告表示）

#### 4. 予約管理 (`/admin/reservations`)

- 予約一覧（検索・フィルター機能）
- 予約詳細の確認
- 管理者による予約のキャンセル
- ステータス変更
    - `confirmed`: 確定
    - `canceled`: キャンセル
    - `completed`: 完了

#### 5. ユーザー管理 (`/admin/users`)

- ユーザー一覧
- ユーザー情報の編集
- ユーザーの予約履歴

---

## 🎨 画面設計

### ユーザー画面（Blade）

#### マイページ

```
┌─────────────────────────────────┐
│ Sorairo Note 2                  │
│ こんにちは、○○さん              │
├──────────────────────────────��──┤
│ 📅 あなたの予約                 │
│                                 │
│ ┌─────────────────────────────┐ │
│ │ 2026/02/15 14:00-15:00      │ │
│ │ カット                      │ │
│ │ ¥3,000                      │ │
│ │        [キャンセル]         │ │
│ └─────────────────────────────┘ │
│                                 │
│ [+ 新しく予約する]              │
└─────────────────────────────────┘
```

#### メニュー選択

```
┌─────────────────────────────────┐
│ メニューを選択                   │
├─────────────────────────────────┤
│ ┌─────────────────────────────┐ │
│ │ [画像]                      │ │
│ │ カット                      │ │
│ │ ¥3,000 / 60分               │ │
│ │        [選択する]           │ │
│ └─────────────────────────────┘ │
│                                 │
│ ┌─────────────────────────────┐ │
│ │ [画像]                      │ │
│ │ カラー                      │ │
│ │ ¥5,000 / 90分               │ │
│ │        [選択する]           │ │
│ └─────────────────────────────┘ │
└─────────────────────────────────┘
```

#### カレンダー（SLOT選択）

```
┌─────────────────────────────────┐
│ 日時を選択                       │
│ メニュー: カット                 │
├─────────────────────────────────┤
│    2026年2月                    │
│  月  火  水  木  金  土  日     │
│                       1   2     │
│  3   4   5   6   7   8   9     │
│ 10  11  12  13  14  15  16     │
│                                 │
│ 2月15日（土）                   │
│ ○ 10:00-11:00 [選択]           │
│ ○ 14:00-15:00 [選択]           │
│ × 16:00-17:00 （予約済）        │
└─────────────────────────────────┘
```

#### 予約確認

```
┌─────────────────────────────────┐
│ 予約内容の確認                   │
├─────────────────────────────────┤
│ メニュー: カット                 │
│ 日時: 2026/02/15 14:00-15:00   │
│ 料金: ¥3,000                    │
│                                 │
│ [← 戻る]    [予約を確定する]   │
└─────────────────────────────────┘
```

### 管理画面（Filament）

Filament の標準UI を使用。レスポンシブ対応でスマホ・タブレットでも操作可能。

- テーブル形式のリスト表示
- モーダルでの作成・編集フォーム
- 検索・フィルター機能
- バルクアクション（一括削除など）

---

## 🗄️ データベース設計

### テーブル構成

#### users（ユーザー）

| カラム名   | 型        | 説明                       |
| ---------- | --------- | -------------------------- |
| id         | bigint    | 主キー                     |
| name       | string    | 名前                       |
| email      | string    | メールアドレス（ユニーク） |
| password   | string    | パスワード（ハッシュ化）   |
| is_admin   | boolean   | 管理者フラグ               |
| created_at | timestamp | 作成日時                   |
| updated_at | timestamp | 更新日時                   |

#### menus（施術メニュー）

| カラム名    | 型        | 説明           |
| ----------- | --------- | -------------- |
| id          | bigint    | 主キー         |
| name        | string    | メニュー名     |
| description | text      | 説明           |
| price       | integer   | 料金（円）     |
| duration    | integer   | 所要時間（分） |
| image_path  | string    | 画像パス       |
| is_active   | boolean   | 有効フラグ     |
| created_at  | timestamp | 作成日時       |
| updated_at  | timestamp | 更新日時       |

#### slots（時間枠）

| カラム名    | 型        | 説明                   |
| ----------- | --------- | ---------------------- |
| id          | bigint    | 主キー                 |
| menu_id     | bigint    | メニューID（外部キー） |
| date        | date      | 日付                   |
| start_time  | time      | 開始時間               |
| end_time    | time      | 終了時間               |
| is_reserved | boolean   | 予約済フラグ           |
| created_at  | timestamp | 作成日時               |
| updated_at  | timestamp | 更新日時               |

#### reservations（予約）

| カラム名    | 型        | 説明                                       |
| ----------- | --------- | ------------------------------------------ |
| id          | bigint    | 主キー                                     |
| user_id     | bigint    | ユーザーID（外部キー）                     |
| menu_id     | bigint    | メニューID（外部キー）                     |
| slot_id     | bigint    | スロットID（外部キー）                     |
| status      | string    | ステータス（confirmed/canceled/completed） |
| canceled_at | timestamp | キャンセル日時                             |
| created_at  | timestamp | 作成日時                                   |
| updated_at  | timestamp | 更新日時                                   |

### リレーション

```
users 1 ─────────── ∞ reservations
menus 1 ─────────── ∞ slots
menus 1 ─────────── ∞ reservations
slots 1 ─────────── 1 reservations
```

---

## 🚢 デプロイ

### Render へのデプロイ（予定）

#### 1. Web Service の作成

- リポジトリを接続
- Build Command: `composer install --optimize-autoloader --no-dev && npm install && npm run build && php artisan config:cache && php artisan route:cache && php artisan view:cache`
- Start Command: `php artisan serve --host=0.0.0.0 --port=$PORT`

#### 2. PostgreSQL の作成

- Render の PostgreSQL サービスを作成
- 接続情報を環境変数に設定

#### 3. 環境変数の設定

```
APP_ENV=production
APP_DEBUG=false
APP_KEY=<ランダム生成>
DB_CONNECTION=pgsql
DB_HOST=<Render PostgreSQL Host>
DB_PORT=5432
DB_DATABASE=<Database Name>
DB_USERNAME=<Database User>
DB_PASSWORD=<Database Password>
```

#### 4. マイグレーション

```bash
# Render Shell で実行
php artisan migrate --force
php artisan db:seed --force
```

---

## 👨‍💻 開発ガイド

### よく使うコマンド

```bash
# コンテナ起動
docker compose up -d

# コンテナ停止
docker compose down

# コンテナ内でコマンド実行
docker exec -it sorairo_app php artisan <command>

# ログ確認
docker compose logs -f app

# データベースリセット
docker exec -it sorairo_app php artisan migrate:fresh --seed

# Filament リソース作成
docker exec -it sorairo_app php artisan make:filament-resource ModelName --generate

# フロントエンド開発サーバー
cd src && npm run dev

# 本番ビルド
cd src && npm run build
```

### ブランチ戦略

- `main`: 本番環境
- `develop`: 開発環境
- `feature/*`: 機能開発
- `fix/*`: バグ修正

### コミットメッセージ

```
feat: 新機能
fix: バグ修正
docs: ドキュメント更新
style: コードフォーマット
refactor: リファクタリング
test: テスト追加
chore: その他の変更
```

---

## 📝 今後の実装予定

- [ ] ユーザー認証画面（Laravel Breeze）
- [ ] マイページ実装
- [ ] メニュー選択画面
- [ ] カレンダーUI実装
- [ ] 予約確認・確定機能
- [ ] Filament 管理画面のカスタマイズ
- [ ] メール通知機能
- [ ] レスポンシブデザインの最適化
- [ ] テストコードの作成
- [ ] Render へのデプロイ

---

## 🤝 コントリビューション

このプロジェクトは個人開発プロジェクトです。

---

## 📄 ライセンス

このプロジェクトは MIT ライセンスの下でライセンスされています。

---

## 📧 お問い合わせ

プロジェクトに関する質問は Issue を作成してください。

---

**Sorairo Note 2** - Built with ❤️ using Laravel & Filament
