<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MailTemplateSettingResource\Pages;
use App\Models\SystemSetting;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class MailTemplateSettingResource extends Resource
{
    protected static ?string $model = SystemSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'システム設定';

    protected static ?string $navigationLabel = 'メール文章作成';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'メール文章作成';

    protected static ?string $pluralModelLabel = 'メール文章作成';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('差し込み変数')
                    ->description('本文・件名で {{variable}} 形式を使用できます。使える変数: {{user_name}}, {{user_email}}, {{reservation_id}}, {{reservation_status}}, {{reservation_date}}, {{reservation_start_time}}, {{reservation_end_time}}, {{menu_name}}, {{menu_price}}, {{menu_duration}}, {{event_type}}, {{mypage_url}}, {{new_reservation_url}}, {{admin_reservation_url}}, {{app_name}}')
                    ->schema([]),
                Section::make('予約確定メール（ユーザー向け）')
                    ->schema([
                        Forms\Components\TextInput::make('mail_user_confirmed_subject')
                            ->label('件名')
                            ->maxLength(255)
                            ->helperText('未入力の場合は既定の件名を使用します。'),
                        Forms\Components\MarkdownEditor::make('mail_user_confirmed_body')
                            ->label('本文（Markdown）')
                            ->helperText('未入力の場合は既定の本文を使用します。')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
                Section::make('予約キャンセルメール（ユーザー向け）')
                    ->schema([
                        Forms\Components\TextInput::make('mail_user_canceled_subject')
                            ->label('件名')
                            ->maxLength(255)
                            ->helperText('未入力の場合は既定の件名を使用します。'),
                        Forms\Components\MarkdownEditor::make('mail_user_canceled_body')
                            ->label('本文（Markdown）')
                            ->helperText('未入力の場合は既定の本文を使用します。')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
                Section::make('管理者通知メール（新規予約）')
                    ->schema([
                        Forms\Components\TextInput::make('mail_admin_confirmed_subject')
                            ->label('件名')
                            ->maxLength(255)
                            ->helperText('未入力の場合は既定の件名を使用します。'),
                        Forms\Components\MarkdownEditor::make('mail_admin_confirmed_body')
                            ->label('本文（Markdown）')
                            ->helperText('未入力の場合は既定の本文を使用します。')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
                Section::make('管理者通知メール（キャンセル）')
                    ->schema([
                        Forms\Components\TextInput::make('mail_admin_canceled_subject')
                            ->label('件名')
                            ->maxLength(255)
                            ->helperText('未入力の場合は既定の件名を使用します。'),
                        Forms\Components\MarkdownEditor::make('mail_admin_canceled_body')
                            ->label('本文（Markdown）')
                            ->helperText('未入力の場合は既定の本文を使用します。')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
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
            'index' => Pages\EditMailTemplateSetting::route('/'),
        ];
    }

    public static function getNavigationUrl(): string
    {
        return static::getUrl('index');
    }
}
