@component('mail::message')
# 予約キャンセルのお知らせ

{{ $reservation->user->name }} 様

以下の予約がキャンセルされました。

## キャンセルされた予約

**メニュー:** {{ $reservation->menu->name }}

**日時:** {{ $reservation->slot->date->format('Y年m月d日') }} {{ $reservation->slot->start_time->format('H:i') }} - {{ $reservation->slot->end_time->format('H:i') }}

またのご予約をお待ちしております。

@component('mail::button', ['url' => route('menus.index')])
新しく予約する
@endcomponent

{{ config('app.name') }}
@endcomponent
