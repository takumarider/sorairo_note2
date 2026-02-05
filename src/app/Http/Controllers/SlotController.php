<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Slot;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SlotController extends Controller
{
    public function index(Request $request)
    {
        $menuId = $request->input('menu_id');
        $menu = Menu::findOrFail($menuId);

        $weekStartInput = $request->input('week_start');
        $weekStart = $weekStartInput
            ? Carbon::parse($weekStartInput)->startOfWeek(Carbon::MONDAY)
            : now()->startOfWeek(Carbon::MONDAY);
        $weekEnd = (clone $weekStart)->addDays(6);

        $slots = Slot::where('menu_id', $menuId)
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->groupBy(fn ($slot) => $slot->date->format('Y-m-d'));

        $weekDays = collect();
        for ($i = 0; $i < 7; $i++) {
            $currentDate = (clone $weekStart)->addDays($i);
            $formatted = $currentDate->format('Y-m-d');
            $weekDays->push([
                'date' => $currentDate,
                'slots' => $slots->get($formatted, collect()),
            ]);
        }

        $prevWeek = (clone $weekStart)->subWeek();
        $nextWeek = (clone $weekStart)->addWeek();

        return view('slots.index', [
            'menu' => $menu,
            'weekDays' => $weekDays,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'prevWeek' => $prevWeek,
            'nextWeek' => $nextWeek,
        ]);
    }
}
