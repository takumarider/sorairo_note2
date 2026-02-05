<?php

namespace App\Filament\Resources\SlotResource\Pages;

use App\Filament\Resources\SlotResource;
use App\Models\Slot;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSlot extends EditRecord
{
    protected static string $resource = SlotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function (Actions\DeleteAction $action, Slot $record): void {
                    if ($record->is_reserved) {
                        Notification::make()
                            ->title('予約済みの時間枠は削除できません。')
                            ->danger()
                            ->send();
                        $action->halt();
                    }
                }),
        ];
    }
}
