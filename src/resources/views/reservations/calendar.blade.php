<x-app-layout>
    <style>
        .reservation-calendar-mobile {
            display: block;
        }

        .reservation-calendar-desktop {
            display: none;
        }

        @media (min-width: 640px) {
            .reservation-calendar-mobile {
                display: none;
            }

            .reservation-calendar-desktop {
                display: block;
            }
        }
    </style>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            予約日を選択
        </h2>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:p-7">
                <div class="mb-5 rounded-2xl bg-gradient-to-r from-cyan-50 to-sky-50 p-4 ring-1 ring-cyan-100 sm:p-5">
                    <p class="text-xs font-semibold tracking-wide text-cyan-700">STEP 1 / 2</p>
                    <h1 class="mt-1 text-xl font-bold text-slate-900 sm:text-2xl">{{ $menu->name }}</h1>
                    <div class="mt-3 grid grid-cols-2 gap-2 text-sm sm:flex sm:flex-wrap sm:gap-5">
                        <div class="rounded-lg bg-white px-3 py-2 text-slate-700 ring-1 ring-slate-200">
                            <span class="text-xs text-slate-500">料金</span>
                            <div class="font-bold text-cyan-700">¥{{ number_format($totalPrice) }}</div>
                        </div>
                        <div class="rounded-lg bg-white px-3 py-2 text-slate-700 ring-1 ring-slate-200">
                            <span class="text-xs text-slate-500">所要時間</span>
                            <div class="font-bold text-slate-900">{{ $totalDuration }}分</div>
                        </div>
                    </div>

                    @if($options->isNotEmpty())
                    <div class="mt-3">
                        <h3 class="mb-2 text-xs font-semibold text-slate-600">選択オプション</h3>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($options as $option)
                                <span class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1 text-xs text-slate-700 ring-1 ring-slate-200">
                                    {{ $option->name }}
                                    <svg class="h-3.5 w-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                @php
                    $flashReasonMessages = [
                        'business_hours_not_set' => '選択した日は営業時間が未設定です。管理者側で営業時間マスタを設定してください。',
                        'closed' => '選択した日は休業日です。別の日付を選択してください。',
                        'fully_booked' => '選択した日は予約可能な時間帯が埋まっています。別の日付をお試しください。',
                        'duration_too_long' => '選択したメニューの所要時間が営業時間内に収まりません。',
                    ];
                    $flashReason = session('availability_reason');

                    $start = $month->clone()->startOfMonth()->startOfWeek();
                    $end = $month->clone()->endOfMonth()->endOfWeek();
                    $availableDateList = [];
                    for ($d = $start->copy(); $d < $end; $d->addDay()) {
                        $key = $d->toDateString();
                        if (($availableDates[$key] ?? false) && ! $d->isPast()) {
                            $availableDateList[] = $d->copy();
                        }
                    }
                @endphp

                @if($flashReason && isset($flashReasonMessages[$flashReason]))
                    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                        {{ $flashReasonMessages[$flashReason] }}
                    </div>
                @elseif(($availabilitySummary['configured_days'] ?? 0) === 0)
                    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                        この月は営業時間がまだ設定されていません。管理者側で営業時間マスタを登録すると、予約可能日が表示されます。
                    </div>
                @elseif(($availabilitySummary['open_days'] ?? 0) > 0 && ($availabilitySummary['available_days'] ?? 0) === 0)
                    <div class="mb-4 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                        この月は営業日の設定がありますが、現在予約可能な日がありません。予約の埋まり状況または所要時間をご確認ください。
                    </div>
                @endif

                <div class="mb-5 flex items-center justify-between gap-3">
                    <a href="{{ url()->full() . (strpos(url()->full(), '?') ? '&' : '?') . 'month=' . $month->clone()->subMonth()->format('Y-m') }}"
                       class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700">
                        ← 前月
                    </a>
                    <h2 class="text-base font-bold text-slate-900 sm:text-lg">{{ $month->isoFormat('Y年M月') }}</h2>
                          <a href="{{ url()->full() . (strpos(url()->full(), '?') ? '&' : '?') . 'month=' . $month->clone()->addMonth()->format('Y-m') }}"
                              class="rounded-xl border border-sky-600 !bg-sky-600 px-3 py-2 text-sm font-semibold !text-white shadow-sm transition hover:!bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-300 focus:ring-offset-2">
                        翌月 →
                    </a>
                </div>

                <form method="GET" action="{{ route('reservations.times') }}" class="reservation-calendar-mobile">
                    <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                    @foreach($optionIds as $optionId)
                        <input type="hidden" name="options[]" value="{{ $optionId }}">
                    @endforeach

                    <h3 class="mb-2 text-sm font-semibold text-slate-700">空きのある日</h3>
                    <p class="mb-3 text-xs text-slate-500">タップすると次の画面で時間を選べます</p>

                    @if(empty($availableDateList))
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                            この月に選べる日付がありません。前月・翌月もお試しください。
                        </div>
                    @else
                        <div class="space-y-2">
                            @foreach($availableDateList as $date)
                                <button type="submit"
                                        name="date"
                                        value="{{ $date->toDateString() }}"
                                        class="w-full rounded-xl border border-sky-500 bg-sky-500 px-4 py-3 text-left text-white shadow-sm transition hover:bg-sky-600 active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-sky-300 focus:ring-offset-2">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-xs font-semibold text-sky-100">{{ $date->isoFormat('M月D日(ddd)') }}</p>
                                            <p class="text-base font-bold text-white">{{ $date->isoFormat('Y年M月D日') }}</p>
                                        </div>
                                        <span class="rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-sky-700 ring-1 ring-sky-200">選択する</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </form>

                <form method="GET" action="{{ route('reservations.times') }}" class="reservation-calendar-desktop">
                    <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                    @foreach($optionIds as $optionId)
                        <input type="hidden" name="options[]" value="{{ $optionId }}">
                    @endforeach

                    <h3 class="mb-2 text-sm font-semibold text-slate-700">空きのある日</h3>
                    <p class="mb-3 text-xs text-slate-500">クリックすると次の画面で時間を選べます</p>

                    @if(empty($availableDateList))
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                            この月に選べる日付がありません。前月・翌月もお試しください。
                        </div>
                    @else
                        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                            @foreach($availableDateList as $date)
                                <button type="submit"
                                        name="date"
                                        value="{{ $date->toDateString() }}"
                                        class="w-full rounded-xl border border-sky-500 bg-sky-500 px-4 py-3 text-left text-white shadow-sm transition hover:bg-sky-600 active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-sky-300 focus:ring-offset-2">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-xs font-semibold text-sky-100">{{ $date->isoFormat('M月D日(ddd)') }}</p>
                                            <p class="text-base font-bold text-white">{{ $date->isoFormat('Y年M月D日') }}</p>
                                        </div>
                                        <span class="rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-sky-700 ring-1 ring-sky-200">選択する</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </form>

                <div class="rounded-xl border border-cyan-200 bg-cyan-50 p-4 text-sm text-slate-700">
                    日付を選ぶと次の画面で時間を選択できます。
                </div>

                <!-- 戻るボタン -->
                <div class="mt-6">
                    <a href="{{ route('menus.show', $menu->id) }}" 
                       class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition">
                        ← メニュー詳細に戻る
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
