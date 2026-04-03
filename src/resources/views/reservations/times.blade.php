<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            予約時刻を選択
        </h2>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:p-7">
                <div class="mb-6 rounded-2xl bg-gradient-to-r from-cyan-50 to-sky-50 p-4 ring-1 ring-cyan-100 sm:p-5">
                    <p class="text-xs font-semibold tracking-wide text-cyan-700">STEP 2 / 2</p>
                    <div class="mt-2">
                        <h1 class="text-xl font-bold leading-snug text-slate-900 sm:text-2xl">{{ $menu->name }}</h1>
                        <p class="mt-1 text-sm text-slate-600">{{ $date->isoFormat('Y年M月D日(dddd)') }}</p>
                    </div>

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
                    $reasonMessages = [
                        'business_hours_not_set' => 'この日は営業時間が未設定です。管理者側の営業時間マスタ設定後にご利用ください。',
                        'closed' => 'この日は休業日です。別の日付を選択してください。',
                        'fully_booked' => 'この日は予約可能な時間帯が埋まっています。別の日付をお試しください。',
                        'duration_too_long' => '選択したメニューの所要時間が営業時間内に収まりません。',
                    ];

                    $timeBuckets = [
                        'morning' => ['label' => '午前', 'range' => '09:00 - 11:59', 'times' => []],
                        'afternoon' => ['label' => '午後', 'range' => '12:00 - 16:59', 'times' => []],
                        'evening' => ['label' => '夕方以降', 'range' => '17:00 - 22:00', 'times' => []],
                    ];

                    foreach ($availableTimes as $time) {
                        $hour = (int) substr($time, 0, 2);
                        if ($hour < 12) {
                            $timeBuckets['morning']['times'][] = $time;
                        } elseif ($hour < 17) {
                            $timeBuckets['afternoon']['times'][] = $time;
                        } else {
                            $timeBuckets['evening']['times'][] = $time;
                        }
                    }
                @endphp

                <form method="GET" action="{{ route('reservations.confirm') }}" class="space-y-5">
                    <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                    <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                    @foreach($optionIds as $optionId)
                        <input type="hidden" name="options[]" value="{{ $optionId }}">
                    @endforeach

                    <div>
                        <h2 class="text-base font-bold text-slate-900 sm:text-lg">開始時間を選択してください</h2>
                        <p class="mt-1 text-xs text-slate-500">ボタンをタップすると確認画面へ進みます</p>
                    </div>

                    @if(empty($availableTimes))
                            <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800">
                                <p>{{ $reasonMessages[$availabilityReason] ?? 'この日に利用可能な時間がありません。別の日付でお試しください。' }}</p>
                            </div>
                    @else
                        <button type="submit"
                                name="start_time"
                                value="{{ $availableTimes[0] }}"
                                class="w-full rounded-2xl border border-sky-300 bg-sky-50 px-4 py-3 text-left ring-1 ring-sky-100 transition hover:bg-sky-100 active:scale-[0.99]">
                            <p class="text-xs font-semibold text-sky-700">最短で予約できる時間</p>
                            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $availableTimes[0] }}</p>
                            <p class="mt-1 text-xs text-slate-600">終了予定 {{ \Carbon\Carbon::createFromFormat('H:i', $availableTimes[0])->addMinutes($totalDuration)->format('H:i') }}</p>
                        </button>

                        @foreach($timeBuckets as $bucket)
                            @if(!empty($bucket['times']))
                                <section class="rounded-xl border border-slate-200 bg-slate-50 p-3 sm:p-4">
                                    <div class="mb-3 flex items-center justify-between">
                                        <h3 class="text-sm font-semibold text-slate-900">{{ $bucket['label'] }}</h3>
                                        <span class="text-xs text-slate-500">{{ $bucket['range'] }}</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                        @foreach($bucket['times'] as $time)
                                            <button type="submit"
                                                    name="start_time"
                                                    value="{{ $time }}"
                                                    class="rounded-xl border border-sky-300 bg-sky-50 px-2 py-2.5 text-center transition hover:border-sky-500 hover:bg-sky-100 active:scale-[0.99]">
                                                <span class="block text-base font-bold text-slate-900">{{ $time }}</span>
                                                <span class="block text-[11px] text-slate-500">終了 {{ \Carbon\Carbon::createFromFormat('H:i', $time)->addMinutes($totalDuration)->format('H:i') }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </section>
                            @endif
                        @endforeach
                    @endif
                </form>

                <!-- 戻るボタン -->
                <div class="mt-6">
                    <a href="{{ route('reservations.calendar', ['menu_id' => $menu->id, 'options' => $optionIds]) }}" 
                       class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition">
                        ← 日付選択に戻る
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
