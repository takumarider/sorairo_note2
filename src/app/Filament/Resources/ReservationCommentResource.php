<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationCommentResource\Pages;
use App\Models\Reservation;
use App\Models\ReservationComment;
use App\Models\User;
use App\Services\ReservationCommentService;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ReservationCommentResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'sorairoメッセージ';

    protected static ?string $modelLabel = '予約メッセージ';

    protected static ?string $pluralModelLabel = '予約メッセージ';

    public static function getNavigationBadge(): ?string
    {
        $newCount = static::newQuery(Reservation::query())->count();
        $unreadCount = static::unreadQuery(Reservation::query())
            ->whereNotIn('reservations.id', static::newQuery(Reservation::query())->select('reservations.id'))
            ->count();
        $total = $newCount + $unreadCount;

        return $total > 0 ? (string) $total : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function newQuery(Builder $query): Builder
    {
        return $query
            ->where('status', 'confirmed')
            ->whereDoesntHave('comments', fn (Builder $q) => $q->where('sender_type', ReservationComment::SENDER_ADMIN))
            ->whereNotExists(fn ($q) => $q->selectRaw('1')
                ->from('reservations as earlier')
                ->whereColumn('earlier.user_id', 'reservations.user_id')
                ->whereColumn('earlier.id', '<', 'reservations.id'));
    }

    public static function unreadQuery(Builder $query): Builder
    {
        return $query->whereHas('comments', fn (Builder $q) => $q
            ->where('sender_type', ReservationComment::SENDER_USER)
            ->whereNull('read_at'));
    }

    public static function endedQuery(Builder $query): Builder
    {
        return $query->whereIn('status', ['completed', 'canceled'])
            ->whereHas('comments')
            ->whereDoesntHave('comments', fn (Builder $q) => $q
                ->where('sender_type', ReservationComment::SENDER_USER)
                ->whereNull('read_at'));
    }

    public static function readQuery(Builder $query): Builder
    {
        return $query->where('status', 'confirmed')
            ->whereHas('comments')
            ->whereDoesntHave('comments', fn (Builder $q) => $q
                ->where('sender_type', ReservationComment::SENDER_USER)
                ->whereNull('read_at'))
            ->whereNotIn('reservations.id', static::newQuery(Reservation::query())->select('reservations.id'));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with(['user', 'menu', 'latestComment.sender'])
                ->withCount([
                    'comments as unread_user_comments_count' => fn (Builder $commentQuery) => $commentQuery
                        ->where('sender_type', ReservationComment::SENDER_USER)
                        ->whereNull('read_at'),
                ]))
            ->defaultSort('unread_user_comments_count', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('予約番号')
                    ->formatStateUsing(fn (int $state): string => '#'.str_pad((string) $state, 6, '0', STR_PAD_LEFT))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('お客様')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('menu.name')
                    ->label('メニュー')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('date')
                    ->label('予約日')
                    ->date('Y/m/d')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('状態')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'canceled' => 'キャンセル済み',
                        'completed' => '完了',
                        default => '予約確定',
                    }),
                TextColumn::make('latestComment.body')
                    ->label('最新メッセージ')
                    ->limit(50)
                    ->wrap(),
                TextColumn::make('unread_user_comments_count')
                    ->label('未読')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'danger' : 'gray')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('予約状態')
                    ->options([
                        'confirmed' => '予約確定',
                        'completed' => '完了',
                        'canceled' => 'キャンセル済み',
                    ]),
                TernaryFilter::make('has_unread_user_comments')
                    ->label('ユーザーメッセージ未読')
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('comments', fn (Builder $commentQuery) => $commentQuery
                            ->where('sender_type', ReservationComment::SENDER_USER)
                            ->whereNull('read_at')),
                        false: fn (Builder $query) => $query->whereDoesntHave('comments', fn (Builder $commentQuery) => $commentQuery
                            ->where('sender_type', ReservationComment::SENDER_USER)
                            ->whereNull('read_at')),
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('reply')
                    ->label('メッセージを確認・返信')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->modalHeading(fn (?Reservation $record): string => $record ? $record->user->name.'様とのメッセージ' : 'メッセージ')
                    ->modalSubmitActionLabel('返信メッセージを送信')
                    ->successNotificationTitle('返信を送信しました')
                    ->modalContent(fn (?Reservation $record) => $record ? view(
                        'filament.resources.reservation-comment-resource.reply-history',
                        ['comments' => app(ReservationCommentService::class)->commentsFor($record->id)]
                    ) : null)
                    ->form([
                        Textarea::make('body')
                            ->label('返信メッセージ')
                            ->rows(5)
                            ->maxLength(1000)
                            ->required(),
                    ])
                    ->action(function (Tables\Actions\Action $action, Reservation $record, array $data): void {
                        /** @var User $admin */
                        $admin = Auth::user();

                        // 開いた時点で既読にすると未読タブの絞り込みからレコードが外れ、送信が無効になるため返信時に既読化する
                        app(ReservationCommentService::class)->markReceivedCommentsAsRead(
                            $record,
                            ReservationComment::SENDER_USER
                        );

                        app(ReservationCommentService::class)->send(
                            $record,
                            $admin,
                            ReservationComment::SENDER_ADMIN,
                            $data['body']
                        );

                        $action->success();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReservationComments::route('/'),
        ];
    }
}
