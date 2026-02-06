<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * セキュリティ関連のHTTPヘッダーを付与するミドルウェア
 *
 * - X-Content-Type-Options: MIMEスニッフィング防止
 * - X-Frame-Options: クリックジャッキング防止
 * - X-XSS-Protection: ブラウザXSSフィルター有効化
 * - Referrer-Policy: リファラー情報の制御
 * - Strict-Transport-Security: HTTPS強制（本番環境のみ）
 * - Permissions-Policy: ブラウザ機能アクセス制限
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // MIMEスニッフィング防止
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // クリックジャッキング防止（iframe埋め込みを同一オリジンのみに制限）
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // ブラウザXSSフィルター有効化
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // リファラー情報の制御（同一オリジンにはフルURL、外部にはオリジンのみ送信）
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // ブラウザ機能アクセス制限（カメラ・マイク・位置情報等を無効化）
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // 本番環境ではHSTS（HTTP Strict Transport Security）を有効化
        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
