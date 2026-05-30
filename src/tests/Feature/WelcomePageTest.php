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

    public function test_welcome_page_applies_style_settings_and_paragraph_mode(): void
    {
        SystemSetting::getSingleton()->update([
            'welcome_title' => '装飾タイトル',
            'welcome_lead' => "1段落目\n\n2段落目",
            'welcome_theme_background' => 'mint',
            'welcome_theme_accent' => 'emerald',
            'welcome_hero_title_size' => 'xl',
            'welcome_hero_title_color' => 'emerald',
            'welcome_hero_text_align' => 'center',
            'welcome_hero_lead_paragraph_mode' => 'paragraph',
            'welcome_body_blocks' => [
                [
                    'title' => '装飾ブロック',
                    'text' => "本文A\n\n本文B",
                    'title_size' => 'lg',
                    'title_color' => 'emerald',
                    'text_size' => 'lg',
                    'text_color' => 'emerald',
                    'text_align' => 'center',
                    'paragraph_mode' => 'paragraph',
                    'image_path' => null,
                ],
            ],
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('装飾タイトル');
        $response->assertSee('装飾ブロック');
        $response->assertSee('from-emerald-50', false);
        $response->assertSee('text-center', false);
        $response->assertSee('text-5xl lg:text-6xl', false);
    }

    public function test_welcome_page_applies_new_color_and_size_variations(): void
    {
        SystemSetting::getSingleton()->update([
            'welcome_title' => '新スタイル',
            'welcome_subtitle' => 'サブスタイル',
            'welcome_theme_background' => 'indigo',
            'welcome_theme_accent' => 'indigo',
            'welcome_hero_title_size' => '2xl',
            'welcome_hero_title_color' => 'indigo',
            'welcome_hero_subtitle_size' => 'xs',
            'welcome_hero_subtitle_color' => 'cyan',
            'welcome_hero_lead_size' => 'xl',
            'welcome_hero_lead_color' => 'amber',
            'welcome_shop_title_size' => '2xl',
            'welcome_shop_title_color' => 'indigo',
            'welcome_shop_body_size' => 'xl',
            'welcome_shop_body_color' => 'cyan',
            'welcome_body_blocks' => [
                [
                    'title' => '本文サイズ確認',
                    'text' => '本文カラー確認',
                    'title_size' => '2xl',
                    'title_color' => 'indigo',
                    'text_size' => 'xl',
                    'text_color' => 'amber',
                    'image_path' => null,
                ],
            ],
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('text-6xl lg:text-7xl', false);
        $response->assertSee('text-indigo-900', false);
        $response->assertSee('text-base lg:text-lg', false);
        $response->assertSee('text-cyan-800', false);
        $response->assertSee('text-2xl lg:text-3xl', false);
        $response->assertSee('text-amber-700', false);
        $response->assertSee('bg-indigo-100 text-indigo-800', false);
        $response->assertSee('from-indigo-50', false);
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

    public function test_welcome_page_shows_default_contact_message_when_not_configured(): void
    {
        SystemSetting::getSingleton()->update([
            'welcome_title' => 'テストタイトル',
            'welcome_contact_number' => null,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('お問い合わせ:');
        $response->assertSee('インスタのDMにご連絡ください');
    }
}
