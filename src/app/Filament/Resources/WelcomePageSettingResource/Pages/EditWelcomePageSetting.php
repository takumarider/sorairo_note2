<?php

namespace App\Filament\Resources\WelcomePageSettingResource\Pages;

use App\Filament\Resources\WelcomePageSettingResource;
use App\Models\SystemSetting;
use Filament\Actions;
use Filament\Actions\StaticAction;
use Filament\Resources\Pages\EditRecord;

class EditWelcomePageSetting extends EditRecord
{
    protected static string $resource = WelcomePageSettingResource::class;

    public function mount(int|string|null $record = null): void
    {
        $this->record = SystemSetting::getSingleton();

        $this->authorizeAccess();
        $this->fillForm();
        $this->previousUrl = url()->previous();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('確認')
                ->icon('heroicon-m-eye')
                ->color('gray')
                ->modalHeading('ウェルカムページプレビュー')
                ->modalDescription('現在の入力内容を保存前に確認できます。')
                ->modalWidth('5xl')
                ->modalSubmitAction(false)
                ->modalCancelAction(fn (StaticAction $action) => $action->label('閉じる'))
                ->modalContent(fn () => view(
                    'filament.resources.welcome-page-setting-resource.partials.welcome-preview',
                    WelcomePageSettingResource::buildPreviewData((array) $this->form->getRawState()),
                )),
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        return null;
    }
}