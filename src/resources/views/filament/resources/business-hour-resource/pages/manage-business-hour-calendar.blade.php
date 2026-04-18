<x-filament-panels::page>
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
            <p class="text-xs text-slate-500">営業カレンダー</p>
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

    <div class="mt-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">{{ \Carbon\Carbon::createFromFormat('Y-m', $this->selectedMonth)->isoFormat('YYYY年M月') }} の予約公開設定</h3>
                <p class="text-xs text-slate-500">未設定の月は非公開です。必要な月を明示的に公開してください。</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <label class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700">
                    <input
                        type="radio"
                        value="1"
                        wire:model="monthPublication"
                        class="h-4 w-4 border-emerald-300 text-emerald-600 focus:ring-emerald-500"
                    >
                    公開
                </label>

                <label class="inline-flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700">
                    <input
                        type="radio"
                        value="0"
                        wire:model="monthPublication"
                        class="h-4 w-4 border-rose-300 text-rose-600 focus:ring-rose-500"
                    >
                    非公開
                </label>

                <x-filament::button
                    wire:click="saveMonthPublication"
                    color="primary"
                    size="sm"
                >
                    保存
                </x-filament::button>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 shadow-sm">
        <div class="mb-3 flex items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">営業時間カレンダー</h3>
                <p class="text-xs text-slate-500">日付クリックで特定日の営業時間を作成できます。イベントクリックでモーダルを開いて編集・削除できます。</p>
            </div>

            <x-filament::button
                type="button"
                color="gray"
                size="sm"
                x-on:click="window.dispatchEvent(new CustomEvent('business-hour-calendar-refresh'))"
            >
                再読み込み
            </x-filament::button>
        </div>

        <div
            id="business-hour-calendar"
            class="overflow-hidden rounded-lg border border-sky-200 bg-white"
            data-component-id="{{ $this->getId() }}"
            data-selected-month="{{ $this->selectedMonth }}"
            wire:ignore
        ></div>
    </div>

    <x-filament::modal
        id="business-hour-calendar-editor"
        width="md"
        icon="heroicon-o-clock"
        icon-color="primary"
    >
        <x-slot name="heading">
            {{ $this->calendarModalMode === 'edit' ? '特定日営業時間を編集' : '特定日営業時間を作成' }}
        </x-slot>

        <x-slot name="description">
            @if ($this->calendarModalDate)
                {{ \Carbon\Carbon::createFromFormat('Y-m-d', $this->calendarModalDate)->isoFormat('YYYY年M月D日(ddd)') }} の設定
            @else
                対象日を選択してください。
            @endif
        </x-slot>

        <div class="space-y-4 py-1">
            <label class="flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700">
                <input
                    type="checkbox"
                    wire:model="calendarModalIsClosed"
                    class="h-4 w-4 rounded border-rose-300 text-rose-600 focus:ring-rose-500"
                >
                この日を休業日にする
            </label>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <label class="text-sm">
                    <span class="mb-1 block font-semibold text-slate-700">開始時刻</span>
                    <input
                        type="time"
                        wire:model="calendarModalOpenTime"
                        step="300"
                        @disabled($this->calendarModalIsClosed)
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-400 disabled:bg-slate-100 disabled:text-slate-400"
                    >
                </label>

                <label class="text-sm">
                    <span class="mb-1 block font-semibold text-slate-700">終了時刻</span>
                    <input
                        type="time"
                        wire:model="calendarModalCloseTime"
                        step="300"
                        @disabled($this->calendarModalIsClosed)
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-400 disabled:bg-slate-100 disabled:text-slate-400"
                    >
                </label>
            </div>
        </div>

        <x-slot name="footerActions">
            @if ($this->calendarModalMode === 'edit')
                <x-filament::button
                    color="danger"
                    wire:click="deleteFromCalendarModal"
                    wire:confirm="この特定日設定を削除しますか？"
                >
                    削除
                </x-filament::button>
            @endif

            <x-filament::button
                color="primary"
                wire:click="submitCalendarModal"
            >
                保存
            </x-filament::button>

            <x-filament::button
                color="gray"
                x-on:click="$dispatch('close-modal', { id: 'business-hour-calendar-editor' })"
            >
                キャンセル
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
    <style>
        .fi-resource-business-hour-resource .fi-header {
            align-items: flex-start;
            flex-direction: column;
            gap: 0.75rem;
        }

        .fi-resource-business-hour-resource .fi-header-heading {
            white-space: nowrap;
        }

        .fi-resource-business-hour-resource .fi-header-actions {
            width: 100%;
        }

        #business-hour-calendar {
            min-height: 700px;
        }

        .fc .fc-toolbar-title {
            color: #0f172a;
            font-size: 1.05rem;
            font-weight: 700;
        }

        .fc .fc-button-primary {
            background: #0ea5e9;
            border-color: #0284c7;
        }

        .fc .fc-button-primary:hover,
        .fc .fc-button-primary:focus {
            background: #0369a1;
            border-color: #075985;
        }

        .fc .fc-daygrid-day.fc-day-today {
            background: #e0f2fe;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script>
        (function () {
            const init = () => {
                const calendarEl = document.getElementById('business-hour-calendar');

                if (!calendarEl) {
                    return;
                }

                const componentId = calendarEl.dataset.componentId;
                const livewireComponent = window.Livewire ? window.Livewire.find(componentId) : null;
                const selectedMonth = calendarEl.dataset.selectedMonth || null;

                if (!livewireComponent) {
                    return;
                }

                if (calendarEl.__businessHourCalendar) {
                    calendarEl.__businessHourCalendar.destroy();
                    calendarEl.__businessHourCalendar = null;
                }

                const calendar = new FullCalendar.Calendar(calendarEl, {
                    locale: 'ja',
                    timeZone: 'Asia/Tokyo',
                    initialView: 'dayGridMonth',
                    initialDate: selectedMonth ? (selectedMonth + '-01') : undefined,
                    firstDay: 1,
                    selectable: true,
                    editable: false,
                    height: 'auto',
                    headerToolbar: {
                        left: 'today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    events: (fetchInfo, successCallback, failureCallback) => {
                        livewireComponent
                            .call('getCalendarEvents', fetchInfo.startStr, fetchInfo.endStr)
                            .then((events) => successCallback(events))
                            .catch(() => failureCallback());
                    },
                    dateClick: (info) => {
                        livewireComponent.call('openCreateSpecificDateModal', info.dateStr);
                    },
                    eventClick: (info) => {
                        const source = info.event.extendedProps && info.event.extendedProps.source;
                        const clickedDate = info.event.extendedProps.date || info.event.startStr.slice(0, 10);

                        if (source !== 'specific') {
                            livewireComponent.call('openCreateSpecificDateModal', clickedDate);
                            return;
                        }

                        const businessHourId = Number(info.event.extendedProps.business_hour_id);

                        if (!businessHourId) {
                            return;
                        }

                        livewireComponent.call('openEditSpecificDateModal', businessHourId);
                    }
                });

                window.addEventListener('business-hour-calendar-refresh', () => calendar.refetchEvents());

                livewireComponent.on('business-hour-calendar-refresh', () => {
                    calendar.refetchEvents();
                });

                livewireComponent.on('business-hour-calendar-month-updated', (payload) => {
                    if (payload && payload.month) {
                        calendar.gotoDate(payload.month + '-01');
                    }

                    calendar.refetchEvents();
                });

                calendar.render();
                calendarEl.__businessHourCalendar = calendar;
            };

            document.addEventListener('livewire:load', init);
            document.addEventListener('livewire:navigated', init);
        })();
    </script>
@endpush
