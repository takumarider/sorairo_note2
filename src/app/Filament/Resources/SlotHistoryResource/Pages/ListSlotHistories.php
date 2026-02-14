<?php

namespace App\Filament\Resources\SlotHistoryResource\Pages;

use App\Filament\Resources\SlotHistoryResource;
use App\Filament\Resources\SlotResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSlotHistories extends ListRecords
{
    protected static string $resource = SlotHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('current_slots')
                ->label('時間枠管理へ戻る')
                ->icon('heroicon-m-arrow-left')
                ->url(SlotResource::getUrl()),
        ];
    }
}
