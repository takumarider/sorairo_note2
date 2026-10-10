<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\ReservationComment;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\ReservationCommentService;
use Illuminate\Support\Facades\Auth;

class MypageController extends Controller
{
    public function __construct(
        private NotificationService $notificationService,
        private ReservationCommentService $commentService
    ) {}

    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        $reservations = $user
            ->reservations()
            ->with(['menu', 'slot', 'options'])
            ->whereDate('date', '>=', now()->toDateString())
            ->where('status', 'confirmed')
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        $unreadMessageCount = ReservationComment::query()
            ->whereIn('reservation_id', $user->reservations()->select('id'))
            ->where('sender_type', ReservationComment::SENDER_ADMIN)
            ->whereNull('deleted_by_admin_at')
            ->whereNull('read_at')
            ->count();

        return view('mypage', compact('reservations', 'unreadMessageCount'));
    }

    public function messages()
    {
        $commentReservations = $this->messageReservations(false);
        $isArchive = false;
        $hasArchive = $this->messageReservations(true)->isNotEmpty();

        return view('mypage.messages', compact('commentReservations', 'isArchive', 'hasArchive'));
    }

    public function archivedMessages()
    {
        $commentReservations = $this->messageReservations(true);
        $isArchive = true;
        $hasArchive = true;

        return view('mypage.messages', compact('commentReservations', 'isArchive', 'hasArchive'));
    }

    private function messageReservations(bool $archived)
    {
        /** @var User $user */
        $user = Auth::user();
        $today = now('Asia/Tokyo')->toDateString();

        $query = $user
            ->reservations()
            ->with(['menu', 'latestVisibleComment'])
            ->withCount([
                'comments as unread_admin_comments_count' => fn ($query) => $query
                    ->where('sender_type', ReservationComment::SENDER_ADMIN)
                    ->whereNull('deleted_by_admin_at')
                    ->whereNull('read_at'),
            ]);

        if ($archived) {
            $query->where(fn ($q) => $q->whereDate('date', '<', $today)->orWhere('status', 'canceled'));
        } else {
            $query->whereDate('date', '>=', $today)->where('status', '!=', 'canceled');
        }

        return $query->orderByDesc('date')->orderByDesc('start_time')->get();
    }

    public function comments(Reservation $reservation)
    {
        /** @var User $user */
        $user = Auth::user();

        $this->ensureReservationOwner($reservation, $user);

        $reservation->load(['menu', 'options']);
        $this->commentService->markReceivedCommentsAsRead($reservation, ReservationComment::SENDER_ADMIN);
        $comments = $this->commentService->commentsFor($reservation->id)
            ->whereNull('hidden_by_user_at')
            ->whereNull('deleted_by_admin_at')
            ->values();

        return view('mypage.comments', compact('reservation', 'comments'));
    }

    public function storeComment(Reservation $reservation)
    {
        /** @var User $user */
        $user = Auth::user();

        $this->ensureReservationOwner($reservation, $user);

        $validated = request()->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $this->commentService->send(
            $reservation,
            $user,
            ReservationComment::SENDER_USER,
            $validated['body']
        );

        return redirect()
            ->route('mypage.reservations.comments', $reservation)
            ->with('success', 'コメントを送信しました。');
    }

    public function destroyComment(Reservation $reservation, ReservationComment $comment)
    {
        /** @var User $user */
        $user = Auth::user();

        $this->ensureReservationOwner($reservation, $user);

        abort_unless(
            $comment->reservation_id === $reservation->id
                && $comment->sender_type === ReservationComment::SENDER_USER,
            404
        );

        $comment->update(['hidden_by_user_at' => now()]);

        return redirect()
            ->route('mypage.reservations.comments', $reservation)
            ->with('success', 'メッセージを削除しました。');
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
        $this->notificationService->sendReservationCanceledToUser($reservation);
        $this->notificationService->sendAdminNotification($reservation, 'canceled');

        return redirect()->route('mypage')
            ->with('success', '予約をキャンセルしました。');
    }

    private function ensureReservationOwner(Reservation $reservation, User $user): void
    {
        abort_unless($reservation->user_id === $user->id, 403);
    }
}
