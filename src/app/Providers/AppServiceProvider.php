<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 管理者権限変更の監査ログ用オブザーバー登録
        User::observe(UserObserver::class);

        // パスワードバリデーションルールの強化
        Password::defaults(function () {
            return Password::min(12)        // 最低12文字
                ->letters()                  // 英字必須
                ->mixedCase()                // 大文字・小文字混合必須
                ->numbers()                  // 数字必須
                ->symbols()                  // 記号必須
                ->uncompromised();           // 漏洩データベースとの照合
        });

        // 本番環境でHTTPSを強制
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
