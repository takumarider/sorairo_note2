<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusinessHourResource\Pages;
use App\Models\BusinessHour;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BusinessHourResource extends Resource
{
    protected static ?string $model = BusinessHour::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = '営業時間';

    protected static ?string $modelLabel = '営業時間';

    protected static ?string $pluralModelLabel = '営業時間';

    protected static ?string $navigationGroup = '設定';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('営業時間設定')
                    ->schema([
                        Forms\Components\Placeholder::make('usage_guide')
                            ->label('入力ルール')
                            ->content('曜日は毎週の基本設定、特定日はその日だけの上書きです。休業日にする場合も対象の曜日または特定日を作成して設定してください。')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('day_of_week')
                            ->label('曜日')
                            ->options([
                                0 => '日曜日',
                                1 => '月曜日',
                                2 => '火曜日',
                                3 => '水曜日',
                                4 => '木曜日',
                                5 => '金曜日',
                                6 => '土曜日',
                            ])
                            ->nullable()
                            ->helperText('曜日または特定日のいずれかを設定してください。曜日を設定すると毎週その曜日に適用されます。'),

                        Forms\Components\DatePicker::make('specific_date')
                            ->label('特定日')
                            ->nullable()
                            ->helperText('特定の日付を設定すると、この日付の営業時間が曜日の設定を上書きします。'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('営業時間')
                    ->schema([
                        Forms\Components\TimePicker::make('open_time')
                            ->label('営業開始時間')
                            ->required()
                            ->seconds(false),

                        Forms\Components\TimePicker::make('close_time')
                            ->label('営業終了時間')
                            ->required()
                            ->seconds(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('ステータス')
                    ->schema([
                        Forms\Components\Toggle::make('is_closed')
                            ->label('休業日')
                            ->default(false)
                            ->helperText('有効にするとこの日付/曜日は営業していません。'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('day_of_week')
                    ->label('曜日')
                    ->formatStateUsing(fn (?int $state): string => match ($state) {
                        0 => '日曜日',
                        1 => '月曜日',
                        2 => '火曜日',
                        3 => '水曜日',
                        4 => '木曜日',
                        5 => '金曜日',
                        6 => '土曜日',
                        null => '（なし）',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('specific_date')
                    ->label('特定日')
                    ->date('Y年m月d日')
                    ->sortable(),

                Tables\Columns\TextColumn::make('open_time')
                    ->label('開始時間')
                    ->time('H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('close_time')
                    ->label('終了時間')
                    ->time('H:i')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_closed')
                    ->label('休業日')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('作成日')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_closed')
                    ->label('休業日')
                    ->nullable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBusinessHours::route('/'),
            'create' => Pages\CreateBusinessHour::route('/create'),
            'edit' => Pages\EditBusinessHour::route('/{record}/edit'),
        ];
    }
}
