<?php

namespace Tests\Feature;

use App\Models\BusinessHour;
use App\Models\Menu;
use App\Models\Reservation;
use App\Models\User;
use App\Services\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReservationOverlapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        // 営業時間を設定（毎日 10:00-20:00）
        for ($day = 0; $day < 7; $day++) {
            BusinessHour::create([
                'day_of_week' => $day,
                'open_time' => '10:00',
                'close_time' => '20:00',
                'is_closed' => false,
            ]);
        }
    }

    public function test_user_cannot_reserve_overlapping_time_on_different_menu(): void
    {
        $menuA = Menu::factory()->create(['duration' => 60]); // 60分
        $menuB = Menu::factory()->create(['duration' => 60]); // 60分

        $date = now()->addDay()->toDateString();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // User1が 10:00-11:00 に予約
        $reservation1 = Reservation::create([
            'user_id' => $user1->id,
            'menu_id' => $menuA->id,
            'slot_id' => null,
            'date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'confirmed',
        ]);

        // デバッグ：データベースに保存されたデータを確認
        $savedReservation = Reservation::where('user_id', $user1->id)->first();
        $this->assertNotNull($savedReservation);
        // date フィールドの値をログに出力（テストランナーが見ることができる）
        $this->assertTrue(true, "Saved date: {$savedReservation->date}, start_time: {$savedReservation->start_time}, end_time: {$savedReservation->end_time}");

        // AvailabilityService で 10:30 が利用可能かどうかを確認
        $availabilityService = new AvailabilityService();
        $reservedRanges = $availabilityService->getReservedRanges(\Carbon\Carbon::createFromFormat('Y-m-d', $date)->startOfDay());
        
        // reservedRanges が正しく取得できているか確認
        $this->assertGreaterThan(0, $reservedRanges->count(), 'Reserved ranges should not be empty');

        $availableTimes = $availabilityService->getAvailableTimes($menuB, [], $date);
        
        // 10:30 は重複するので利用可能ではないはず
        $this->assertNotContains('10:30', $availableTimes, '10:30 should not be in available times due to overlap');

        // User2が同じ日の 10:30-11:30 に予約しようとする（重複する）
        $response = $this->actingAs($user2)->post('/reservations', [
            'menu_id' => $menuB->id,
            'date' => $date,
            'start_time' => '10:30',
            'options' => [],
        ]);

        // バリデーションエラーが返される or リダイレクトされる
        $this->assertTrue(
            $response->status() === 302 || $response->status() === 422,
            "Expected redirect or validation error response, got {$response->status()}"
        );

        // 予約は作成されない。重複する時間での予約なので、結果的に1つだけのはず
        $this->assertEquals(1, Reservation::count(), 'Overlapping reservation should not be created');
    }

    public function test_user_can_reserve_when_time_does_not_overlap(): void
    {
        $menuA = Menu::factory()->create(['duration' => 60]); // 60分
        $menuB = Menu::factory()->create(['duration' => 60]); // 60分

        $date = now()->addDay()->toDateString();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // User1が 10:00-11:00 に予約
        $reservation1 = Reservation::create([
            'user_id' => $user1->id,
            'menu_id' => $menuA->id,
            'slot_id' => null,
            'date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'confirmed',
        ]);

        // AvailabilityService で 11:00 が利用可能かどうかを確認
        $availabilityService = new AvailabilityService();
        $availableTimes = $availabilityService->getAvailableTimes($menuB, [], $date);
        
        // 11:00 は重複しないので利用可能のはず
        $this->assertContains('11:00', $availableTimes, '11:00 should be in available times as there is no overlap');

        // User2が同じ日の 11:00-12:00 に予約（重複しない）
        $response = $this->actingAs($user2)->post('/reservations', [
            'menu_id' => $menuB->id,
            'date' => $date,
            'start_time' => '11:00',
            'options' => [],
        ]);

        // 予約が作成される
        $reservation2 = Reservation::where('user_id', $user2->id)
            ->where('menu_id', $menuB->id)
            ->first();

        $this->assertNotNull($reservation2);
        $this->assertEquals($date, $reservation2->date->format('Y-m-d'));
        $this->assertEquals('11:00', $reservation2->start_time->format('H:i'));

        $response->assertRedirect(route('reservations.complete', ['reservation' => $reservation2->id]));

        $this->assertDatabaseCount('reservations', 2);
    }

    public function test_user_can_see_new_flow_reservation_on_mypage_after_booking(): void
    {
        $menu = Menu::factory()->create([
            'name' => 'カット&カラー',
            'duration' => 60,
        ]);

        $date = now()->addDay()->toDateString();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/reservations', [
            'menu_id' => $menu->id,
            'date' => $date,
            'start_time' => '11:00',
            'options' => [],
        ]);

        $reservation = Reservation::where('user_id', $user->id)
            ->where('menu_id', $menu->id)
            ->first();

        $this->assertNotNull($reservation);
        $this->assertNull($reservation->slot_id);
        $response->assertRedirect(route('reservations.complete', ['reservation' => $reservation->id]));

        $completeResponse = $this->actingAs($user)
            ->get(route('reservations.complete', ['reservation' => $reservation->id]));

        $completeResponse->assertOk();
        $completeResponse->assertSee('href="'.route('mypage').'"', false);

        $mypageResponse = $this->actingAs($user)->get(route('mypage'));

        $mypageResponse->assertOk();
        $mypageResponse->assertSee('カット&カラー');
        $mypageResponse->assertSee('11:00 - 12:00');
    }
}
