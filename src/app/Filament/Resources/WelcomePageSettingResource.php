<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WelcomePageSettingResource\Pages;
use App\Models\SystemSetting;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class WelcomePageSettingResource extends Resource
{
    protected static ?string $model = SystemSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $navigationGroup = 'システム設定';

    protected static ?string $navigationLabel = 'ウェルカムページ設定';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'ウェルカムページ設定';

    protected static ?string $pluralModelLabel = 'ウェルカムページ設定';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('ウェルカムページ設定')
                    ->description('トップページに表示する内容を編集します。内容の確認は右上の確認ボタンから行えます。')
                    ->schema(static::getWelcomeFields())
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
            'index' => Pages\EditWelcomePageSetting::route('/'),
        ];
    }

    public static function getNavigationUrl(): string
    {
        return static::getUrl('index');
    }

    public static function getWelcomeFields(): array
    {
        return [
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
                ->live(),
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
                        ->live(),
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
        ];
    }

    public static function buildPreviewData(array $state): array
    {
        return [
            'hero_badge' => $state['welcome_badge'] ?? null,
            'hero_title' => $state['welcome_title'] ?? null,
            'hero_subtitle' => $state['welcome_subtitle'] ?? null,
            'hero_lead' => $state['welcome_lead'] ?? null,
            'hero_image' => static::resolveImageUrl($state['welcome_main_image_path'] ?? null),
            'body_blocks' => collect($state['welcome_body_blocks'] ?? [])
                ->map(fn (array $block) => [
                    'title' => $block['title'] ?? null,
                    'text' => $block['text'] ?? null,
                    'image' => static::resolveImageUrl($block['image_path'] ?? null),
                ])
                ->values()
                ->toArray(),
            'shop_title' => $state['welcome_shop_title'] ?? null,
            'shop_description' => $state['welcome_shop_description'] ?? null,
            'shop_hours' => $state['welcome_business_hours'] ?? null,
            'shop_holiday' => $state['welcome_regular_holiday'] ?? null,
            'shop_note' => $state['welcome_business_note'] ?? null,
            'instagram_url' => $state['welcome_instagram_url'] ?? null,
        ];
    }

    public static function resolveImageUrl(mixed $image): ?string
    {
        if (is_null($image)) {
            return null;
        }

        if (is_array($image)) {
            if (isset($image['path'])) {
                return Storage::url($image['path']);
            }

            if (isset($image[0])) {
                return static::resolveImageUrl($image[0]);
            }

            // FileUpload may store values as [uuid => path|TemporaryUploadedFile].
            $first = collect($image)->first();

            if (filled($first)) {
                return static::resolveImageUrl($first);
            }

            return null;
        }

        if ($image instanceof TemporaryUploadedFile) {
            try {
                return $image->temporaryUrl();
            } catch (\Exception) {
                try {
                    return route('livewire.preview-file', [
                        'filename' => $image->getFilename(),
                    ]);
                } catch (\Exception) {
                    return null;
                }
            }
        }

        if (is_string($image) && filled($image)) {
            if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
                return $image;
            }

            return Storage::url($image);
        }

        return null;
    }
}
