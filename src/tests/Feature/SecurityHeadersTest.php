<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * セキュリティヘッダーのテスト
 */
class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * レスポンスにセキュリティヘッダーが含まれていることを確認
     */
    public function test_security_headers_are_present(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    /**
     * 本番環境以外ではHSTSヘッダーが含まれないことを確認
     */
    public function test_hsts_header_not_present_in_non_production(): void
    {
        $response = $this->get('/');

        $response->assertHeaderMissing('Strict-Transport-Security');
    }
}
