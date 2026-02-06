<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = '設定';

    protected static ?string $navigationLabel = 'ユーザー';

    protected static ?string $modelLabel = 'ユーザー';

    protected static ?string $pluralModelLabel = 'ユーザー';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('名前')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('メールアドレス')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('email_verified_at')
                    ->label('メール確認日時'),
                Forms\Components\TextInput::make('password')
                    ->label('パスワード')
                    ->password()
                    ->rule(Password::defaults())
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create'),
                Forms\Components\Toggle::make('is_admin')
                    ->label('管理者')
                    ->default(false)
                    ->helperText('管理者権限の変更はセキュリティログに記録されます。')
                    ->disabled(fn (?User $record): bool =>
                        // 自分自身の管理者権限を外せないようにする
                        $record !== null && $record->id === auth()->id()
                    ),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('名前')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('メールアドレス')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_admin')
                    ->label('管理者')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reservations_count')
                    ->label('予約数')
                    ->counts('reservations')
                    ->sortable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label('メール確認日時')
                    ->dateTime()
                    ->sortable()
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
                Tables\Filters\TernaryFilter::make('is_admin')
                    ->label('管理者')
                    ->trueLabel('管理者')
                    ->falseLabel('一般')
                    ->nullable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Tables\Actions\DeleteBulkAction $action, \Illuminate\Database\Eloquent\Collection $records) {
                            // 管理者ユーザーの一括削除を防止
                            $adminUsers = $records->where('is_admin', true);
                            if ($adminUsers->isNotEmpty()) {
                                \Filament\Notifications\Notification::make()
                                    ->title('管理者ユーザーは一括削除できません')
                                    ->body('管理者権限を持つユーザーが含まれています。個別に管理者権限を解除してから削除してください。')
                                    ->danger()
                                    ->send();
                                $action->cancel();
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ReservationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
