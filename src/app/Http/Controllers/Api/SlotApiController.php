<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Slot;
use Illuminate\Http\Request;

class SlotApiController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'date' => 'required|date|after_or_equal:today',
        ]);

        $slots = Slot::where('menu_id', $validated['menu_id'])
            ->where('date', $validated['date'])
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'success' => true,
            'slots' => $slots->map(function (Slot $slot) {
                return [
                    'id' => $slot->id,
                    'date' => $slot->date->format('Y-m-d'),
                    'start_time' => $slot->start_time->format('H:i'),
                    'end_time' => $slot->end_time->format('H:i'),
                    'is_reserved' => $slot->is_reserved,
                    'is_available' => $slot->isAvailable(),
                ];
            }),
        ]);
    }
}
