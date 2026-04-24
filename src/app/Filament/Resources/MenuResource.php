<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuResource\Pages;
use App\Models\Menu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'メニュー';

    protected static ?string $modelLabel = 'メニュー';

    protected static ?string $pluralModelLabel = 'メニュー';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('メニュー基本情報')
                    ->schema([
                        Forms\Components\Toggle::make('is_event')
                            ->label('イベントメニュー')
                            ->helperText('ON にすると通常の施術ではなく、定員付きのイベント枠として扱います。')
                            ->default(false)
                            ->afterStateUpdated(function (Set $set, bool $state): void {
                                if ($state) {
                                    $set('duration', 0);
                                }
                            })
                            ->live(),
                        Forms\Components\TextInput::make('name')
                            ->label('メニュー名')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label('説明')
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('price')
                            ->label('料金')
                            ->numeric()
                            ->suffix('円')
                            ->required(),
                        Forms\Components\TextInput::make('duration')
                            ->label('所要時間')
                            ->numeric()
                            ->suffix('分')
                            ->default(0)
                            ->minValue(0)
                            ->required(fn (Get $get): bool => ! (bool) $get('is_event'))
                            ->visible(fn (Get $get): bool => ! (bool) $get('is_event')),
                        Forms\Components\FileUpload::make('image_path')
                            ->label('画像')
                            ->image()
                            ->directory('menus')
                            ->disk('public')
                            ->imageEditor()
                            ->imagePreviewHeight('150')
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('有効')
                            ->default(true),
                        Forms\Components\Placeholder::make('event_slot_hint')
                            ->label('イベント枠の扱い')
                            ->content('イベントは時間枠管理で開始・終了時刻を設定します。所要時間やオプションは使用しません。')
                            ->visible(fn (Get $get): bool => (bool) $get('is_event'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('オプション')
                    ->hidden(fn (Get $get): bool => (bool) $get('is_event'))
                    ->schema([
                        Forms\Components\Repeater::make('options')
                            ->relationship('options')
                            ->label('メニューオプション')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('オプション名')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('price')
                                    ->label('追加料金')
                                    ->numeric()
                                    ->suffix('円')
                                    ->required()
                                    ->default(0),
                                Forms\Components\TextInput::make('duration')
                                    ->label('追加所要時間')
                                    ->numeric()
                                    ->suffix('分')
                                    ->required()
                                    ->default(0),
                                Forms\Components\FileUpload::make('image_path')
                                    ->label('オプション画像')
                                    ->image()
                                    ->directory('menu-options')
                                    ->disk('public')
                                    ->imageEditor()
                                    ->imagePreviewHeight('100')
                                    ->required(),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('有効')
                                    ->default(true),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('画像')
                    ->disk('public')
                    ->square()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('メニュー名')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_event')
                    ->label('イベント')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('料金')
                    ->money('JPY')
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration')
                    ->label('所要時間')
                    ->suffix('分')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('有効')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('作成日')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('更新日')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_event')
                    ->label('イベント'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('有効'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}
