<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Reservation;
use App\Models\ReservationComment;
use App\Models\User;
use App\Services\ReservationCommentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReservationCommentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_reservation_comment_service_operations(): void
    {
        $service = new ReservationCommentService;
        $user = User::factory()->create();
        $menu = Menu::factory()->create();
        $reservation = Reservation::create([
            'user_id' => $user->id,
            'menu_id' => $menu->id,
            'date' => now('Asia/Tokyo')->addDay()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'confirmed',
        ]);

        $this->assertNull($service->getComment($reservation->id));

        $service->saveComment($reservation->id, '特別なご要望テスト');
        $this->assertEquals('特別なご要望テスト', $service->getComment($reservation->id));

        $service->saveComment($reservation->id, '');
        $this->assertEquals('特別なご要望テスト', $service->getComment($reservation->id));

        $service->deleteComment($reservation->id);
        $this->assertNull($service->getComment($reservation->id));
    }

    public function test_user_and_admin_can_exchange_comments_and_track_unread_messages(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $menu = Menu::factory()->create();
        $reservation = Reservation::create([
            'user_id' => $user->id,
            'menu_id' => $menu->id,
            'date' => now('Asia/Tokyo')->subDay()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'completed',
        ]);

        $this->actingAs($user)
            ->post(route('mypage.reservations.comments.store', $reservation), ['body' => '施術について確認したいです。'])
            ->assertRedirect(route('mypage.reservations.comments', $reservation));

        $this->assertDatabaseHas('reservation_comments', [
            'reservation_id' => $reservation->id,
            'sender_type' => ReservationComment::SENDER_USER,
            'sender_id' => $user->id,
            'body' => '施術について確認したいです。',
            'read_at' => null,
        ]);

        $service = app(ReservationCommentService::class);
        $service->markReceivedCommentsAsRead($reservation, ReservationComment::SENDER_USER);
        $service->send($reservation, $admin, ReservationComment::SENDER_ADMIN, '承知しました。');

        $response = $this->actingAs($user)->get(route('mypage.reservations.comments', $reservation));
        $response->assertOk()
            ->assertSee('メッセージ履歴')
            ->assertSee('メッセージ履歴')
            ->assertSee('サロンへメッセージを入力')
            ->assertSee('承知しました。');

        $this->assertDatabaseHas('reservation_comments', [
            'reservation_id' => $reservation->id,
            'sender_type' => ReservationComment::SENDER_ADMIN,
            'body' => '承知しました。',
        ]);
        $this->assertDatabaseMissing('reservation_comments', [
            'reservation_id' => $reservation->id,
            'sender_type' => ReservationComment::SENDER_ADMIN,
            'body' => '承知しました。',
            'read_at' => null,
        ]);

        $mine = ReservationComment::where('sender_type', ReservationComment::SENDER_USER)->firstOrFail();
        $this->actingAs($user)
            ->delete(route('mypage.reservations.comments.destroy', [$reservation, $mine]))
            ->assertRedirect(route('mypage.reservations.comments', $reservation));

        $this->assertNotNull($mine->fresh()->hidden_by_user_at);
        $this->actingAs($user)->get(route('mypage.reservations.comments', $reservation))
            ->assertDontSee('施術について確認したいです。')
            ->assertSee('承知しました。');

        $adminComment = ReservationComment::where('sender_type', ReservationComment::SENDER_ADMIN)->firstOrFail();
        $this->actingAs($user)
            ->delete(route('mypage.reservations.comments.destroy', [$reservation, $adminComment]))
            ->assertNotFound();
    }

    public function test_legacy_comment_import_command_moves_file_comments_to_database(): void
    {
        $user = User::factory()->create();
        $menu = Menu::factory()->create();
        $reservation = Reservation::create([
            'user_id' => $user->id,
            'menu_id' => $menu->id,
            'date' => now('Asia/Tokyo')->addDay()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'confirmed',
        ]);
        Storage::disk('local')->put(
            'reservation_comments/'.$reservation->id.'.txt',
            '以前のコメントです。'
        );

        $this->artisan('reservation-comments:import-legacy')
            ->expectsOutput('1件の予約コメントを移行しました。')
            ->assertExitCode(0);

        $this->assertDatabaseHas('reservation_comments', [
            'reservation_id' => $reservation->id,
            'sender_type' => ReservationComment::SENDER_USER,
            'sender_id' => $user->id,
            'body' => '以前のコメントです。',
        ]);
    }

    public function test_user_cannot_view_another_users_reservation_comments(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $menu = Menu::factory()->create();
        $reservation = Reservation::create([
            'user_id' => $owner->id,
            'menu_id' => $menu->id,
            'date' => now('Asia/Tokyo')->addDay()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'confirmed',
        ]);

        $this->actingAs($otherUser)
            ->get(route('mypage.reservations.comments', $reservation))
            ->assertForbidden();
    }

    public function test_admin_can_open_the_reservation_comment_management_page(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $menu = Menu::factory()->create(['name' => 'コメント確認メニュー']);
        $reservation = Reservation::create([
            'user_id' => $user->id,
            'menu_id' => $menu->id,
            'date' => now('Asia/Tokyo')->addDay()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'confirmed',
        ]);
        app(ReservationCommentService::class)->send(
            $reservation,
            $user,
            ReservationComment::SENDER_USER,
            '管理画面で確認するコメントです。'
        );

        $this->actingAs($admin)
            ->get('/admin/reservation-comments')
            ->assertOk()
            ->assertSee('sorairoメッセージ')
            ->assertSee('最新メッセージ')
            ->assertSee('コメント確認メニュー')
            ->assertSee('管理画面で確認するコメントです。');
    }

    public function test_user_can_submit_reservation_with_comment_and_view_it(): void
    {
        for ($day = 0; $day < 7; $day++) {
            \App\Models\BusinessHour::create([
                'day_of_week' => $day,
                'open_time' => '10:00',
                'close_time' => '20:00',
                'is_closed' => false,
            ]);
        }

        $targetDate = now('Asia/Tokyo')->addDay();
        \App\Models\ReservationPublicationMonth::updateOrCreate([
            'year_month' => $targetDate->format('Y-m'),
        ], [
            'is_published' => true,
        ]);

        $user = User::factory()->create();
        $menu = Menu::factory()->create(['duration' => 60, 'price' => 5000]);

        $response = $this->actingAs($user)->post(route('reservations.store'), [
            'menu_id' => $menu->id,
            'date' => $targetDate->format('Y-m-d'),
            'start_time' => '10:00',
            'comment' => '肌が弱いので弱めの薬剤希望です。',
        ]);

        $response->assertRedirect();
        $reservation = Reservation::where('user_id', $user->id)->first();
        $this->assertNotNull($reservation);

        $service = new ReservationCommentService;
        $this->assertEquals('肌が弱いので弱めの薬剤希望です。', $service->getComment($reservation->id));

        // 完了画面
        $completeResponse = $this->actingAs($user)->get(route('reservations.complete', ['reservation' => $reservation->id]));
        $completeResponse->assertStatus(200);
        $completeResponse->assertSee('sorairoメッセージ・ご要望');
        $completeResponse->assertSee('肌が弱いので弱めの薬剤希望です。');

        // マイページ
        $mypageResponse = $this->actingAs($user)->get(route('mypage'));
        $mypageResponse->assertStatus(200);
        $mypageResponse->assertSee('sorairoメッセージ');
        $mypageResponse->assertSee(route('mypage.messages'), false);

        $this->actingAs($user)->get(route('mypage.messages'))
            ->assertOk()
            ->assertSee('メッセージを見る');

        // 管理者カレンダー詳細
        $admin = User::factory()->create(['is_admin' => true]);
        $adminResponse = $this->actingAs($admin)->get('/admin/reservations/calendar');
        $adminResponse->assertStatus(200);
    }

    public function test_admin_comment_tabs_split_new_unread_and_read(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $menu = Menu::factory()->create();
        $mk = fn (User $u) => Reservation::create([
            'user_id' => $u->id, 'menu_id' => $menu->id,
            'date' => now('Asia/Tokyo')->addDay()->toDateString(),
            'start_time' => '10:00', 'end_time' => '11:00', 'status' => 'confirmed',
        ]);
        $service = app(ReservationCommentService::class);

        $newUser = User::factory()->create();
        $new = $mk($newUser);

        $unreadUser = User::factory()->create();
        $mk($unreadUser);
        $unread = $mk($unreadUser);
        $service->send($unread, $unreadUser, ReservationComment::SENDER_USER, '未読です');

        $readUser = User::factory()->create();
        $mk($readUser);
        $read = $mk($readUser);
        $service->send($read, $readUser, ReservationComment::SENDER_USER, '既読です');
        $service->markReceivedCommentsAsRead($read, ReservationComment::SENDER_USER);

        $endedUser = User::factory()->create();
        $mk($endedUser);
        $ended = $mk($endedUser);
        $ended->update(['status' => 'completed']);
        $service->send($ended, $endedUser, ReservationComment::SENDER_USER, '終了済み');
        $service->markReceivedCommentsAsRead($ended, ReservationComment::SENDER_USER);

        $reopenUser = User::factory()->create();
        $mk($reopenUser);
        $reopen = $mk($reopenUser);
        $reopen->update(['status' => 'completed']);
        $service->send($reopen, $reopenUser, ReservationComment::SENDER_USER, '終了後の連絡');

        $markedUser = User::factory()->create();
        $marked = $mk($markedUser);
        $service->send($marked, $markedUser, ReservationComment::SENDER_USER, '最初の予約で質問');
        $service->markReceivedCommentsAsRead($marked, ReservationComment::SENDER_USER);

        $ids = fn (string $tab) => \Livewire\Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\ReservationCommentResource\Pages\ListReservationComments::class, ['activeTab' => $tab])
            ->instance()->getTableRecords()->pluck('id')->all();

        $this->assertSame([$new->id], array_values(array_intersect($ids('new'), [$new->id, $unread->id, $read->id, $ended->id, $reopen->id, $marked->id])));
        $this->assertEqualsCanonicalizing([$unread->id, $reopen->id], array_values(array_intersect($ids('unread'), [$new->id, $unread->id, $read->id, $ended->id, $reopen->id, $marked->id])));
        $this->assertSame([$ended->id], array_values(array_intersect($ids('ended'), [$new->id, $unread->id, $read->id, $ended->id, $reopen->id, $marked->id])));
        $this->assertEqualsCanonicalizing([$read->id, $marked->id], array_values(array_intersect($ids('read'), [$new->id, $unread->id, $read->id, $ended->id, $reopen->id, $marked->id])));
    }

    public function test_past_and_canceled_reservations_are_listed_in_message_archive(): void
    {
        $user = User::factory()->create();
        $make = fn (string $name, string $date, string $status) => Reservation::create([
            'user_id' => $user->id,
            'menu_id' => Menu::factory()->create(['name' => $name])->id,
            'date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => $status,
        ]);
        $make('現行メニューA', now('Asia/Tokyo')->addDay()->toDateString(), 'confirmed');
        $make('過去メニューB', now('Asia/Tokyo')->subDay()->toDateString(), 'completed');
        $make('取消メニューC', now('Asia/Tokyo')->addDay()->toDateString(), 'canceled');

        $this->actingAs($user)->get(route('mypage.messages'))
            ->assertOk()
            ->assertSee('現行メニューA')
            ->assertDontSee('過去メニューB')
            ->assertDontSee('取消メニューC')
            ->assertSee(route('mypage.messages.archive'), false);

        $this->actingAs($user)->get(route('mypage.messages.archive'))
            ->assertOk()
            ->assertSee('過去メニューB')
            ->assertSee('取消メニューC')
            ->assertDontSee('現行メニューA');
    }

    public function test_admin_reply_from_unread_tab_sends_once_and_marks_read(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $reservation = Reservation::create([
            'user_id' => $user->id,
            'menu_id' => Menu::factory()->create()->id,
            'date' => now('Asia/Tokyo')->addDay()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'confirmed',
        ]);
        $service = app(ReservationCommentService::class);
        $service->send($reservation, $admin, ReservationComment::SENDER_ADMIN, '先行返信');
        $service->send($reservation, $user, ReservationComment::SENDER_USER, '未読の質問です');

        \Livewire\Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\ReservationCommentResource\Pages\ListReservationComments::class)
            ->set('activeTab', 'unread')
            ->callTableAction('reply', $reservation, ['body' => '返信です'])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('reservation_comments', ['reservation_id' => $reservation->id, 'sender_type' => ReservationComment::SENDER_ADMIN, 'body' => '返信です']);
        $this->assertDatabaseMissing('reservation_comments', ['reservation_id' => $reservation->id, 'sender_type' => ReservationComment::SENDER_USER, 'read_at' => null]);
    }

    public function test_admin_can_dismiss_new_reservations_to_ended_tab(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $menu = Menu::factory()->create();
        $mk = fn (User $u) => Reservation::create([
            'user_id' => $u->id, 'menu_id' => $menu->id,
            'date' => now('Asia/Tokyo')->addDay()->toDateString(),
            'start_time' => '10:00', 'end_time' => '11:00', 'status' => 'confirmed',
        ]);

        $single = $mk(User::factory()->create());
        $bulkA = $mk(User::factory()->create());
        $bulkB = $mk(User::factory()->create());
        $unreadUser = User::factory()->create();
        $withUnread = $mk($unreadUser);
        app(ReservationCommentService::class)->send($withUnread, $unreadUser, ReservationComment::SENDER_USER, '未読です');

        $page = \App\Filament\Resources\ReservationCommentResource\Pages\ListReservationComments::class;
        $ids = fn (string $tab) => \Livewire\Livewire::actingAs($admin)
            ->test($page, ['activeTab' => $tab])
            ->instance()->getTableRecords()->pluck('id')->all();

        \Livewire\Livewire::actingAs($admin)->test($page)
            ->callTableAction('dismissNew', $single)
            ->callTableBulkAction('dismissNewBulk', [$bulkA, $bulkB, $withUnread]);

        $this->assertNotNull($single->fresh()->new_dismissed_at);
        $this->assertNotNull($bulkA->fresh()->new_dismissed_at);
        $this->assertNotNull($bulkB->fresh()->new_dismissed_at);

        $check = [$single->id, $bulkA->id, $bulkB->id, $withUnread->id];
        $this->assertSame([], array_values(array_intersect($ids('new'), $check)));
        $this->assertEqualsCanonicalizing([$single->id, $bulkA->id, $bulkB->id], array_values(array_intersect($ids('ended'), $check)));
        $this->assertSame([], array_values(array_intersect($ids('read'), $check)));
        $this->assertSame([$withUnread->id], array_values(array_intersect($ids('unread'), $check)));
    }

    public function test_admin_can_toggle_read_and_unread_without_replying(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $reservation = Reservation::create([
            'user_id' => $user->id,
            'menu_id' => Menu::factory()->create()->id,
            'date' => now('Asia/Tokyo')->addDay()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'confirmed',
        ]);
        $service = app(ReservationCommentService::class);
        $service->send($reservation, $admin, ReservationComment::SENDER_ADMIN, '先行返信');
        $service->send($reservation, $user, ReservationComment::SENDER_USER, '質問です');

        $component = \Livewire\Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\ReservationCommentResource\Pages\ListReservationComments::class)
            ->set('activeTab', 'unread')
            ->callTableAction('toggleRead', $reservation);

        $this->assertDatabaseMissing('reservation_comments', ['reservation_id' => $reservation->id, 'sender_type' => ReservationComment::SENDER_USER, 'read_at' => null]);

        $component->set('activeTab', 'read')->callTableAction('toggleRead', $reservation);

        $this->assertDatabaseHas('reservation_comments', ['reservation_id' => $reservation->id, 'sender_type' => ReservationComment::SENDER_USER, 'read_at' => null]);
    }

    public function test_admin_can_delete_own_message_and_user_no_longer_sees_it(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $reservation = Reservation::create([
            'user_id' => $user->id,
            'menu_id' => Menu::factory()->create()->id,
            'date' => now('Asia/Tokyo')->addDay()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'confirmed',
        ]);
        $service = app(ReservationCommentService::class);
        $wrong = $service->send($reservation, $admin, ReservationComment::SENDER_ADMIN, '間違い送信です');
        $hidden = $service->send($reservation, $user, ReservationComment::SENDER_USER, 'ユーザー削除分');
        $hidden->update(['hidden_by_user_at' => now()]);

        \Livewire\Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\ReservationCommentResource\Pages\ListReservationComments::class)
            ->call('deleteAdminComment', $wrong->id);

        $this->assertNotNull($wrong->fresh()->deleted_by_admin_at);
        $this->assertDatabaseHas('reservation_comments', ['id' => $wrong->id]);

        $this->actingAs($user)->get(route('mypage.reservations.comments', $reservation))
            ->assertOk()
            ->assertDontSee('間違い送信です');

        $html = view('filament.resources.reservation-comment-resource.reply-history', [
            'comments' => $service->commentsFor($reservation->id),
        ])->render();
        $this->assertStringContainsString('お客様が削除済み', $html);
        $this->assertStringContainsString('ユーザー削除分', $html);
        $this->assertStringContainsString('削除済みのメッセージ', $html);
        $this->assertStringNotContainsString('間違い送信です', $html);
    }

    public function test_header_mypage_button_shows_unread_admin_message_count(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $menu = Menu::factory()->create();
        $reservation = Reservation::create([
            'user_id' => $user->id, 'menu_id' => $menu->id,
            'date' => now('Asia/Tokyo')->addDay()->toDateString(),
            'start_time' => '10:00', 'end_time' => '11:00', 'status' => 'confirmed',
        ]);

        $this->actingAs($user)->get(route('menus.index'))->assertDontSee('未読1');

        $service = app(ReservationCommentService::class);
        $service->send($reservation, $admin, ReservationComment::SENDER_ADMIN, 'こんにちは');
        $this->actingAs($user)->get(route('menus.index'))->assertSee('未読1');

        $service->markReceivedCommentsAsRead($reservation, ReservationComment::SENDER_ADMIN);
        $this->actingAs($user)->get(route('menus.index'))->assertDontSee('未読1');
    }
}
