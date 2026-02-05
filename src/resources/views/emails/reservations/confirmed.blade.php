@component('mail::message')
# 予約確定のお知らせ

{{ $reservation->user->name }} 様

ご予約ありがとうございます。
以下の内容で予約を受け付けました。

## 予約内容

**メニュー:** {{ $reservation->menu->name }}

**日時:** {{ $reservation->slot->date->format('Y年m月d日') }} {{ $reservation->slot->start_time->format('H:i') }} - {{ $reservation->slot->end_time->format('H:i') }}

**料金:** ¥{{ number_format($reservation->menu->price) }}

**所要時間:** {{ $reservation->menu->duration }}分

@component('mail::button', ['url' => route('mypage')])
マイページで確認
@endcomponent

ご来店をお待ちしております。

{{ config('app.name') }}
@endcomponent
