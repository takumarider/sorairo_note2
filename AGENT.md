# 🤖 AI Agent Development Guide - Sorairo Note 2

このドキュメントは、GitHub Copilot Agent やその他の AI 開発支援ツールが、このプロジェクトのコンテキストを理解し、効率的にコード生成・修正を行うための完全な指針です。

---

## 📋 目次

1. [プロジェクト概要](#プロジェクト概要)
2. [技術スタック](#技術スタック)
3. [アーキテクチャ](#アーキテクチャ)
4. [権限管理システム](#権限管理システム)
5. [コーディング規約](#コーディング規約)
6. [データベース設計](#データベース設計)
7. [UI/UX 仕様](#uiux-仕様)
8. [実装状況](#実装状況)
9. [開発フロー](#開発フロー)
10. [よく使うコマンド](#よく使うコマンド)
11. [重要な設計判断](#重要な設計判断)
12. [トラブルシューティング](#トラブルシューティング)
13. [AI Agent への指示例](#ai-agent-への指示例)

---

## 🎯 プロジェクト概���

### プロダクト名

**Sorairo Note 2** - オンライン予約管理システム

### 目的

サロン向けの予約管理システム。ユーザーは施術メニューと時間枠（SLOT）を選択して予約を完結。管理者はFilamentで予約・メニュー・SLOTを管理。

### ターゲット

- **エンドユーザー**: 施術を予約したい一般ユーザー（スマホメイン）
- **管理者**: サロンオーナー・スタッフ（スマホ/タブレット対応）

### MVP スコープ

1. ✅ ユーザー認証（Laravel Breeze）
2. ✅ 管理者識別システム（is_admin フラグ）
3. ✅ 管理画面アクセスボタン
4. ⏳ メニュー選択画面
5. ⏳ カレンダー UI（SLOT 選択）
6. ⏳ 予約確認・確定
7. ⏳ マイページ（予約一覧・キャンセル）
8. ✅ Filament 管理画面（メニュー・SLOT・予約管理）

---

## 🛠 技術スタック

### バックエンド

- **Laravel**: 12.x
- **PHP**: 8.4
  - 拡張: intl, zip, pdo_pgsql
- **Database**: PostgreSQL 16
- **Admin Panel**: Filament 3.x

### フロントエンド

- **Template Engine**: Blade
- **Build Tool**: Vite 7.x
- **CSS Framework**: Tailwind CSS 4.x
- **JavaScript**: jQuery 3.x + Alpine.js 3.x
- **Icons**: Heroicons

### インフラ

- **Development**: Docker Compose
  - `sorairo_app`: PHP 8.4 CLI
  - `sorairo_db`: PostgreSQL 16
  - `sorairo_mail`: Mailpit
- **Production**: Render（予定）

### 依存関係管理

- **PHP**: Composer
- **Node.js**: npm (Node.js 20.x)

---

## 🏗 アーキテクチャ

### ディレクトリ構成

```
sorairo_note2/
├── docker/
│   └── php/
│       └── Dockerfile           # PHP 8.4 + intl + zip + pdo_pgsql
├── docker-compose.yml           # app, db, mail
├── src/                         # Laravel プロジェクト
│   ├── app/
│   │   ├── Console/
│   │   │   └── Commands/
│   │   │       └── CreateAdminUser.php  # 管理者作成コマンド
│   │   ├── Filament/            # Filament 管理画面
│   │   │   ├── Resources/
│   │   │   │   ├── MenuResource.php
│   │   │   │   ├── SlotResource.php
│   │   │   │   ├── ReservationResource.php
│   │   │   │   └── UserResource.php
│   │   │   ├── Widgets/
│   │   │   │   └── StatsOverview.php
│   │   │   └── Pages/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── MenuController.php
│   │   │   │   ├── SlotController.php
│   │   │   │   └── ReservationController.php
│   │   │   └── Middleware/
│   │   └── Models/
│   │       ├── User.php         # FilamentUser 実装済み、is_admin フラグ
│   │       ├── Menu.php
│   │       ├── Slot.php
│   │       └── Reservation.php
│   ├── database/
│   │   ├── migrations/
│   │   │   ├── 0001_01_01_000000_create_users_table.php
│   │   │   ├── xxxx_add_is_admin_to_users_table.php
│   │   │   ├── xxxx_create_menus_table.php
│   │   │   ├── xxxx_create_slots_table.php
│   │   │   └── xxxx_create_reservations_table.php
│   │   └── seeders/
│   │       └── DatabaseSeeder.php  # 管理者アカウント作成（is_admin = true）
│   ├── resources/
│   │   ├── css/
│   │   │   └── app.css          # Tailwind CSS
│   │   ├── js/
│   │   │   ├── app.js           # jQuery + Alpine.js
│   │   │   └── calendar.js      # カレンダー UI（未実装）
│   │   └── views/
│   │       ├── layouts/
│   │       │   ├── app.blade.php
│   │       │   ├── guest.blade.php
│   │       │   └── navigation.blade.php  # 管理画面ボタンあり
│   │       ├── welcome.blade.php
│   │       ├── dashboard.blade.php
│   │       ├── mypage.blade.php      # 未実装
│   │       ├── menus/
│   │       │   └── index.blade.php   # 未実装
│   │       ├── slots/
│   │       │   └── index.blade.php   # 未実装
│   │       └── reservations/
│   │           ├── confirm.blade.php # 未実装
│   │           └── complete.blade.php # 未実装
│   ├── routes/
│   │   └── web.php
│   ├── .env                     # Git 除外（機密情報）
│   ├── .env.example             # Git 含む（テンプレート）
│   ├── package.json
│   ├── vite.config.js
│   ├── tailwind.config.js
│   └── composer.json
├── .gitignore
├── README.md
└── AGENT.md                     # このファイル
```

---

## 🔐 権限管理システム

### 管理者識別の仕様

#### is_admin フラグによる識別

- **users テーブル**: `is_admin` カラム（boolean, default: false）
- **管理者**: `is_admin = true` → Filament 管理画面にアクセス可能
- **一般ユーザー**: `is_admin = false` → ユーザー画面のみアクセス可能

#### アクセス制御の仕組み

```php
// app/Models/User.php
public function canAccessPanel(Panel $panel): bool
{
    return $this->is_admin;  // 管理者のみ Filament にアクセス可能
}
```

#### UI での表示制御

```blade
{{-- resources/views/layouts/navigation.blade.php --}}
@if(auth()->check() && auth()->user()->is_admin)
    <a href="{{ route('filament.admin.pages.dashboard') }}">
        🔧 管理画面
    </a>
@endif
```

### 実装詳細

#### 1. データベース構造

```php
// database/migrations/xxxx_add_is_admin_to_users_table.php
Schema::table('users', function (Blueprint $table) {
    $table->boolean('is_admin')->default(false)->after('email');
});
```

#### 2. User モデル

```php
// app/Models/User.php
namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',  // ← 管理者フラグ
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',  // ← boolean にキャスト
        ];
    }

    // Filament のアクセス制御
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin;
    }

    // リレーション
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
```

#### 3. Seeder での管理者作成

```php
// database/seeders/DatabaseSeeder.php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 管理者アカウント
        if (!User::where('email', env('ADMIN_EMAIL'))->exists()) {
            User::create([
                'name' => env('ADMIN_NAME', 'Admin User'),
                'email' => env('ADMIN_EMAIL', 'admin@example.com'),
                'password' => bcrypt(env('ADMIN_PASSWORD', 'password')),
                'email_verified_at' => now(),
                'is_admin' => true,  // ← 管理者フラグ
            ]);
        }

        // テストユーザー（一般ユーザー）
        User::factory()->count(10)->create([
            'is_admin' => false,
        ]);
    }
}
```

#### 4. 環境変数（.env）

```bash
# Filament 管理者アカウント
ADMIN_NAME="管理者"
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=password
```

#### 5. ナビゲーションバーの実装

**デスクトップ版**

```blade
{{-- resources/views/layouts/navigation.blade.php --}}
<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                {{-- Logo & Navigation Links --}}
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    {{-- 管理画面ボタン（管理者のみ表示） --}}
                    @if(auth()->check() && auth()->user()->is_admin)
                        <x-nav-link
                            :href="route('filament.admin.pages.dashboard')"
                            target="_blank"
                            class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:text-gray-700 focus:border-gray-300"
                        >
                            <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            {{ __('管理画面') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            {{-- Settings Dropdown --}}
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            {{-- Hamburger --}}
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- モバイルメニュー --}}
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            {{-- 管理画面ボタン（管理者のみ表示） --}}
            @if(auth()->check() && auth()->user()->is_admin)
                <x-responsive-nav-link :href="route('filament.admin.pages.dashboard')" target="_blank">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        {{ __('管理画面') }}
                    </div>
                </x-responsive-nav-link>
            @endif
        </div>

        {{-- User Options --}}
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
```

---

## 📏 コーディング規約

### PHP（Laravel）

#### 命名規則

- **クラス**: PascalCase（例: `MenuController`, `ReservationResource`）
- **メソッド**: camelCase（例: `getAvailableSlots()`, `cancelReservation()`）
- **変数**: camelCase（例: `$userName`, `$availableSlots`）
- **定数**: UPPER_SNAKE_CASE（例: `MAX_SLOTS_PER_DAY`）

#### ファイル配置

- **Controllers**: `app/Http/Controllers/`
- **Models**: `app/Models/`
- **Filament Resources**: `app/Filament/Resources/`
- **Migrations**: `database/migrations/`
- **Seeders**: `database/seeders/`

#### Laravel ベストプラクティス

```php
// ✅ Good: Route Model Binding
public function show(Menu $menu)
{
    return view('menus.show', compact('menu'));
}

// ✅ Good: Eloquent リレーション
public function slots()
{
    return $this->hasMany(Slot::class);
}

// ✅ Good: Query Builder（N+1 問題回避）
$reservations = Reservation::with(['user', 'menu', 'slot'])->get();

// ✅ Good: 条件分岐（Early Return）
public function canCancelReservation(Reservation $reservation): bool
{
    if (!auth()->check()) {
        return false;
    }

    if ($reservation->user_id !== auth()->id() && !auth()->user()->is_admin) {
        return false;
    }

    return $reservation->status === 'confirmed';
}

// ❌ Bad: 直接 SQL
DB::select('SELECT * FROM users WHERE id = ?', [$id]);
```

### Blade テンプレート

#### ファイル名

- **小文字 + ハイフン**: `menu-list.blade.php`, `slot-calendar.blade.php`

#### 構造

```blade
{{-- ✅ Good: レイアウト継承 --}}
@extends('layouts.app')

@section('title', 'メニュー一覧')

@section('content')
    <div class="container mx-auto px-4">
        {{-- コンテンツ --}}
    </div>
@endsection

{{-- ✅ Good: コンポーネント使用 --}}
<x-button type="primary">予約する</x-button>

{{-- ✅ Good: 条件分岐 --}}
@if($slots->count() > 0)
    @foreach($slots as $slot)
        <div class="slot-item" data-slot-id="{{ $slot->id }}">
            {{ $slot->start_time }} - {{ $slot->end_time }}
        </div>
    @endforeach
@else
    <p class="text-gray-500">予約可能な時間がありません</p>
@endif

{{-- ✅ Good: 認証チェック --}}
@auth
    <p>ログイン中: {{ auth()->user()->name }}</p>
@endauth

{{-- ✅ Good: 管理者チェック --}}
@if(auth()->check() && auth()->user()->is_admin)
    <a href="{{ route('filament.admin.pages.dashboard') }}">管理画面</a>
@endif
```

### JavaScript（jQuery）

#### ファイル配置

- **メイン**: `resources/js/app.js`
- **モジュール**: `resources/js/modules/`（例: `calendar.js`, `reservation.js`）

#### スタイル

```javascript
// ✅ Good: jQuery Ready
$(document).ready(function () {
  console.log("jQuery loaded");
});

// ✅ Good: イベント委譲
$(document).on("click", ".slot-item", function () {
  const slotId = $(this).data("slot-id");
  selectSlot(slotId);
});

// ✅ Good: AJAX リクエスト（CSRF トークン付き）
$.ajaxSetup({
  headers: {
    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
  },
});

$.ajax({
  url: "/api/slots",
  method: "GET",
  data: { date: "2026-02-15", menu_id: 1 },
  success: function (response) {
    renderSlots(response.slots);
  },
  error: function (xhr) {
    console.error("Error:", xhr.responseJSON);
    alert("エラーが発生しました");
  },
});

// ✅ Good: 関数の定義
function selectSlot(slotId) {
  $(".slot-item").removeClass("selected");
  $(`.slot-item[data-slot-id="${slotId}"]`).addClass("selected");
  $("#selected-slot-id").val(slotId);
}
```

### CSS（Tailwind CSS）

#### クラス命名

```html
<!-- ✅ Good: ユーティリティファースト -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
  <h1 class="text-3xl font-bold text-gray-900 mb-4">メニュー一覧</h1>
</div>

<!-- ✅ Good: レスポンシブ -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
  <div class="bg-white rounded-lg shadow p-4">
    <!-- カード -->
  </div>
</div>

<!-- ✅ Good: 状態管理 -->
<button
  class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:bg-gray-400"
>
  予約する
</button>

<!-- ⚠️ カスタムクラスは最小限に -->
<style>
  .custom-calendar-grid {
    /* 複雑な独自スタイルのみ */
    display: grid;
    grid-template-columns: repeat(7, 1fr);
  }
</style>
```

---

## 🗄️ データベース設計

### テーブル定義

#### users

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->boolean('is_admin')->default(false);  // ← 管理者フラグ
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->rememberToken();
    $table->timestamps();

    $table->index('is_admin');  // 管理者検索用インデックス
});
```

#### menus

```php
Schema::create('menus', function (Blueprint $table) {
    $table->id();
    $table->string('name');                        // メニュー名
    $table->text('description')->nullable();       // 説明
    $table->integer('price');                      // 料金（円）
    $table->integer('duration');                   // 所要時間（分）
    $table->string('image_path')->nullable();      // 画像パス
    $table->boolean('is_active')->default(true);   // 有効フラグ
    $table->timestamps();

    $table->index('is_active');  // アクティブメニュー検索用
});
```

#### slots

```php
Schema::create('slots', function (Blueprint $table) {
    $table->id();
    $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
    $table->date('date');                          // 日付
    $table->time('start_time');                    // 開始時間
    $table->time('end_time');                      // 終了時間
    $table->boolean('is_reserved')->default(false); // 予約済フラグ
    $table->timestamps();

    // ユニーク制約：同じメニュー・日付・時間の重複を防ぐ
    $table->unique(['menu_id', 'date', 'start_time'], 'unique_slot');

    // インデックス
    $table->index(['date', 'is_reserved']);  // 日付・予約状況での検索用
});
```

#### reservations

```php
Schema::create('reservations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
    $table->foreignId('slot_id')->constrained()->cascadeOnDelete();
    $table->enum('status', ['confirmed', 'canceled', 'completed'])->default('confirmed');
    $table->timestamp('canceled_at')->nullable();
    $table->timestamps();

    // インデックス
    $table->index(['user_id', 'status']);  // ユーザーの予約検索用
    $table->index('slot_id');              // スロット検索用
});
```

### リレーション

```php
// User.php
public function reservations()
{
    return $this->hasMany(Reservation::class);
}

public function futureReservations()
{
    return $this->reservations()
        ->whereHas('slot', function ($query) {
            $query->where('date', '>=', now()->toDateString());
        })
        ->where('status', 'confirmed');
}

// Menu.php
public function slots()
{
    return $this->hasMany(Slot::class);
}

public function reservations()
{
    return $this->hasMany(Reservation::class);
}

public function availableSlots()
{
    return $this->slots()
        ->where('date', '>=', now()->toDateString())
        ->where('is_reserved', false)
        ->orderBy('date')
        ->orderBy('start_time');
}

// Slot.php
public function menu()
{
    return $this->belongsTo(Menu::class);
}

public function reservation()
{
    return $this->hasOne(Reservation::class);
}

// Reservation.php
public function user()
{
    return $this->belongsTo(User::class);
}

public function menu()
{
    return $this->belongsTo(Menu::class);
}

public function slot()
{
    return $this->belongsTo(Slot::class);
}
```

---

## 🎨 UI/UX 仕様

### 管理画面ボタンの表示

#### デスクトップ版

```
┌──────────────────────────────────────────────────┐
│ Sorairo Note 2  [Dashboard] [🔧 管理画面]        │
│                               こんにちは、管理者さん │
└──────────────────────────────────────────────────┘
```

#### モバイル版

```
┌──────────────────┐
│ ☰  Sorairo Note 2│
├──────────────────┤
│ Dashboard        │
│ 🔧 管理画面      │  ← is_admin = true のみ表示
│ Profile          │
│ Log Out          │
└──────────────────┘
```

### ボタンデザイン仕様

#### デスクトップ

- **位置**: ヘッダー右側、Dashboard の隣
- **スタイル**: テキストリンク（ナビゲーションと同じスタイル）
- **アイコン**: 歯車アイコン（Heroicon: cog-6-tooth）
- **ホバー**: 下線表示 + テキスト色変化
- **ターゲット**: `target="_blank"`（新しいタブで開く）

#### モバイル

- **位置**: ハンバーガーメニュー内、Dashboard の下
- **スタイル**: リストアイテムと同じ
- **アイコン**: 歯車アイコン + テキスト

### カラー仕様（Tailwind CSS）

```css
/* プライマリカラー */
text-gray-500       /* 通常状態 */
text-gray-700       /* ホバー状態 */
border-gray-300     /* ホバー時のボーダー */

/* 管理画面固有（Filament） */
bg-amber-500        /* Filament のプライマリカラー */
```

---

## 📊 実装状況

### ✅ 完了

- [x] Docker 環境構築（PHP 8.4 + PostgreSQL 16 + Mailpit）
- [x] Laravel 12 セットアップ
- [x] Vite + Tailwind CSS + jQuery 構成
- [x] Laravel Breeze（認証）インストール
- [x] Filament 3.x インストール
- [x] **is_admin フラグ実装**
  - [x] マイグレーション作成
  - [x] User モデル更新
  - [x] DatabaseSeeder 更新
- [x] **管理画面ボタン実装**
  - [x] navigation.blade.php 更新
  - [x] デスクトップ版ボタン
  - [x] モバイル版ボタン
- [x] User モデル（FilamentUser 実装）
- [x] 管理者アカウント作成機能（Seeder + Command）
- [x] .env / .env.example 分離
- [x] README.md 整備
- [x] AGENT.md 作成

### 🚧 進行中（Phase 2）

- [ ] Menu モデル + マイグレーション + Factory
- [ ] Slot モデル + マイグレーション + Factory
- [ ] Reservation モデル + マイグレーション
- [ ] Filament Resources
  - [ ] MenuResource（CRUD + 画像アップロード）
  - [ ] SlotResource（カレンダーUI + 一括作成）
  - [ ] ReservationResource（ステータス管理）
  - [ ] UserResource（is_admin フラグ編集）
- [ ] Filament Widgets
  - [ ] StatsOverview（統計カード）

### ⏳ 未実装（Phase 3-4）

- [ ] ユーザー画面
  - [ ] メニュー選択画面（/menus）
  - [ ] カレンダー UI（/slots）
  - [ ] 予約確認画面（/reservations/confirm）
  - [ ] 予約完了画面（/reservations/complete）
  - [ ] マイページ（/mypage）
  - [ ] 予約キャンセル機能
- [ ] API エンドポイント
  - [ ] GET `/api/slots?date=2026-02-15&menu_id=1`
  - [ ] POST `/api/reservations`
  - [ ] DELETE `/api/reservations/{id}`
- [ ] JavaScript（カレンダー UI）
  - [ ] calendar.js 実装
  - [ ] AJAX 予約処理
- [ ] メール通知
  - [ ] 予約確定メール
  - [ ] キャンセルメール
- [ ] 画像アップロード（Filament）
- [ ] テストコード
- [ ] Render デプロイ設定

---

## 🔄 開発フロー

### Phase 1: 権限管理システム（完了✅）

#### 実装済み項目

1. ✅ マイグレーション（add_is_admin_to_users_table）
2. ✅ User モデル更新（$fillable, casts, canAccessPanel）
3. ✅ DatabaseSeeder 更新（is_admin = true で管理者作成）
4. ✅ navigation.blade.php に管理画面ボタン追加
5. ✅ デスクトップ・モバイル両対応

### Phase 2: データベース構築（次のステップ）

#### Step 1: Menu モデル作成

```bash
docker exec -it sorairo_app php artisan make:model Menu -mf
```

```php
// database/migrations/xxxx_create_menus_table.php
Schema::create('menus', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->integer('price');
    $table->integer('duration');
    $table->string('image_path')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();

    $table->index('is_active');
});
```

```php
// app/Models/Menu.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration',
        'image_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function slots()
    {
        return $this->hasMany(Slot::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function availableSlots()
    {
        return $this->slots()
            ->where('date', '>=', now()->toDateString())
            ->where('is_reserved', false)
            ->orderBy('date')
            ->orderBy('start_time');
    }
}
```

#### Step 2: Slot モデル作成

```bash
docker exec -it sorairo_app php artisan make:model Slot -mf
```

```php
// database/migrations/xxxx_create_slots_table.php
Schema::create('slots', function (Blueprint $table) {
    $table->id();
    $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
    $table->date('date');
    $table->time('start_time');
    $table->time('end_time');
    $table->boolean('is_reserved')->default(false);
    $table->timestamps();

    $table->unique(['menu_id', 'date', 'start_time'], 'unique_slot');
    $table->index(['date', 'is_reserved']);
});
```

```php
// app/Models/Slot.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'date',
        'start_time',
        'end_time',
        'is_reserved',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_reserved' => 'boolean',
        ];
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function reservation()
    {
        return $this->hasOne(Reservation::class);
    }

    public function isAvailable(): bool
    {
        return !$this->is_reserved && $this->date >= now()->toDateString();
    }
}
```

#### Step 3: Reservation モデル作成

```bash
docker exec -it sorairo_app php artisan make:model Reservation -m
```

```php
// database/migrations/xxxx_create_reservations_table.php
Schema::create('reservations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
    $table->foreignId('slot_id')->constrained()->cascadeOnDelete();
    $table->enum('status', ['confirmed', 'canceled', 'completed'])->default('confirmed');
    $table->timestamp('canceled_at')->nullable();
    $table->timestamps();

    $table->index(['user_id', 'status']);
    $table->index('slot_id');
});
```

```php
// app/Models/Reservation.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'menu_id',
        'slot_id',
        'status',
        'canceled_at',
    ];

    protected function casts(): array
    {
        return [
            'canceled_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function slot()
    {
        return $this->belongsTo(Slot::class);
    }

    public function cancel()
    {
        $this->update([
            'status' => 'canceled',
            'canceled_at' => now(),
        ]);

        // Slot を解放
        $this->slot->update(['is_reserved' => false]);
    }
}
```

#### Step 4: マイグレーション実行

```bash
docker exec -it sorairo_app php artisan migrate
```

#### Step 5: Filament Resources 作成

```bash
# Menu リソース
docker exec -it sorairo_app php artisan make:filament-resource Menu --generate

# Slot リソース
docker exec -it sorairo_app php artisan make:filament-resource Slot --generate

# Reservation リソース
docker exec -it sorairo_app php artisan make:filament-resource Reservation --generate

# User リソース（既存ユーザーの管理）
docker exec -it sorairo_app php artisan make:filament-resource User --generate
```

#### Step 6: Filament Widget 作成

```bash
docker exec -it sorairo_app php artisan make:filament-widget StatsOverview --type=stats
```

### Phase 3: ユーザー画面実装（予定）

詳細は Phase 2 完了後に具体化

### Phase 4: 統合・テスト（予定）

詳細は Phase 3 完了後に具体化

---

## 🔧 よく使うコマンド

### Docker

```bash
# コンテナ起動
docker compose up -d

# コンテナ停止
docker compose down

# コンテナ再起動
docker compose restart app

# ログ確認（リアルタイム）
docker compose logs -f app

# コンテナ内に入る
docker exec -it sorairo_app bash

# データベースに接続
docker exec -it sorairo_db psql -U sorairo_note2 -d sorairo_note2
```

### Laravel

```bash
# マイグレーション
docker exec -it sorairo_app php artisan migrate
docker exec -it sorairo_app php artisan migrate:fresh --seed
docker exec -it sorairo_app php artisan migrate:rollback
docker exec -it sorairo_app php artisan migrate:status

# シーダー
docker exec -it sorairo_app php artisan db:seed
docker exec -it sorairo_app php artisan db:seed --class=DatabaseSeeder

# キャッシュクリア
docker exec -it sorairo_app php artisan cache:clear
docker exec -it sorairo_app php artisan config:clear
docker exec -it sorairo_app php artisan route:clear
docker exec -it sorairo_app php artisan view:clear
docker exec -it sorairo_app php artisan optimize:clear

# Artisan コマンド一覧
docker exec -it sorairo_app php artisan list

# Tinker（REPL）
docker exec -it sorairo_app php artisan tinker

# 管理者作成コマンド
docker exec -it sorairo_app php artisan admin:create

# アプリケーション情報
docker exec -it sorairo_app php artisan about
```

### Filament

```bash
# リソース作成（自動生成）
docker exec -it sorairo_app php artisan make:filament-resource ModelName --generate

# ウ��ジェット作成
docker exec -it sorairo_app php artisan make:filament-widget WidgetName
docker exec -it sorairo_app php artisan make:filament-widget StatsOverview --type=stats

# ページ作成
docker exec -it sorairo_app php artisan make:filament-page PageName

# ユーザー作成
docker exec -it sorairo_app php artisan make:filament-user

# Filament のアセット公開
docker exec -it sorairo_app php artisan filament:assets
```

### フロントエンド

```bash
# 依存関係インストール
cd src && npm install

# 開発サーバー（HMR 有効）
npm run dev

# 本番ビルド
npm run build

# ビルドファイル削除
rm -rf public/build

# ビルド確認
ls -la public/build/
```

### Composer

```bash
# パッケージインストール
docker exec -it sorairo_app composer install

# パッケージ追加
docker exec -it sorairo_app composer require vendor/package

# パッケージ削除
docker exec -it sorairo_app composer remove vendor/package

# オートロード再生成
docker exec -it sorairo_app composer dump-autoload

# パッケージ更新
docker exec -it sorairo_app composer update
```

### Git

```bash
# ブランチ作成
git checkout -b feature/menu-implementation

# 変更をステージング
git add .

# コミット
git commit -m "feat: Implement Menu model and migration"

# プッシュ
git push origin feature/menu-implementation

# マージ
git checkout main
git merge feature/menu-implementation
```

---

## 💡 重要な設計判断

### 1. なぜ Docker を使うのか？

- **環境の一貫性**: 開発・本番で同じ環境
- **依存関係の隔離**: ホストマシンを汚さない
- **チーム開発**: 誰でも同じ環境を再現可能
- **PostgreSQL**: Docker で簡単にセットアップ

### 2. なぜ Filament を使うのか？

- **迅速な管理画面構築**: CRUD が自動生成さ��る
- **スマホ対応**: レスポンシブデザインが標準
- **拡張性**: カスタマイズが容易
- **Laravel との統合**: Eloquent と完全に統合

### 3. なぜ jQuery を使うのか？

- **シンプルな DOM 操作**: カレンダー UI に最適
- **学習コスト**: Vue/React より習得が早い
- **Alpine.js との共存**: Breeze のデフォルト構成を維持
- **軽量**: 大規模 SPA は不要

### 4. SLOT の設計思想

- **1 SLOT = 1予約**: シンプルで競合が少ない
- **is_reserved フラグ**: 予約状態を即座に判定
- **ユニーク制約**: 同じ時間の重複を防ぐ
- **論理削除なし**: 予約済 SLOT は物理的に削除不可

### 5. 予約のキャンセルポリシー

- **ユーザー**: 自分の予約のみキャンセル可能
- **管理者**: すべての予約をキャンセル可能
- **SLOT の解放**: キャンセル時に `is_reserved = false` に更新
- **ソフトデリート**: 予約履歴は保持（canceled_at）

### 6. 管理者識別方法

- **is_admin フラグ**: シンプルで拡張性がある
- **ロールベース（将来）**: 複数の権限レベルが必要になった場合は Spatie Permission を導入
- **UI での判定**: `auth()->user()->is_admin` で Blade 内で簡単に判定
- **セキュリティ**: Filament の `canAccessPanel()` で保護

### 7. 管理画面へのアクセス制御

- **Filament 側**: `canAccessPanel()` メソッドで is_admin をチェック
- **ユーザー画面**: 管理画面ボタンの表示/非表示で UX を向上
- **セキュ��ティ**: URL 直接アクセスでも Filament のミドルウェアが保護
- **ターゲット**: 新しいタブで開く（`target="_blank"`）

### 8. データベースインデックス戦略

- **users.is_admin**: 管理者検索用
- **slots.date + is_reserved**: 予約可能スロット検索用
- **reservations.user_id + status**: ユーザーの予約一覧用
- **パフォーマンス**: N+1 問題を Eager Loading で解決

---

## 🚨 トラブルシューティング

### エラー: `No application encryption key`

```bash
# APP_KEY を生成
docker exec -it sorairo_app php artisan key:generate

# .env を確認
docker exec -it sorairo_app grep APP_KEY .env
# 出力: APP_KEY=base64:xxxxx
```

### エラー: `intl extension not found`

```bash
# Dockerfile を確認（既に追加済み）
cat docker/php/Dockerfile | grep intl

# コンテナを再ビルド
docker compose down
docker compose build --no-cache
docker compose up -d

# intl が有効か確認
docker exec -it sorairo_app php -m | grep intl
```

### エラー: `Connection refused [db]`

```bash
# .env の DB_HOST を確認
docker exec -it sorairo_app grep DB_HOST .env
# 出力: DB_HOST=db

# コンテナが起動しているか確認
docker ps
# sorairo_db が Up 状態か確認

# データベース接続テスト
docker exec -it sorairo_db psql -U sorairo_note2 -d sorairo_note2 -c "SELECT version();"
```

### フロントエンドが更新されない

```bash
# Vite 開発サーバーを再起動
cd src
npm run dev

# キャッシュをクリア
rm -rf public/build
npm run build

# ブラウザのキャッシュもクリア（Ctrl+Shift+R）
```

### Filament にログインできない

```bash
# 管理者アカウントを確認
docker exec -it sorairo_app php artisan tinker
>>> User::where('email', 'admin@example.com')->first();
>>> User::where('is_admin', true)->get();

# is_admin フラグを確認
docker exec -it sorairo_app php artisan tinker
>>> User::find(1)->is_admin;

# 管理者アカウントを再作成
docker exec -it sorairo_app php artisan admin:create
```

### 管理画面ボタンが表示されない

```bash
# ユーザーの is_admin を確認
docker exec -it sorairo_app php artisan tinker
>>> auth()->user()->is_admin;

# Blade のキャッシュをクリア
docker exec -it sorairo_app php artisan view:clear

# ブラウザをリロード（Ctrl+Shift+R）
```

### マイグレーションエラー

```bash
# マイグレーション状態を確認
docker exec -it sorairo_app php artisan migrate:status

# ロールバック
docker exec -it sorairo_app php artisan migrate:rollback

# 完全リセット（データ消失注意！）
docker exec -it sorairo_app php artisan migrate:fresh --seed
```

---

## 🤖 AI Agent への指示例

### 良い指示の例

#### モデル・マイグレーション作成

```
Menu モデルとマイグレーションを作成してください。
以下のカラムが必要です：
- name (string): メニュー名
- description (text, nullable): 説明
- price (integer): 料金（円）
- duration (integer): 所要時間（分）
- image_path (string, nullable): 画像パス
- is_active (boolean, default: true): 有効フラグ

リレーション：
- slots() - hasMany
- reservations() - hasMany
- availableSlots() - スコープ（date >= 今日、is_reserved = false）
```

#### Filament リソース作成

```
MenuResource を作成してください。
要件：
- 日本語ラベル（名前、説明、料金、所要時間、画像、有効）
- 画像アップロード機能（FileUpload コンポーネント）
- is_active でフィルター
- 料金は「¥」付きで表示
- 所要時間は「分」付きで表示
```

#### 権限管理

```
Reservation モデルに、ユーザーがキャンセル可能かチェックする
canCancel() メソッドを追加してください。
条件：
- 自分の予約、または管理者（is_admin = true）
- status が 'confirmed'
- 予約日時が未来
```

#### UI 実装

```
resources/views/menus/index.blade.php を作成してください。
要件：
- layouts.app を継承
- Tailwind CSS でカード形式のグリッド表示
- 各カードに画像、名前、説明、料金、所要時間
- 「選択する」ボタン（/slots?menu_id={id} へリンク）
- レスポンシブ（モバイル: 1列、タブレット: 2列、デスクトップ: 3列）
```

### 避けるべき指示

❌ **曖昧な指示**

```
予約システムを作って
```

→ 具体的なモデル、テーブル、画面を指定してください。

❌ **複数の異なるタスク**

```
メニューとスロットとユーザー画面を全部作って
```

→ 1つずつ段階的に指示してください。

❌ **コンテキスト不足**

```
エラーが出ます。直してください。
```

→ エラーメッセージ、ファイル名、実行したコマンドを含めてください。

❌ **仕様が不明確**

```
かっこいい画面を作って
```

→ 具体的なレイアウト、カラー、機能を指定してください。

---

## 📝 次のステップ（Phase 2）

### 優先順位

#### 1. Menu モデル + マイグレーション（1時間）

- [x] マイグレーション作成
- [x] Menu モデル作成
- [x] リレーション定義
- [x] Factory 作成（テストデータ用）

#### 2. Slot モデル + マイグレーション（1時間）

- [x] マイグレーション作成（ユニーク制約付き）
- [x] Slot モデル作成
- [x] リレーション定義
- [x] isAvailable() メソッド実装

#### 3. Reservation モデル + マイグレーション（1時間）

- [x] マイグレーション作成
- [x] Reservation モデル作成
- [x] リレーション定義
- [x] cancel() メソッド実装

#### 4. Filament Resources（2-3時間）

- [ ] MenuResource（画像アップロード付き）
- [ ] SlotResource（一括作成機能）
- [ ] ReservationResource（ステータス管理）
- [ ] UserResource（is_admin フラグ編集）

#### 5. Filament Widgets（1時間）

- [ ] StatsOverview（統計カード）
  - 総予約数
  - 今月の予約数
  - キャンセル数
  - 登録ユーザー数

---

## 📚 参考リソース

- [Laravel 12 ドキュメント](https://laravel.com/docs/12.x)
- [Filament 3 ドキュメント](https://filamentphp.com/docs/3.x)
- [Tailwind CSS ドキュメント](https://tailwindcss.com/docs)
- [Vite ドキュメント](https://vitejs.dev/)
- [Heroicons](https://heroicons.com/)
- [Alpine.js ドキュメント](https://alpinejs.dev/)
- [jQuery API ドキュメント](https://api.jquery.com/)

---

**最終更新**: 2026-01-27  
**プロジェクトステータス**: Phase 1 完了 ✅ → Phase 2 開始  
**バージョン**: 1.0.0
