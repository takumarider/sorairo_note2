<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NoteResource\Pages;
use App\Models\Note;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class NoteResource extends Resource
{
    protected static ?string $model = Note::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'お知らせ';

    protected static ?string $modelLabel = 'お知らせ';

    protected static ?string $pluralModelLabel = 'お知らせ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('お知らせ内容')->schema([
                    TextInput::make('title')
                        ->label('タイトル')
                        ->required()
                        ->maxLength(100),
                    Textarea::make('content')
                        ->label('本文')
                        ->rows(6)
                        ->required(),
                    FileUpload::make('image_path')
                        ->label('画像')
                        ->image()
                        ->imageEditor()
                        ->directory('notes')
                        ->visibility('public')
                        ->maxSize(2048)
                        ->disk('render'),
                ])->columns(1),
                Section::make('公開設定')->schema([
                    Toggle::make('is_published')
                        ->label('公開する')
                        ->default(true),
                    DateTimePicker::make('published_at')
                        ->label('公開日時')
                        ->default(now()),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('タイトル')
                    ->sortable()
                    ->searchable(),
                IconColumn::make('is_published')
                    ->label('公開')
                    ->boolean(),
                TextColumn::make('published_at')
                    ->label('公開日時')
                    ->dateTime('Y/m/d H:i')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('作成')
                    ->dateTime('Y/m/d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_published')
                    ->label('公開状態'),
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
            'index' => Pages\ListNotes::route('/'),
            'create' => Pages\CreateNote::route('/create'),
            'edit' => Pages\EditNote::route('/{record}/edit'),
        ];
    }
}
