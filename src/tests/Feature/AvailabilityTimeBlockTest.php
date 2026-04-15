<?php

namespace Tests\Feature;

use App\Models\BusinessHour;
use App\Models\Menu;
use App\Models\TimeBlock;
use App\Services\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityTimeBlockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        for ($day = 0; $day < 7; $day++) {
            BusinessHour::create([
                'day_of_week' => $day,
                'open_time' => '10:00',
                'close_time' => '20:00',
                'is_closed' => false,
            ]);
        }
    }

    public function test_blocked_reason_is_returned_when_full_day_is_blocked(): void
    {
        $menu = Menu::factory()->create([
            'duration' => 60,
        ]);

        $targetDate = now('Asia/Tokyo')->addDay()->startOfDay();

        TimeBlock::create([
            'start_at' => $targetDate->copy()->setTime(10, 0),
            'end_at' => $targetDate->copy()->setTime(20, 0),
            'reason' => '終日メンテナンス',
            'is_active' => true,
        ]);

        $service = new AvailabilityService;
        $result = $service->getAvailableTimesWithReason($menu, [], $targetDate->toDateString());

        $this->assertSame([], $result['times']);
        $this->assertSame('blocked', $result['reason']);
    }
}
