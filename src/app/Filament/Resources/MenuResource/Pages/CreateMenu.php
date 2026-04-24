<?php

namespace App\Filament\Resources\MenuResource\Pages;

use App\Filament\Resources\MenuResource;
use App\Filament\Resources\SlotResource;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateMenu extends CreateRecord
{
    protected static string $resource = MenuResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['is_event'] ?? false) === true) {
            $data['duration'] = 0;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        if (! ($this->record->is_event ?? false)) {
            return;
        }

        Notification::make()
            ->title('イベントメニューを作成しました。続けてイベント枠を登録してください。')
            ->success()
            ->persistent()
            ->actions([
                Action::make('create-slot')
                    ->label('イベント枠を追加')
                    ->button()
                    ->url(SlotResource::getUrl('create', ['menu_id' => $this->record->id])),
            ])
            ->send();
    }
}
