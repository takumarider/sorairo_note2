<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Services\AvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::query()
            ->where('is_active', true)
            ->orderedByTypeForDisplay()
            ->get();

        return view('menus.index', compact('menus'));
    }

    public function show(Menu $menu)
    {
        // オプションを積極的にロード
        $menu->load(['options' => fn ($query) => $query->active()]);

        $hasSameDayAvailability = $this->hasSameDayAvailability($menu, []);

        return view('menus.show', compact('menu', 'hasSameDayAvailability'));
    }

    public function sameDayAvailability(Request $request, Menu $menu): JsonResponse
    {
        abort_if($menu->is_event || ! $menu->is_active, 404);

        $validated = $request->validate([
            'options' => 'nullable|array',
            'options.*' => ['integer', 'distinct', Rule::exists('menu_options', 'id')
                ->where('menu_id', $menu->id)
                ->where('is_active', 1)],
        ]);

        return response()->json([
            'available' => $this->hasSameDayAvailability($menu, $validated['options'] ?? []),
        ])->header('Cache-Control', 'no-store');
    }

    private function hasSameDayAvailability(Menu $menu, array $optionIds): bool
    {
        if ($menu->is_event || ! $menu->is_active) {
            return false;
        }

        $today = now('Asia/Tokyo');
        $availabilityService = new AvailabilityService;

        return $availabilityService->isMonthPublicForUsers($today)
            && $availabilityService->getAvailableTimes($menu, $optionIds, $today->toDateString()) !== [];
    }
}
