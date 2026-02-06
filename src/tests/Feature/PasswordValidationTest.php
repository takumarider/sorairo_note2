<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * パスワードバリデーション強化のテスト
 */
class PasswordValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 弱いパスワードで新規登録が拒否されることを確認
     */
    public function test_weak_password_is_rejected_on_registration(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * 数字のみのパスワードが拒否されることを確認
     */
    public function test_numeric_only_password_is_rejected(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '123456789012',
            'password_confirmation' => '123456789012',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * 記号なしのパスワードが拒否されることを確認
     */
    public function test_password_without_symbols_is_rejected(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Abcdefgh1234',
            'password_confirmation' => 'Abcdefgh1234',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * 強力なパスワードで新規登録が受け入れられることを確認
     */
    public function test_strong_password_is_accepted_on_registration(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Str0ng!Pass#2026',
            'password_confirmation' => 'Str0ng!Pass#2026',
        ]);

        $response->assertSessionHasNoErrors();
    }

    /**
     * 弱いパスワードでパスワード変更が拒否されることを確認
     */
    public function test_weak_password_is_rejected_on_update(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/password', [
            'current_password' => 'password',
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ]);

        $response->assertSessionHasErrors('password', null, 'updatePassword');
    }
}
