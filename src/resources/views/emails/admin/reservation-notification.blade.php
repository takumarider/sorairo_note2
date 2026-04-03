@component('mail::message')
# {{ $type === 'confirmed' ? '新規予約通知' : '予約キャンセル通知' }}

{{ $type === 'confirmed' ? '新しい予約が入りました。' : '予約がキャンセルされました。' }}

## 予約情報

**予約者:** {{ $reservation->user->name }} ({{ $reservation->user->email }})

**メニュー:** {{ $reservation->menu->name }}

**日時:** {{ $reservation->date->format('Y年m月d日') }} {{ $reservation->start_time->format('H:i') }} - {{ $reservation->end_time->format('H:i') }}

**料金:** ¥{{ number_format($reservation->menu->price) }}

**予約ID:** #{{ $reservation->id }}

**ステータス:** {{ $reservation->status === 'confirmed' ? '確定' : 'キャンセル' }}

@component('mail::button', ['url' => config('app.url') . '/admin/reservations/' . $reservation->id])
管理画面で確認
@endcomponent

{{ config('app.name') }}
@endcomponent
