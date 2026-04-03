<x-filament-panels::page>

    {{-- ===== 月ナビゲーションバー ===== --}}
    <div class="flex items-center justify-between gap-3 rounded-xl border border-sky-200 bg-sky-50 px-5 py-3">
        <x-filament::button
            wire:click="prevMonth"
            color="gray"
            icon="heroicon-m-chevron-left"
            size="sm"
        >
            前月
        </x-filament::button>

        <div class="text-center">
            <h2 class="text-lg font-bold text-slate-900">
                {{ \Carbon\Carbon::createFromFormat('Y-m', $this->selectedMonth)->isoFormat('YYYY年M月') }}
            </h2>
            <p class="text-xs text-slate-500">営業スケジュールプレビュー</p>
        </div>

        <x-filament::button
            wire:click="nextMonth"
            color="gray"
            icon="heroicon-m-chevron-right"
            icon-position="after"
            size="sm"
        >
            翌月
        </x-filament::button>
    </div>

    {{-- ===== 月別スケジュールプレビュー ===== --}}
    @php
        $days = $this->getMonthDays();
        $hasAnySetting = collect($days)->contains(fn ($d) => $d['setting'] !== null);
    @endphp

    @if(! $hasAnySetting)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <div class="flex items-center gap-2 font-semibold">
                <x-heroicon-s-exclamation-triangle class="h-4 w-4 text-amber-600" />
                この月はまだ営業時間が設定されていません
            </div>
            <p class="mt-1 text-amber-700">
                右上の <strong>「初期設定ガイド」</strong> ボタンから曜日ごとの営業時間をまとめて登録できます。特定日だけ変更したい場合は <strong>「新規作成」</strong> から特定日を指定して登録してください。
            </p>
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        {{-- 凡例 --}}
        <div class="flex flex-wrap items-center gap-4 border-b border-slate-100 bg-slate-50 px-4 py-2 text-xs text-slate-600">
            <span class="font-semibold text-slate-500">凡例:</span>
            <span class="flex items-center gap-1.5">
                <span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                営業
            </span>
            <span class="flex items-center gap-1.5">
                <span class="h-2.5 w-2.5 rounded-full bg-rose-400"></span>
                休業日
            </span>
            <span class="flex items-center gap-1.5">
                <span class="h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                未設定（予約不可）
            </span>
            <span class="flex items-center gap-1.5">
                <span class="inline-flex items-center gap-1 rounded-full bg-violet-100 px-1.5 py-0.5 text-xs font-semibold text-violet-700">
                    ★ 特定日設定
                </span>
                曜日デフォルトを上書き
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3">日付</th>
                        <th class="px-3 py-3">曜日</th>
                        <th class="px-4 py-3">営業時間</th>
                        <th class="px-4 py-3">状態</th>
                        <th class="px-4 py-3">設定ソース</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($days as $day)
                        @php
                            $date    = $day['date'];
                            $setting = $day['setting'];
                            $source  = $day['source'];
                            $isToday = $date->isToday();
                            $isSun   = $date->dayOfWeek === 0;
                            $isSat   = $date->dayOfWeek === 6;

                            $rowBg = match(true) {
                                $setting === null      => 'bg-slate-50',
                                $setting->is_closed    => 'bg-rose-50',
                                default                => 'bg-white',
                            };
                        @endphp
                        <tr class="{{ $rowBg }} {{ $isToday ? 'outline outline-2 outline-sky-400 outline-offset-[-2px]' : '' }} transition-colors hover:brightness-95">
                            <td class="px-4 py-2.5">
                                <span class="font-mono text-slate-800">{{ $date->format('m/d') }}</span>
                                @if($isToday)
                                    <span class="ml-1.5 rounded bg-sky-500 px-1.5 py-0.5 text-xs font-bold text-white">今日</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 font-semibold
                                {{ $isSun ? 'text-rose-600' : ($isSat ? 'text-blue-600' : 'text-slate-700') }}">
                                {{ ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek] }}
                            </td>
                            <td class="px-4 py-2.5 font-mono">
                                @if($setting === null)
                                    <span class="text-slate-400">― 未設定 ―</span>
                                @elseif($setting->is_closed)
                                    <span class="font-semibold text-rose-600">休業</span>
                                @else
                                    <span class="text-slate-800">
                                        {{ \Carbon\Carbon::parse($setting->open_time)->format('H:i') }}
                                        <span class="text-slate-400">〜</span>
                                        {{ \Carbon\Carbon::parse($setting->close_time)->format('H:i') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5">
                                @if($setting === null)
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500">
                                        未設定
                                    </span>
                                @elseif($setting->is_closed)
                                    <span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-700">
                                        休業日
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                        営業
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5">
                                @if($source === 'specific')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-violet-100 px-2.5 py-0.5 text-xs font-semibold text-violet-700">
                                        ★ 特定日設定
                                    </span>
                                @elseif($source === 'weekly')
                                    <span class="inline-flex items-center rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-medium text-sky-600">
                                        曜日デフォルト
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">―</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===== 既存Filamentテーブル ===== --}}
    <div>
        <div class="mb-3 flex items-center gap-2">
            <x-heroicon-s-list-bullet class="h-4 w-4 text-slate-400" />
            <h3 class="text-sm font-semibold text-slate-600">
                {{ \Carbon\Carbon::createFromFormat('Y-m', $this->selectedMonth)->isoFormat('YYYY年M月') }} の営業時間レコード
                <span class="ml-1 text-xs font-normal text-slate-500">（曜日デフォルト＋選択月内の特定日）</span>
            </h3>
        </div>
        {{ $this->table }}
    </div>

</x-filament-panels::page>
