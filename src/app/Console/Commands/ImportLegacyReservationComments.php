<?php

namespace App\Console\Commands;

use App\Services\ReservationCommentService;
use Illuminate\Console\Command;

class ImportLegacyReservationComments extends Command
{
    protected $signature = 'reservation-comments:import-legacy';

    protected $description = 'ローカルストレージの既存予約コメントをデータベースへ移行します';

    public function handle(ReservationCommentService $commentService): int
    {
        $importedCount = $commentService->importLegacyComments();

        $this->info("{$importedCount}件の予約コメントを移行しました。");

        return self::SUCCESS;
    }
}
