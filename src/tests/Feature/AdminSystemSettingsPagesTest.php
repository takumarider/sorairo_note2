<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSystemSettingsPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_notification_settings_page(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/system-settings');

        $response->assertOk();
        $response->assertSee('通知元設定（共通）');
        $response->assertSee('管理者通知設定');
        $response->assertDontSee('メイン見出し');
    }

    public function test_admin_can_open_welcome_page_settings_page(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/welcome-page-settings');

        $response->assertOk();
        $response->assertSee('ウェルカムページ設定');
        $response->assertSee('メイン見出し');
        $response->assertSee('確認');
        $response->assertDontSee('管理者通知先メールアドレス');
    }
}