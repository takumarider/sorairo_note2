@component('mail::message')
# 予約キャンセルのお知らせ

{{ $reservation->user->name }} 様

以下の予約がキャンセルされました。

## キャンセルされた予約

**メニュー:** {{ $reservation->menu->name }}

@php
	$reservationDate = $reservation->date ?? $reservation->slot?->date;
	$reservationStart = $reservation->start_time ?? $reservation->slot?->start_time;
	$reservationEnd = $reservation->end_time ?? $reservation->slot?->end_time;
@endphp

**日時:** {{ $reservationDate?->format('Y年m月d日') }} {{ $reservationStart?->format('H:i') }} - {{ $reservationEnd?->format('H:i') }}

またのご予約をお待ちしております。

@component('mail::button', ['url' => route('menus.index')])
新しく予約する
@endcomponent

{{ config('app.name') }}
@endcomponent
