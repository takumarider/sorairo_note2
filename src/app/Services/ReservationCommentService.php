<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ReservationCommentService
{
    private const DIRECTORY = 'reservation_comments';

    /**
     * 指定された予約のコメントを取得します。
     */
    public function getComment(int $reservationId): ?string
    {
        $path = $this->getFilePath($reservationId);

        if (! Storage::disk('local')->exists($path)) {
            return null;
        }

        $content = Storage::disk('local')->get($path);
        $trimmed = trim((string) $content);

        return $trimmed !== '' ? $trimmed : null;
    }

    /**
     * 指定された予約のコメントを保存または削除します。
     */
    public function saveComment(int $reservationId, ?string $comment): void
    {
        $path = $this->getFilePath($reservationId);
        $trimmed = trim((string) $comment);

        if ($trimmed === '') {
            $this->deleteComment($reservationId);

            return;
        }

        Storage::disk('local')->put($path, $trimmed);
    }

    /**
     * 指定された予約のコメントを削除します。
     */
    public function deleteComment(int $reservationId): void
    {
        $path = $this->getFilePath($reservationId);

        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
    }

    private function getFilePath(int $reservationId): string
    {
        return self::DIRECTORY.'/'.$reservationId.'.txt';
    }
}
