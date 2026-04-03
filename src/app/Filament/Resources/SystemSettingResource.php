<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SystemSettingResource\Pages;
use App\Models\SystemSetting;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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
                Section::make('ウェルカムページ設定')
                    ->description('トップページ本文を編集できます。下のプレビューで確認して保存してください。')
                    ->schema([
                        Forms\Components\TextInput::make('welcome_badge')
                            ->label('バッジテキスト')
                            ->maxLength(50)
                            ->placeholder('sorairo_note')
                            ->live(onBlur: true),
                        Forms\Components\TextInput::make('welcome_title')
                            ->label('メイン見出し')
                            ->required()
                            ->maxLength(120)
                            ->live(onBlur: true),
                        Forms\Components\TextInput::make('welcome_subtitle')
                            ->label('サブ見出し')
                            ->maxLength(120)
                            ->live(onBlur: true),
                        Forms\Components\Textarea::make('welcome_lead')
                            ->label('リード文')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->live(onBlur: true),
                        Forms\Components\FileUpload::make('welcome_main_image_path')
                            ->label('メイン画像')
                            ->image()
                            ->disk('public')
                            ->directory('welcome')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(3072)
                            ->columnSpanFull()
                            ->live(onBlur: true),
                        Forms\Components\Repeater::make('welcome_body_blocks')
                            ->label('本文セクション（文章・画像）')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('見出し')
                                    ->required()
                                    ->maxLength(120)
                                    ->live(onBlur: true),
                                Forms\Components\Textarea::make('text')
                                    ->label('本文')
                                    ->rows(4)
                                    ->required()
                                    ->maxLength(2000)
                                    ->columnSpanFull()
                                    ->live(onBlur: true),
                                Forms\Components\FileUpload::make('image_path')
                                    ->label('画像')
                                    ->image()
                                    ->disk('public')
                                    ->directory('welcome/blocks')
                                    ->visibility('public')
                                    ->imageEditor()
                                    ->maxSize(3072)
                                    ->columnSpanFull()
                                    ->live(onBlur: true),
                            ])
                            ->defaultItems(1)
                            ->reorderable()
                            ->collapsible()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('welcome_shop_title')
                            ->label('店舗情報タイトル')
                            ->maxLength(120)
                            ->placeholder('店舗情報')
                            ->live(onBlur: true),
                        Forms\Components\Textarea::make('welcome_shop_description')
                            ->label('店舗紹介文')
                            ->rows(3)
                            ->maxLength(500)
                            ->live(onBlur: true),
                        Forms\Components\TextInput::make('welcome_business_hours')
                            ->label('営業時間')
                            ->maxLength(120)
                            ->placeholder('10:00〜20:00')
                            ->live(onBlur: true),
                        Forms\Components\TextInput::make('welcome_regular_holiday')
                            ->label('定休日')
                            ->maxLength(120)
                            ->placeholder('不定休')
                            ->live(onBlur: true),
                        Forms\Components\Textarea::make('welcome_business_note')
                            ->label('営業補足')
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->live(onBlur: true),
                        Forms\Components\TextInput::make('welcome_instagram_url')
                            ->label('Instagramリンク')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://www.instagram.com/your_account')
                            ->columnSpanFull()
                            ->live(onBlur: true),
                        Forms\Components\Placeholder::make('welcome_preview')
                            ->label('プレビュー')
                            ->columnSpanFull()
                            ->content(function (Get $get): HtmlString {
                                return new HtmlString(
                                    view('filament.resources.system-setting-resource.partials.welcome-preview', [
                                        'hero_badge' => $get('welcome_badge'),
                                        'hero_title' => $get('welcome_title'),
                                        'hero_subtitle' => $get('welcome_subtitle'),
                                        'hero_lead' => $get('welcome_lead'),
                                        'hero_image' => self::resolveImageUrl($get('welcome_main_image_path')),
                                        'body_blocks' => collect($get('welcome_body_blocks') ?? [])
                                            ->map(fn (array $block) => [
                                                'title' => $block['title'] ?? null,
                                                'text' => $block['text'] ?? null,
                                                'image' => self::resolveImageUrl($block['image_path'] ?? null),
                                            ])
                                            ->values()
                                            ->toArray(),
                                        'shop_title' => $get('welcome_shop_title'),
                                        'shop_description' => $get('welcome_shop_description'),
                                        'shop_hours' => $get('welcome_business_hours'),
                                        'shop_holiday' => $get('welcome_regular_holiday'),
                                        'shop_note' => $get('welcome_business_note'),
                                        'instagram_url' => $get('welcome_instagram_url'),
                                    ])->render()
                                );
                            }),
                        Forms\Components\Checkbox::make('welcome_preview_confirmed')
                            ->label('プレビューで内容を確認しました')
                            ->required()
                            ->accepted()
                            ->dehydrated(false)
                            ->columnSpanFull(),
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
            'index' => Pages\EditSystemSetting::route('/'),
        ];
    }

    public static function getNavigationUrl(): string
    {
        return static::getUrl('index');
    }

    /**
     * Resolve image URL from string path or TemporaryUploadedFile.
     */
    private static function resolveImageUrl(mixed $image): ?string
    {
        if (is_null($image)) {
            return null;
        }

        if ($image instanceof TemporaryUploadedFile) {
            try {
                return $image->temporaryUrl();
            } catch (\Exception) {
                return null;
            }
        }

        if (is_string($image)) {
            return Storage::disk('public')->url($image);
        }

        return null;
    }
}
