<?php

namespace App\Filament\Resources\SlotResource\Pages;

use App\Filament\Resources\SlotResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSlot extends CreateRecord
{
    protected static string $resource = SlotResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['is_reserved'] = false;

        return $data;
    }

    protected function fillForm(): void
    {
        parent::fillForm();

        $menuId = request()->integer('menu_id');

        if ($menuId > 0) {
            $this->form->fill([
                'menu_id' => $menuId,
                'capacity' => 1,
            ]);
        }
    }
}
