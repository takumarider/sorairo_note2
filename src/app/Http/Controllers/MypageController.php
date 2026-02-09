<?php

namespace App\Http\Controllers;

use App\Models\Reservation;

class MypageController extends Controller
{
    public function index()
    {
        $reservations = auth()->user()
            ->reservations()
            ->with(['menu', 'slot'])
            ->whereHas('slot', function ($query) {
                $query->where('date', '>=', now()->toDateString());
            })
            ->where('status', 'confirmed')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('mypage', compact('reservations'));
    }

    public function cancel(Reservation $reservation)
    {
        if ($reservation->user_id !== auth()->id()) {
            abort(403, 'この予約をキャンセルする権限がありません。');
        }

        if (! $reservation->canCancel()) {
            return redirect()->route('mypage')
                ->with('error', 'この予約はキャンセルできません。');
        }

        $reservation->cancel();

        return redirect()->route('mypage')
            ->with('success', '予約をキャンセルしました。');
    }
}
