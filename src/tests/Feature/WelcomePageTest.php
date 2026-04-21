<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WelcomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_page_uses_system_settings_content(): void
    {
        SystemSetting::getSingleton()->update([
            'welcome_title' => 'テストタイトル',
            'welcome_lead' => 'テストリード文',
            'welcome_instagram_url' => 'https://www.instagram.com/test_account',
            'welcome_body_blocks' => [
                [
                    'title' => '本文見出し1',
                    'text' => '本文テキスト1',
                    'image_path' => null,
                ],
            ],
            'welcome_business_hours' => "平日 10:00〜20:00\n土曜 10:00〜18:00",
            'welcome_regular_holiday' => '不定休',
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('テストタイトル');
        $response->assertSee('テストリード文');
        $response->assertSee('本文見出し1');
        $response->assertSee('平日 10:00〜20:00');
        $response->assertSee('土曜 10:00〜18:00');
        $response->assertSee('https://www.instagram.com/test_account', false);
    }

    public function test_welcome_page_shows_dashboard_link_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertSee('ダッシュボード');
        $response->assertDontSee('ログイン');
    }

    public function test_welcome_page_shows_login_links_for_guest(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('ログイン');
        $response->assertSee('新規登録');
        $response->assertDontSee('ダッシュボード');
    }
}
