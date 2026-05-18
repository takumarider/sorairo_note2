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
use Illuminate\Support\HtmlString;
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
            Forms\Components\Placeholder::make('welcome_mapping_guide')
                ->label('設定項目と反映箇所')
                ->content(new HtmlString('① ヒーロー: バッジ/見出し/リード文/メイン画像<br>② 本文セクション: 「本文セクション（文章・画像）」の各ブロック<br>③ 店舗情報カード: タイトル/紹介文/営業時間/補足<br>④ 全体デザイン: 背景テーマ・アクセントカラー'))
                ->columnSpanFull(),
            Forms\Components\TextInput::make('welcome_badge')
                ->label('バッジテキスト')
                ->maxLength(50)
                ->placeholder('sorairo_note')
                ->helperText('ヒーロー上部の小さなラベルに反映されます。')
                ->live(onBlur: true),
            Forms\Components\TextInput::make('welcome_title')
                ->label('メイン見出し')
                ->required()
                ->maxLength(120)
                ->helperText('ヒーロー中央の最も大きな見出しに反映されます。')
                ->live(onBlur: true),
            Forms\Components\TextInput::make('welcome_subtitle')
                ->label('サブ見出し')
                ->maxLength(120)
                ->helperText('メイン見出しの直下に反映されます。')
                ->live(onBlur: true),
            Forms\Components\Textarea::make('welcome_lead')
                ->label('リード文')
                ->rows(3)
                ->maxLength(500)
                ->helperText('ヒーロー説明文に反映されます。')
                ->columnSpanFull()
                ->live(onBlur: true),
            Forms\Components\Select::make('welcome_theme_background')
                ->label('全体背景テーマ')
                ->options([
                    'sky' => '空色グラデーション',
                    'mint' => 'ミントグラデーション',
                    'sand' => 'サンドグラデーション',
                ])
                ->helperText('ページ全体の背景色に反映されます。')
                ->native(false),
            Forms\Components\Select::make('welcome_theme_accent')
                ->label('アクセントカラー')
                ->options([
                    'sky' => 'スカイ',
                    'emerald' => 'エメラルド',
                    'rose' => 'ローズ',
                ])
                ->helperText('バッジやInstagramボタンの色に反映されます。')
                ->native(false),
            Forms\Components\Select::make('welcome_hero_text_align')
                ->label('ヒーロー文字配置')
                ->options([
                    'left' => '左寄せ',
                    'center' => '中央寄せ',
                ])
                ->native(false),
            Forms\Components\Select::make('welcome_hero_title_size')
                ->label('見出しサイズ')
                ->options([
                    'md' => '標準',
                    'lg' => '大きめ',
                    'xl' => '大きい',
                ])
                ->native(false),
            Forms\Components\Select::make('welcome_hero_title_color')
                ->label('見出し色')
                ->options([
                    'slate' => '濃いグレー',
                    'sky' => 'スカイ',
                    'emerald' => 'エメラルド',
                ])
                ->native(false),
            Forms\Components\Select::make('welcome_hero_subtitle_size')
                ->label('サブ見出しサイズ')
                ->options([
                    'sm' => '控えめ',
                    'md' => '標準',
                    'lg' => '大きめ',
                ])
                ->native(false),
            Forms\Components\Select::make('welcome_hero_subtitle_color')
                ->label('サブ見出し色')
                ->options([
                    'sky' => 'スカイ',
                    'emerald' => 'エメラルド',
                    'rose' => 'ローズ',
                ])
                ->native(false),
            Forms\Components\Select::make('welcome_hero_lead_size')
                ->label('リード文サイズ')
                ->options([
                    'sm' => '控えめ',
                    'md' => '標準',
                    'lg' => '大きめ',
                ])
                ->native(false),
            Forms\Components\Select::make('welcome_hero_lead_color')
                ->label('リード文色')
                ->options([
                    'slate' => 'グレー',
                    'sky' => 'スカイ',
                    'emerald' => 'エメラルド',
                ])
                ->native(false),
            Forms\Components\Select::make('welcome_hero_lead_paragraph_mode')
                ->label('リード文の改行表示')
                ->options([
                    'line' => 'そのまま改行',
                    'paragraph' => '段落表示',
                ])
                ->native(false)
                ->helperText('段落表示は空行ごとに段落として表示します。'),
            Forms\Components\FileUpload::make('welcome_main_image_path')
                ->label('メイン画像')
                ->image()
                ->disk('public')
                ->directory('welcome')
                ->visibility('public')
                ->imageEditor()
                ->maxSize(3072)
                ->helperText('ヒーロー右側の画像に反映されます。')
                ->columnSpanFull()
                ->live(),
            Forms\Components\Repeater::make('welcome_body_blocks')
                ->label('本文セクション（文章・画像）')
                ->helperText('お店のご案内エリアに反映されます。1件ならカード、複数ならスライド表示です。')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('見出し')
                        ->required()
                        ->maxLength(120)
                        ->live(onBlur: true),
                    Forms\Components\Select::make('title_size')
                        ->label('見出しサイズ')
                        ->options([
                            'sm' => '控えめ',
                            'md' => '標準',
                            'lg' => '大きめ',
                        ])
                        ->native(false),
                    Forms\Components\Select::make('title_color')
                        ->label('見出し色')
                        ->options([
                            'slate' => '濃いグレー',
                            'sky' => 'スカイ',
                            'emerald' => 'エメラルド',
                        ])
                        ->native(false),
                    Forms\Components\Textarea::make('text')
                        ->label('本文')
                        ->rows(4)
                        ->required()
                        ->maxLength(2000)
                        ->columnSpanFull()
                        ->live(onBlur: true),
                    Forms\Components\Select::make('text_size')
                        ->label('本文サイズ')
                        ->options([
                            'sm' => '控えめ',
                            'md' => '標準',
                            'lg' => '大きめ',
                        ])
                        ->native(false),
                    Forms\Components\Select::make('text_color')
                        ->label('本文色')
                        ->options([
                            'slate' => 'グレー',
                            'sky' => 'スカイ',
                            'emerald' => 'エメラルド',
                        ])
                        ->native(false),
                    Forms\Components\Select::make('text_align')
                        ->label('本文配置')
                        ->options([
                            'left' => '左寄せ',
                            'center' => '中央寄せ',
                        ])
                        ->native(false),
                    Forms\Components\Select::make('paragraph_mode')
                        ->label('本文改行表示')
                        ->options([
                            'line' => 'そのまま改行',
                            'paragraph' => '段落表示',
                            'inherit' => '全体設定に合わせる',
                        ])
                        ->native(false),
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
                ->helperText('右側の店舗情報カード見出しに反映されます。')
                ->live(onBlur: true),
            Forms\Components\Select::make('welcome_shop_title_size')
                ->label('店舗情報タイトルサイズ')
                ->options([
                    'sm' => '控えめ',
                    'md' => '標準',
                    'lg' => '大きめ',
                ])
                ->native(false),
            Forms\Components\Select::make('welcome_shop_title_color')
                ->label('店舗情報タイトル色')
                ->options([
                    'slate' => '濃いグレー',
                    'sky' => 'スカイ',
                    'emerald' => 'エメラルド',
                ])
                ->native(false),
            Forms\Components\Textarea::make('welcome_shop_description')
                ->label('店舗紹介文')
                ->rows(3)
                ->maxLength(500)
                ->helperText('店舗情報カード本文に反映されます。')
                ->live(onBlur: true),
            Forms\Components\Textarea::make('welcome_business_hours')
                ->label('営業時間（平日・土曜は改行して入力）')
                ->rows(2)
                ->maxLength(120)
                ->placeholder("平日 10:00〜20:00\n土曜 10:00〜18:00")
                ->helperText('1行目に平日、2行目に土曜の営業時間を入力してください。')
                ->live(onBlur: true),
            Forms\Components\TextInput::make('welcome_regular_holiday')
                ->label('定休日')
                ->maxLength(120)
                ->placeholder('不定休')
                ->live(onBlur: true),
            Forms\Components\TextInput::make('welcome_contact_number')
                ->label('お問い合わせ番号')
                ->tel()
                ->maxLength(50)
                ->placeholder('03-1234-5678')
                ->live(onBlur: true),
            Forms\Components\Textarea::make('welcome_business_note')
                ->label('営業補足')
                ->rows(2)
                ->maxLength(500)
                ->columnSpanFull()
                ->live(onBlur: true),
            Forms\Components\Select::make('welcome_shop_body_size')
                ->label('店舗情報本文サイズ')
                ->options([
                    'sm' => '控えめ',
                    'md' => '標準',
                    'lg' => '大きめ',
                ])
                ->native(false),
            Forms\Components\Select::make('welcome_shop_body_color')
                ->label('店舗情報本文色')
                ->options([
                    'slate' => 'グレー',
                    'sky' => 'スカイ',
                    'emerald' => 'エメラルド',
                ])
                ->native(false),
            Forms\Components\Select::make('welcome_shop_paragraph_mode')
                ->label('店舗情報の改行表示')
                ->options([
                    'line' => 'そのまま改行',
                    'paragraph' => '段落表示',
                ])
                ->native(false),
            Forms\Components\TextInput::make('welcome_instagram_url')
                ->label('Instagramリンク')
                ->url()
                ->rule('starts_with:http://,https://')
                ->maxLength(255)
                ->placeholder('https://www.instagram.com/your_account')
                ->helperText('ヘッダーのInstagramボタンに反映されます。http:// または https:// で始まるURLを入力してください。')
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
                    'title_size' => $block['title_size'] ?? null,
                    'title_color' => $block['title_color'] ?? null,
                    'text_size' => $block['text_size'] ?? null,
                    'text_color' => $block['text_color'] ?? null,
                    'text_align' => $block['text_align'] ?? null,
                    'paragraph_mode' => $block['paragraph_mode'] ?? null,
                ])
                ->values()
                ->toArray(),
            'shop_title' => $state['welcome_shop_title'] ?? null,
            'shop_description' => $state['welcome_shop_description'] ?? null,
            'shop_hours' => $state['welcome_business_hours'] ?? null,
            'shop_holiday' => $state['welcome_regular_holiday'] ?? null,
            'shop_contact_number' => $state['welcome_contact_number'] ?? null,
            'shop_note' => $state['welcome_business_note'] ?? null,
            'instagram_url' => $state['welcome_instagram_url'] ?? null,
            'theme_background' => $state['welcome_theme_background'] ?? null,
            'theme_accent' => $state['welcome_theme_accent'] ?? null,
            'hero_title_size' => $state['welcome_hero_title_size'] ?? null,
            'hero_title_color' => $state['welcome_hero_title_color'] ?? null,
            'hero_subtitle_size' => $state['welcome_hero_subtitle_size'] ?? null,
            'hero_subtitle_color' => $state['welcome_hero_subtitle_color'] ?? null,
            'hero_lead_size' => $state['welcome_hero_lead_size'] ?? null,
            'hero_lead_color' => $state['welcome_hero_lead_color'] ?? null,
            'hero_text_align' => $state['welcome_hero_text_align'] ?? null,
            'hero_lead_paragraph_mode' => $state['welcome_hero_lead_paragraph_mode'] ?? null,
            'shop_title_size' => $state['welcome_shop_title_size'] ?? null,
            'shop_title_color' => $state['welcome_shop_title_color'] ?? null,
            'shop_body_size' => $state['welcome_shop_body_size'] ?? null,
            'shop_body_color' => $state['welcome_shop_body_color'] ?? null,
            'shop_paragraph_mode' => $state['welcome_shop_paragraph_mode'] ?? null,
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
