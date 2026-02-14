<?php

namespace App\Http\Controllers;

use App\Mail\ReservationConfirmed;
use App\Models\Reservation;
use App\Models\Slot;
use App\Models\SystemSetting;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function confirm(Request $request)
    {
        $slotId = $request->input('slot_id');
        $slot = Slot::with(['menu'])->findOrFail($slotId);

        // スロットが予約可能か確認
        if (! $slot->isAvailable()) {
            return redirect()->route('menus.index')->with('error', 'この時間枠は既に予約されています。');
        }

        return view('reservations.confirm', compact('slot'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'slot_id' => 'required|exists:slots,id',
        ]);

        $slot = Slot::with(['menu'])->findOrFail($request->slot_id);

        try {
            // 予約を作成
            $reservation = null;

            DB::transaction(function () use ($slot, &$reservation) {
                $lockedSlot = Slot::whereKey($slot->id)->lockForUpdate()->firstOrFail();

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

                // スロットを予約済みにする
                $lockedSlot->update(['is_reserved' => true]);

                // メール送信
                $this->notificationService->applyFromSettings(SystemSetting::first());

                Mail::to(auth()->user()->email)
                    ->send(new ReservationConfirmed($reservation));

                // 管理者通知
                $this->notificationService->sendAdminNotification($reservation, 'confirmed');
            });

            if (! $reservation instanceof Reservation) {
                return redirect()->route('menus.index')->with('error', '予約の作成に失敗しました。');
            }
        } catch (ValidationException $e) {
            return redirect()->route('menus.index')->with('error', 'この時間帯は既に予約されています。');
        }

        return redirect()->route('reservations.complete', ['reservation' => $reservation->id]);
    }

    public function complete(Reservation $reservation)
    {
        // 自分の予約のみ表示可能
        if ($reservation->user_id !== auth()->id() && ! auth()->user()->is_admin) {
            abort(403);
        }

        $reservation->load(['menu', 'slot']);

        return view('reservations.complete', compact('reservation'));
    }
}
