<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Slot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationApiController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'slot_id' => 'required|exists:slots,id',
        ]);

        $slot = Slot::with(['menu'])->findOrFail($validated['slot_id']);

        if (! $slot->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'この時間枠は既に予約されています。',
            ], 422);
        }

        try {
            $reservation = null;

            DB::transaction(function () use ($slot, &$reservation) {
                $reservation = Reservation::create([
                    'user_id' => auth()->id(),
                    'menu_id' => $slot->menu_id,
                    'slot_id' => $slot->id,
                    'status' => 'confirmed',
                ]);

                $slot->update(['is_reserved' => true]);
            });

            return response()->json([
                'success' => true,
                'message' => '予約が完了しました。',
                'reservation' => [
                    'id' => $reservation->id,
                    'menu_name' => $slot->menu->name,
                    'date' => $slot->date->format('Y-m-d'),
                    'start_time' => $slot->start_time->format('H:i'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '予約の作成に失敗しました。',
            ], 500);
        }
    }

    public function destroy(Reservation $reservation)
    {
        if ($reservation->user_id !== auth()->id() && ! auth()->user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'この予約をキャンセルする権限がありません。',
            ], 403);
        }

        if (! $reservation->canCancel()) {
            return response()->json([
                'success' => false,
                'message' => 'この予約はキャンセルできません。',
            ], 422);
        }

        try {
            $reservation->cancel();

            return response()->json([
                'success' => true,
                'message' => '予約をキャンセルしました。',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'キャンセルに失敗しました。',
            ], 500);
        }
    }
}
