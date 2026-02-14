<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Slot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationApiController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'slot_id' => 'required|exists:slots,id',
        ]);

        $slot = Slot::with(['menu'])->findOrFail($validated['slot_id']);

        try {
            $reservation = null;

            DB::transaction(function () use ($slot, &$reservation) {
                $lockedSlot = Slot::with(['menu'])->whereKey($slot->id)->lockForUpdate()->firstOrFail();

                if (! $lockedSlot->isAvailable()) {
                    throw ValidationException::withMessages([
                        'slot_id' => 'この時間帯は既に予約されています。',
                    ]);
                }

                $reservation = Reservation::create([
                    'user_id' => auth()->id(),
                    'menu_id' => $lockedSlot->menu_id,
                    'slot_id' => $lockedSlot->id,
                    'status' => 'confirmed',
                ]);

                $lockedSlot->update(['is_reserved' => true]);
            });

            if (! $reservation instanceof Reservation) {
                return response()->json([
                    'success' => false,
                    'message' => '予約の作成に失敗しました。',
                ], 500);
            }

            $reservation->loadMissing(['menu', 'slot']);

            return response()->json([
                'success' => true,
                'message' => '予約が完了しました。',
                'reservation' => [
                    'id' => $reservation->id,
                    'menu_name' => $reservation->menu->name,
                    'date' => $reservation->slot->date->format('Y-m-d'),
                    'start_time' => $reservation->slot->start_time->format('H:i'),
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'この時間帯は既に予約されています。',
            ], 422);
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
