<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\SystemSettingResource;
use App\Models\SystemSetting;
use Filament\Widgets\Widget;

class SystemSettingsAlert extends Widget
{
    protected static string $view = 'filament.widgets.system-settings-alert';

    protected int|string|array $columnSpan = 'full';

    public function shouldRender(): bool
    {
        $settings = SystemSetting::first();

        return ! ($settings && $settings->hasAdminNotificationSettings());
    }

    protected function getViewData(): array
    {
        return [
            'settingsUrl' => SystemSettingResource::getUrl('index'),
        ];
    }
}
