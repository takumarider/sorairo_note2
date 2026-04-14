<?php

namespace Tests\Feature;

use App\Filament\Resources\SlotResource\Pages\ManageSlotCalendar;
use App\Models\TimeBlock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TimeBlockApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_list_and_delete_time_block(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $createResponse = $this->actingAs($admin)->postJson('/api/time-blocks', [
            'start_at' => now('Asia/Tokyo')->addDay()->setTime(10, 0)->toIso8601String(),
            'end_at' => now('Asia/Tokyo')->addDay()->setTime(11, 0)->toIso8601String(),
            'reason' => '研修',
        ]);

        $createResponse->assertOk();
        $createResponse->assertJsonPath('success', true);
        $this->assertDatabaseCount('time_blocks', 1);

        $listResponse = $this->actingAs($admin)->getJson('/api/time-blocks');

        $listResponse->assertOk();
        $listResponse->assertJsonPath('success', true);
        $listResponse->assertJsonCount(1, 'blocks');

        $block = TimeBlock::query()->firstOrFail();

        $deleteResponse = $this->actingAs($admin)->deleteJson('/api/time-blocks/'.$block->id);

        $deleteResponse->assertOk();
        $deleteResponse->assertJsonPath('success', true);
        $this->assertDatabaseCount('time_blocks', 0);
    }

    public function test_non_admin_cannot_manage_time_blocks(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->postJson('/api/time-blocks', [
            'start_at' => now('Asia/Tokyo')->addDay()->setTime(10, 0)->toIso8601String(),
            'end_at' => now('Asia/Tokyo')->addDay()->setTime(11, 0)->toIso8601String(),
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('time_blocks', 0);
    }

    public function test_admin_can_update_block_from_calendar(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $block = TimeBlock::create([
            'start_at' => now('Asia/Tokyo')->addDay()->setTime(10, 0),
            'end_at' => now('Asia/Tokyo')->addDay()->setTime(11, 0),
            'is_active' => true,
        ]);

        $newStart = now('Asia/Tokyo')->addDay()->setTime(13, 0)->format('Y-m-d\TH:i:s');
        $newEnd = now('Asia/Tokyo')->addDay()->setTime(14, 30)->format('Y-m-d\TH:i:s');

        Livewire::actingAs($admin)
            ->test(ManageSlotCalendar::class)
            ->call('updateBlockFromCalendar', $block->id, $newStart, $newEnd)
            ->assertHasNoErrors();

        $block->refresh();
        $this->assertEquals('13:00', $block->start_at->format('H:i'));
        $this->assertEquals('14:30', $block->end_at->format('H:i'));
    }

    public function test_update_block_rejects_non_half_hour_interval(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $block = TimeBlock::create([
            'start_at' => now('Asia/Tokyo')->addDay()->setTime(10, 0),
            'end_at' => now('Asia/Tokyo')->addDay()->setTime(11, 0),
            'is_active' => true,
        ]);

        $newStart = now('Asia/Tokyo')->addDay()->setTime(13, 0)->format('Y-m-d\TH:i:s');
        $newEnd = now('Asia/Tokyo')->addDay()->setTime(13, 45)->format('Y-m-d\TH:i:s');

        Livewire::actingAs($admin)
            ->test(ManageSlotCalendar::class)
            ->call('updateBlockFromCalendar', $block->id, $newStart, $newEnd);

        $block->refresh();
        $this->assertEquals('10:00', $block->start_at->format('H:i'));
    }
}
