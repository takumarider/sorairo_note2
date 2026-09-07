<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationCommentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReservationCommentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_reservation_comment_service_operations(): void
    {
        $service = new ReservationCommentService;

        $this->assertNull($service->getComment(123));

        $service->saveComment(123, '特別なご要望テスト');
        $this->assertEquals('特別なご要望テスト', $service->getComment(123));

        $service->saveComment(123, '');
        $this->assertNull($service->getComment(123));

        $service->saveComment(123, '削除テスト');
        $service->deleteComment(123);
        $this->assertNull($service->getComment(123));
    }

    public function test_user_can_submit_reservation_with_comment_and_view_it(): void
    {
        for ($day = 0; $day < 7; $day++) {
            \App\Models\BusinessHour::create([
                'day_of_week' => $day,
                'open_time' => '10:00',
                'close_time' => '20:00',
                'is_closed' => false,
            ]);
        }

        $targetDate = now('Asia/Tokyo')->addDay();
        \App\Models\ReservationPublicationMonth::updateOrCreate([
            'year_month' => $targetDate->format('Y-m'),
        ], [
            'is_published' => true,
        ]);

        $user = User::factory()->create();
        $menu = Menu::factory()->create(['duration' => 60, 'price' => 5000]);

        $response = $this->actingAs($user)->post(route('reservations.store'), [
            'menu_id' => $menu->id,
            'date' => $targetDate->format('Y-m-d'),
            'start_time' => '10:00',
            'comment' => '肌が弱いので弱めの薬剤希望です。',
        ]);

        $response->assertRedirect();
        $reservation = Reservation::where('user_id', $user->id)->first();
        $this->assertNotNull($reservation);

        $service = new ReservationCommentService;
        $this->assertEquals('肌が弱いので弱めの薬剤希望です。', $service->getComment($reservation->id));

        // 完了画面
        $completeResponse = $this->actingAs($user)->get(route('reservations.complete', ['reservation' => $reservation->id]));
        $completeResponse->assertStatus(200);
        $completeResponse->assertSee('肌が弱いので弱めの薬剤希望です。');

        // マイページ
        $mypageResponse = $this->actingAs($user)->get(route('mypage'));
        $mypageResponse->assertStatus(200);
        $mypageResponse->assertSee('肌が弱いので弱めの薬剤希望です。');

        // 管理者カレンダー詳細
        $admin = User::factory()->create(['is_admin' => true]);
        $adminResponse = $this->actingAs($admin)->get('/admin/reservations/calendar');
        $adminResponse->assertStatus(200);
    }
}
