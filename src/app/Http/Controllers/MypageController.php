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
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $reservation->canCancelBy($user)) {
            return redirect()->route('mypage')
                ->with('error', $reservation->cancellationFailureReasonBy($user));
        }

        $reservation->cancel();

        return redirect()->route('mypage')
            ->with('success', '予約をキャンセルしました。');
    }
}
