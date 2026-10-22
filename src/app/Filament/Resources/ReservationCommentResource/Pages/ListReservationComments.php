<?php

namespace App\Filament\Resources\ReservationCommentResource\Pages;

use App\Filament\Resources\ReservationCommentResource;
use App\Models\Reservation;
use App\Models\ReservationComment;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListReservationComments extends ListRecords
{
    protected static string $resource = ReservationCommentResource::class;

    public function deleteAdminComment(int $commentId): void
    {
        $comment = ReservationComment::query()
            ->where('sender_type', ReservationComment::SENDER_ADMIN)
            ->whereNull('deleted_by_admin_at')
            ->findOrFail($commentId);

        $comment->update(['deleted_by_admin_at' => now()]);

        Notification::make()->title('メッセージを削除しました')->success()->send();
    }

    public function getTabs(): array
    {
        $resource = static::getResource();

        // NEW は予約確定のみ。未読は終了済みの予約も含み、未読がない終了済みの予約は終了、それ以外は既読とする
        $unread = fn (Builder $query): Builder => $resource::unreadQuery($query)
            ->whereNotIn('reservations.id', $resource::newQuery(Reservation::query())->select('reservations.id'));

        return [
            'new' => Tab::make('NEW')
                ->icon('heroicon-o-sparkles')
                ->badge(fn () => $resource::newQuery(Reservation::query())->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $resource::newQuery($query)),
            'unread' => Tab::make('未読')
                ->icon('heroicon-o-envelope')
                ->badge(fn () => $unread(Reservation::query())->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $unread($query)),
            'ended' => Tab::make('終了')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $resource::endedQuery($query)),
            'read' => Tab::make('既読')
                ->icon('heroicon-o-envelope-open')
                ->modifyQueryUsing(fn (Builder $query) => $resource::readQuery($query)),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'new';
    }
}
