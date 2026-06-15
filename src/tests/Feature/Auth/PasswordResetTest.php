<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use RuntimeException;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'NewStr0ng!Pass#1',
                'password_confirmation' => 'NewStr0ng!Pass#1',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            return true;
        });
    }

    public function test_reset_password_link_request_returns_validation_error_when_mail_delivery_fails(): void
    {
        Password::shouldReceive('sendResetLink')
            ->once()
            ->with(['email' => 'test@example.com'])
            ->andThrow(new RuntimeException('SMTP authentication failed.'));

        $response = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => 'test@example.com',
        ]);

        $response
            ->assertRedirect('/forgot-password')
            ->assertSessionHasErrors([
                'email' => 'パスワード再設定メールの送信に失敗しました。時間をおいて再度お試しください。',
            ]);
    }

    public function test_reset_password_notification_strings_are_translated_to_japanese(): void
    {
        Lang::setLocale('ja');

        $this->assertSame('パスワード再設定のご案内', Lang::get('Reset Password Notification'));
        $this->assertSame('アカウントのパスワード再設定リクエストを受け付けたため、このメールを送信しています。', Lang::get('You are receiving this email because we received a password reset request for your account.'));
        $this->assertSame('パスワードを再設定する', Lang::get('Reset Password'));
        $this->assertSame('このパスワード再設定リンクの有効期限は 60 分です。', Lang::get('This password reset link will expire in :count minutes.', ['count' => 60]));
        $this->assertSame('もしこのパスワード再設定を依頼していない場合は、追加の対応は不要です。', Lang::get('If you did not request a password reset, no further action is required.'));
    }
}
