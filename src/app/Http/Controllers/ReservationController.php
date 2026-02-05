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
        if ($slot->is_reserved) {
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
        
        // スロットが予約可能か再確認
        if ($slot->is_reserved) {
            return redirect()->route('menus.index')->with('error', 'この時間枠は既に予約されています。');
        }
        
        // 予約を作成
        $reservation = null;

        DB::transaction(function () use ($slot, &$reservation) {
            $reservation = Reservation::create([
                'user_id' => auth()->id(),
                'menu_id' => $slot->menu_id,
                'slot_id' => $slot->id,
                'status' => 'confirmed',
            ]);

            // スロットを予約済みにする
            $slot->update(['is_reserved' => true]);

            // メール送信
            $this->notificationService->applyFromSettings(SystemSetting::first());

            Mail::to(auth()->user()->email)
                ->send(new ReservationConfirmed($reservation));

            // 管理者通知
            $this->notificationService->sendAdminNotification($reservation, 'confirmed');
        });
        
        return redirect()->route('reservations.complete', ['reservation' => $reservation->id]);
    }
    
    public function complete(Reservation $reservation)
    {
        // 自分の予約のみ表示可能
        if ($reservation->user_id !== auth()->id() && !auth()->user()->is_admin) {
            abort(403);
        }
        
        $reservation->load(['menu', 'slot']);
        
        return view('reservations.complete', compact('reservation'));
    }
}
