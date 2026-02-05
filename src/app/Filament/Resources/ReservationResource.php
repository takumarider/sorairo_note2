<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationResource\Pages;
use App\Models\Reservation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = '予約';

    protected static ?string $modelLabel = '予約';

    protected static ?string $pluralModelLabel = '予約';

    public static function getNavigationBadge(): ?string
    {
        return (string) Reservation::query()->where('status', 'confirmed')->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('ユーザー')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('menu_id')
                    ->label('メニュー')
                    ->relationship('menu', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('slot_id')
                    ->label('時間枠')
                    ->relationship('slot', 'id')
                    ->searchable()
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('ユーザー')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('menu.name')
                    ->label('メニュー')
                    ->searchable()
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
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('ステータス')
                    ->options([
                        'confirmed' => '確定',
                        'canceled' => 'キャンセル',
                        'completed' => '完了',
                    ]),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('ユーザー')
                    ->relationship('user', 'name'),
                Tables\Filters\SelectFilter::make('menu_id')
                    ->label('メニュー')
                    ->relationship('menu', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('cancel')
                    ->label('キャンセル')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Reservation $record) => $record->status === 'confirmed')
                    ->action(function (Reservation $record): void {
                        $record->cancel();

                        Notification::make()
                            ->title('予約をキャンセルしました。')
                            ->success()
                            ->send();
                    }),
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
            'index' => Pages\ListReservations::route('/'),
            'create' => Pages\CreateReservation::route('/create'),
            'edit' => Pages\EditReservation::route('/{record}/edit'),
        ];
    }
}
