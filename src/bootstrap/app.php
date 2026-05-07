<?php

use App\Services\DiscordService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Renderのリバースプロキシを信頼（本番環境でのセキュアクッキー・HTTPS検出用）
        $middleware->trustProxies(at: '*');

        // セキュリティヘッダーを全リクエストに付与
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (Throwable $e): void {
            if (! app()->environment('production')) {
                return;
            }

            if ($e instanceof ValidationException || $e instanceof AuthenticationException) {
                return;
            }

            if ($e instanceof HttpExceptionInterface && $e->getStatusCode() < 500) {
                return;
            }

            $fingerprint = sha1(implode('|', [
                $e::class,
                $e->getMessage(),
                $e->getFile(),
                (string) $e->getLine(),
            ]));

            $cacheKey = 'discord:error:'.$fingerprint;
            if (! Cache::add($cacheKey, 1, now()->addMinutes(10))) {
                return;
            }

            $requestPath = app()->runningInConsole() ? 'CLI' : request()->fullUrl();

            $message = implode("\n", [
                '【障害検知】',
                '環境: '.app()->environment(),
                '例外: '.$e::class,
                'メッセージ: '.$e->getMessage(),
                '場所: '.$e->getFile().':'.$e->getLine(),
                'リクエスト: '.$requestPath,
            ]);

            app(DiscordService::class)->send($message, [
                'category' => 'exception',
                'exception' => $e::class,
            ]);
        });
    })->create();
