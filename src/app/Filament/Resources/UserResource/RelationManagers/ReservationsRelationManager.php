<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\Reservation;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ReservationsRelationManager extends RelationManager
{
    protected static string $relationship = 'reservations';

    protected static ?string $title = '予約';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('menu_id')
                    ->label('メニュー')
                    ->relationship('menu', 'name')
                    ->required(),
                Forms\Components\Select::make('slot_id')
                    ->label('時間枠')
                    ->relationship('slot', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => sprintf('%s %s-%s', $record->date?->format('Y/m/d'), $record->start_time, $record->end_time))
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('ステータス')
                    ->options([
                        'confirmed' => '確定',
                        'canceled' => 'キャンセル',
                        'completed' => '完了',
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('menu.name')
                    ->label('メニュー')
                    ->sortable(),
                Tables\Columns\TextColumn::make('slot.date')
                    ->label('日付')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slot.start_time')
                    ->label('開始時間')
                    ->time('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('slot.end_time')
                    ->label('終了時間')
                    ->time('H:i')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('ステータス')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'confirmed' => '確定',
                        'canceled' => 'キャンセル',
                        'completed' => '完了',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'confirmed',
                        'warning' => 'completed',
                        'danger' => 'canceled',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('作成日')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }
}
