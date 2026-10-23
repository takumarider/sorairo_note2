<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\ReservationComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class ReservationCommentService
{
    private const DIRECTORY = 'reservation_comments';

    public function getComment(int $reservationId): ?string
    {
        return $this->commentsFor($reservationId)
            ->firstWhere('sender_type', ReservationComment::SENDER_USER)
            ?->body;
    }

    public function saveComment(int $reservationId, ?string $comment): void
    {
        $trimmed = trim((string) $comment);

        if ($trimmed === '') {
            return;
        }

        $reservation = Reservation::findOrFail($reservationId);

        $this->send($reservation, $reservation->user, ReservationComment::SENDER_USER, $trimmed);
    }

    public function deleteComment(int $reservationId): void
    {
        ReservationComment::where('reservation_id', $reservationId)->delete();

        $path = self::DIRECTORY.'/'.$reservationId.'.txt';
        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
    }

    public function commentsFor(int $reservationId): Collection
    {
        $this->importLegacyComment($reservationId);

        return ReservationComment::query()
            ->with('sender')
            ->where('reservation_id', $reservationId)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
    }

    public function send(Reservation $reservation, User $sender, string $senderType, string $body): ReservationComment
    {
        return $reservation->comments()->create([
            'sender_type' => $senderType,
            'sender_id' => $sender->id,
            'body' => trim($body),
        ]);
    }

    public function markReceivedCommentsAsRead(Reservation $reservation, string $senderType): void
    {
        $reservation->comments()
            ->where('sender_type', $senderType)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function markLatestReceivedCommentAsUnread(Reservation $reservation, string $senderType): void
    {
        $latest = $reservation->comments()
            ->where('sender_type', $senderType)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        $latest?->update(['read_at' => null]);
    }

    public function importLegacyComments(): int
    {
        $importedCount = 0;

        foreach (Storage::disk('local')->files(self::DIRECTORY) as $path) {
            $filename = pathinfo($path, PATHINFO_FILENAME);
            if (! ctype_digit($filename)) {
                continue;
            }

            if ($this->importLegacyComment((int) $filename)) {
                $importedCount++;
            }
        }

        return $importedCount;
    }

    private function importLegacyComment(int $reservationId): bool
    {
        if (ReservationComment::where('reservation_id', $reservationId)->exists()) {
            return false;
        }

        $path = self::DIRECTORY.'/'.$reservationId.'.txt';
        if (! Storage::disk('local')->exists($path)) {
            return false;
        }

        $body = trim((string) Storage::disk('local')->get($path));
        if ($body === '') {
            return false;
        }

        $reservation = Reservation::find($reservationId);
        if (! $reservation) {
            return false;
        }

        $reservation->comments()->create([
            'sender_type' => ReservationComment::SENDER_USER,
            'sender_id' => $reservation->user_id,
            'body' => $body,
        ]);

        return true;
    }
}
