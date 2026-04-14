<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use App\Models\Reservation;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListReservations extends ListRecords
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('calendar')
                ->label('カレンダーで管理')
                ->icon('heroicon-m-calendar-days')
                ->url(ReservationResource::getUrl('calendar')),
        ];
    }

    public function getTabs(): array
    {
        $today = now('Asia/Tokyo')->toDateString();

        return [
            'active' => Tab::make('予約中')
                ->badge($this->applyActiveScope(Reservation::query(), $today)->count())
                ->modifyQueryUsing(fn (Builder $query) => $this->applyActiveScope($query, $today)),
            'ended' => Tab::make('終了')
                ->badge($this->applyEndedScope(Reservation::query(), $today)->count())
                ->modifyQueryUsing(fn (Builder $query) => $this->applyEndedScope($query, $today)),
        ];
    }

    protected function applyActiveScope(Builder $query, string $today): Builder
    {
        return $query
            ->where('status', '!=', 'completed')
            ->where(function (Builder $subQuery) use ($today): void {
                $subQuery
                    ->whereDate('date', '>=', $today)
                    ->orWhereHas('slot', fn (Builder $slotQuery) => $slotQuery->whereDate('date', '>=', $today));
            });
    }

    protected function applyEndedScope(Builder $query, string $today): Builder
    {
        return $query
            ->where(function (Builder $subQuery) use ($today): void {
                $subQuery
                    ->where('status', 'completed')
                    ->orWhereDate('date', '<', $today)
                    ->orWhereHas('slot', fn (Builder $slotQuery) => $slotQuery->whereDate('date', '<', $today));
            });
    }
}
