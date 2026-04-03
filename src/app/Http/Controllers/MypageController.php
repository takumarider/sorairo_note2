<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MypageController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        $reservations = $user
            ->reservations()
            ->with(['menu', 'slot'])
            ->whereDate('date', '>=', now()->toDateString())
            ->where('status', 'confirmed')
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return view('mypage', compact('reservations'));
    }

    public function cancel(Reservation $reservation)
    {
        if ($reservation->user_id !== Auth::id()) {
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
