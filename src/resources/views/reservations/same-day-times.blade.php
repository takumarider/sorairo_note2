<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            今日の予約
        </h2>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:p-7">
                <div class="mb-6 rounded-2xl bg-gradient-to-r from-cyan-50 to-sky-50 p-4 ring-1 ring-cyan-100 sm:p-5">
                    <p class="text-xs font-semibold tracking-wide text-cyan-700">SAME DAY RESERVATION</p>
                    <h1 class="mt-1 text-xl font-bold text-slate-900 sm:text-2xl">{{ $date->isoFormat('Y年M月D日(ddd)') }}</h1>
                    <p class="mt-2 text-sm text-slate-600">空いている時間を選択すると、次の画面でメニューとオプションを選べます。</p>
                </div>

                @if($errors->has('start_time'))
                    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                        {{ $errors->first('start_time') }}
                    </div>
                @endif

                @php
                    $reasonMessages = [
                        'month_unpublished' => '当月の予約枠が現在非公開です。管理者の公開設定後にご利用ください。',
                        'fully_booked' => '本日ご案内できる時間がありません。お手数ですが通常予約から別日をご確認ください。',
                    ];
                @endphp

                @if(empty($availableTimes))
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                        {{ $reasonMessages[$availabilityReason] ?? '本日ご案内できる時間がありません。' }}
                    </div>
                @else
                    <form method="GET" action="{{ route('reservations.same-day.menus') }}" class="space-y-5">
                        <div>
                            <h2 class="text-base font-bold text-slate-900 sm:text-lg">時間を選択してください</h2>
                            <p class="mt-1 text-xs text-slate-500">この時間に予約可能な通常メニューのみ、次の画面で表示されます。</p>
                        </div>

                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            @foreach($availableTimes as $time)
                                <button type="submit"
                                        name="start_time"
                                        value="{{ $time }}"
                                        class="rounded-xl border border-sky-300 bg-sky-50 px-3 py-3 text-center transition hover:border-sky-500 hover:bg-sky-100 active:scale-[0.99]">
                                    <span class="block text-lg font-bold text-slate-900">{{ $time }}</span>
                                    <span class="block text-[11px] text-slate-500">この時間で探す</span>
                                </button>
                            @endforeach
                        </div>
                    </form>
                @endif

                <div class="mt-6">
                    <a href="{{ route('menus.index') }}" class="text-sm font-semibold text-slate-600 transition hover:text-slate-900">
                        ← メニュー一覧へ戻る
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
