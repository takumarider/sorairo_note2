<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SlotResource\Pages;
use App\Models\Menu;
use App\Models\Slot;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SlotResource extends Resource
{
    protected static ?string $model = Slot::class;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'イベント枠';

    protected static ?string $modelLabel = 'イベント枠';

    protected static ?string $pluralModelLabel = 'イベント枠';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('menu', fn (Builder $query) => $query->where('is_event', true))
            ->whereDate('date', '>=', Carbon::today()->toDateString());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('menu_id')
                    ->label('イベントメニュー')
                    ->options(fn () => Menu::query()->where('is_event', true)->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\DatePicker::make('date')
                    ->label('日付')
                    ->required(),
                Forms\Components\TimePicker::make('start_time')
                    ->label('開始時間')
                    ->required(),
                Forms\Components\TimePicker::make('end_time')
                    ->label('終了時間')
                    ->required(),
                Forms\Components\TextInput::make('capacity')
                    ->label('定員')
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->required()
                    ->helperText('イベント枠ごとの定員です。'),
                Forms\Components\Toggle::make('is_reserved')
                    ->label('予約済み')
                    ->default(false)
                    ->hidden()
                    ->dehydrated(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('menu.name')
                    ->label('イベントメニュー')
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
                Tables\Columns\TextColumn::make('capacity')
                    ->label('定員')
                    ->placeholder('-')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('is_reserved')
                    ->label('予約状況')
                    ->formatStateUsing(fn (bool $state): string => $state ? '予約済み' : '空き')
                    ->colors([
                        'danger' => true,
                        'success' => false,
                    ])
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->label('イベントメニュー')
                    ->relationship('menu', 'name', fn (Builder $query) => $query->where('is_event', true)),
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
                Tables\Actions\EditAction::make(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, Slot $record): void {
                        if ($record->is_reserved || $record->confirmedReservations()->exists()) {
                            Notification::make()
                                ->title('予約に使用されている時間枠は削除できません。')
                                ->danger()
                                ->send();
                            $action->halt();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Tables\Actions\DeleteBulkAction $action, $records): void {
                            if ($records->contains(fn (Slot $record) => $record->is_reserved || $record->confirmedReservations()->exists())) {
                                Notification::make()
                                    ->title('予約に使用されている時間枠が含まれるため削除できません。')
                                    ->danger()
                                    ->send();
                                $action->halt();
                            }
                        }),
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
            'index' => Pages\ListSlots::route('/'),
            'create' => Pages\CreateSlot::route('/create'),
            'edit' => Pages\EditSlot::route('/{record}/edit'),
            'calendar' => Pages\ManageSlotCalendar::route('/calendar'),
        ];
    }
}
