<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SlotHistoryResource\Pages;
use App\Models\Slot;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SlotHistoryResource extends Resource
{
    protected static ?string $model = Slot::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = '時間枠履歴';

    protected static ?string $modelLabel = '時間枠履歴';

    protected static ?string $pluralModelLabel = '時間枠履歴';

    protected static ?int $navigationSort = 31;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereDate('date', '<', Carbon::today()->toDateString());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('menu.name')
                    ->label('メニュー')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('日付')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('開始時間')
                    ->time('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->label('終了時間')
                    ->time('H:i')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('is_reserved')
                    ->label('予約状況')
                    ->formatStateUsing(fn (bool $state): string => $state ? '予約済み' : '空き')
                    ->colors([
                        'danger' => true,
                        'success' => false,
                    ]),
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
                Tables\Filters\SelectFilter::make('menu_id')
                    ->label('メニュー')
                    ->relationship('menu', 'name'),
                Tables\Filters\TernaryFilter::make('is_reserved')
                    ->label('予約状況')
                    ->trueLabel('予約済み')
                    ->falseLabel('空き')
                    ->nullable(),
                Tables\Filters\Filter::make('date_range')
                    ->label('日付範囲')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')->label('開始日'),
                        Forms\Components\DatePicker::make('date_until')->label('終了日'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['date_from'] ?? null,
                                fn ($q, $date) => $q->whereDate('date', '>=', $date)
                            )
                            ->when(
                                $data['date_until'] ?? null,
                                fn ($q, $date) => $q->whereDate('date', '<=', $date)
                            );
                    }),
            ])
            ->actions([
                DeleteAction::make(),
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
            'index' => Pages\ListSlotHistories::route('/'),
        ];
    }
}
