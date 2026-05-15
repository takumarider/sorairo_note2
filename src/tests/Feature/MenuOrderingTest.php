<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuOrderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_index_orders_by_type_then_sort_order(): void
    {
        $user = User::factory()->create();

        Menu::factory()->create([
            'name' => '通常メニューB',
            'is_event' => false,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Menu::factory()->create([
            'name' => '通常メニューA',
            'is_event' => false,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Menu::factory()->create([
            'name' => 'イベントB',
            'is_event' => true,
            'sort_order' => 2,
            'duration' => 0,
            'is_active' => true,
        ]);

        Menu::factory()->create([
            'name' => 'イベントA',
            'is_event' => true,
            'sort_order' => 1,
            'duration' => 0,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('menus.index'));

        $response->assertOk();
        $response->assertSeeInOrder([
            '通常メニューA',
            '通常メニューB',
            'イベントA',
            'イベントB',
        ]);
    }

    public function test_sort_order_is_assigned_separately_for_treatments_and_events(): void
    {
        $firstTreatment = Menu::factory()->create([
            'is_event' => false,
            'sort_order' => null,
        ]);

        $secondTreatment = Menu::factory()->create([
            'is_event' => false,
            'sort_order' => null,
        ]);

        $firstEvent = Menu::factory()->create([
            'is_event' => true,
            'sort_order' => null,
            'duration' => 0,
        ]);

        $secondEvent = Menu::factory()->create([
            'is_event' => true,
            'sort_order' => null,
            'duration' => 0,
        ]);

        $this->assertSame(1, $firstTreatment->fresh()->sort_order);
        $this->assertSame(2, $secondTreatment->fresh()->sort_order);
        $this->assertSame(1, $firstEvent->fresh()->sort_order);
        $this->assertSame(2, $secondEvent->fresh()->sort_order);
    }
}
