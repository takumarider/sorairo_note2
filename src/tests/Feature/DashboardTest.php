<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_prioritizes_news_and_keeps_primary_links_visible(): void
    {
        $user = User::factory()->create();

        Note::create([
            'title' => '営業日のお知らせ',
            'content' => '今週の営業スケジュールを更新しました。',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Sorairo News');
        $response->assertSee('営業日のお知らせ');
        $response->assertDontSee('ようこそ');
        $response->assertDontSee('ヘルプ');
        $response->assertSee('メニュー');
        $response->assertSee('マイページ');
    }
}