<?php

namespace Tests\Feature;

use App\Mail\AdminReservationNotification;
use App\Mail\ReservationCanceled;
use App\Mail\ReservationConfirmed;
use App\Models\Menu;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationMailRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmed_mail_can_render_with_null_slot_id(): void
    {
        $user = User::factory()->create([
            'name' => 'テストユーザー',
        ]);
        $menu = Menu::factory()->create([
            'name' => 'カット',
            'price' => 5500,
            'duration' => 60,
        ]);

        $reservation = Reservation::create([
            'user_id' => $user->id,
            'menu_id' => $menu->id,
            'slot_id' => null,
            'date' => '2026-04-01',
            'start_time' => '09:30',
            'end_time' => '10:30',
            'status' => 'confirmed',
        ])->load(['user', 'menu']);

        $rendered = (new ReservationConfirmed($reservation))->render();

        $this->assertStringContainsString('予約確定のお知らせ', $rendered);
        $this->assertStringContainsString('2026年04月01日', $rendered);
        $this->assertStringContainsString('09:30 - 10:30', $rendered);
    }

    public function test_admin_notification_mail_can_render_with_null_slot_id(): void
    {
        $user = User::factory()->create([
            'name' => '管理通知確認ユーザー',
            'email' => 'customer@example.com',
        ]);
        $menu = Menu::factory()->create([
            'name' => 'カラー',
            'price' => 7000,
        ]);

        $reservation = Reservation::create([
            'user_id' => $user->id,
            'menu_id' => $menu->id,
            'slot_id' => null,
            'date' => '2026-04-02',
            'start_time' => '11:00',
            'end_time' => '12:00',
            'status' => 'confirmed',
        ])->load(['user', 'menu']);

        $rendered = (new AdminReservationNotification($reservation, 'confirmed'))->render();

        $this->assertStringContainsString('新規予約通知', $rendered);
        $this->assertStringContainsString('2026年04月02日', $rendered);
        $this->assertStringContainsString('11:00 - 12:00', $rendered);
    }

    public function test_canceled_mail_can_render_with_null_slot_id(): void
    {
        $user = User::factory()->create([
            'name' => 'キャンセル確認ユーザー',
        ]);
        $menu = Menu::factory()->create([
            'name' => 'パーマ',
            'price' => 8000,
        ]);

        $reservation = Reservation::create([
            'user_id' => $user->id,
            'menu_id' => $menu->id,
            'slot_id' => null,
            'date' => '2026-04-03',
            'start_time' => '14:00',
            'end_time' => '15:30',
            'status' => 'canceled',
        ])->load(['user', 'menu']);

        $rendered = (new ReservationCanceled($reservation))->render();

        $this->assertStringContainsString('予約キャンセルのお知らせ', $rendered);
        $this->assertStringContainsString('2026年04月03日', $rendered);
        $this->assertStringContainsString('14:00 - 15:30', $rendered);
    }
}
