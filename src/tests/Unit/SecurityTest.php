<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * セキュリティ関連のテスト
 * - 管理者権限変更のログ記録
 * - パスワードバリデーション
 * - セキュリティヘッダー
 */
class SecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者権限が変更されたときにセキュリティログが記録されることを確認
     */
    public function test_admin_role_change_is_logged(): void
    {
        Log::shouldReceive('channel')
            ->with('security')
            ->atLeast()
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('warning')
            ->atLeast()
            ->once();

        $user = User::factory()->create(['is_admin' => false]);
        $user->is_admin = true;
        $user->save();
    }

    /**
     * 管理者ユーザーが作成されたときにセキュリティログが記録されることを確認
     */
    public function test_admin_user_creation_is_logged(): void
    {
        Log::shouldReceive('channel')
            ->with('security')
            ->atLeast()
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('warning')
            ->atLeast()
            ->once();

        User::factory()->create(['is_admin' => true]);
    }

    /**
     * 一般ユーザー作成時にはセキュリティログが記録されないことを確認
     */
    public function test_regular_user_creation_is_not_logged(): void
    {
        Log::shouldReceive('channel')
            ->with('security')
            ->never();

        User::factory()->create(['is_admin' => false]);
    }
}
