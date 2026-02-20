<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuResource\Pages;
use App\Models\Menu;
use Filament\Forms;
use Filament\Forms\Form;
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
                    ->required(),
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
