<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SystemSettingResource\Pages;
use App\Models\SystemSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SystemSettingResource extends Resource
{
    protected static ?string $model = SystemSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'システム設定';

    protected static ?string $navigationLabel = '通知設定';

    protected static ?string $modelLabel = '通知設定';

    protected static ?string $pluralModelLabel = '通知設定';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('通知元設定（共通）')
                    ->schema([
                        Forms\Components\TextInput::make('notification_from_email')
                            ->label('通知元メールアドレス')
                            ->email()
                            ->required()
                            ->helperText('ユーザー通知・管理者通知の送信元アドレスです'),
                        Forms\Components\TextInput::make('notification_from_name')
                            ->label('通知元名')
                            ->required()
                            ->helperText('例: Sorairo Note'),
                    ])
                    ->columns(2),
                Section::make('管理者通知設定')
                    ->schema([
                        Forms\Components\TextInput::make('admin_notification_email')
                            ->label('管理者通知先メールアドレス')
                            ->email()
                            ->required()
                            ->helperText('予約・キャンセル通知を受け取るメールアドレスです'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([])
            ->filters([])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'edit' => Pages\EditSystemSetting::route('/'),
        ];
    }

    public static function getNavigationUrl(): string
    {
        return static::getUrl('edit');
    }
}
