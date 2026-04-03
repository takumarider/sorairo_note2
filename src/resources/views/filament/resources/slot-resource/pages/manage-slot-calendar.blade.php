<x-filament-panels::page>
    <div class="space-y-4">
        {{ $this->form }}

        <div class="flex items-center gap-3">
            <x-filament::button wire:click="generateDailySlots" icon="heroicon-m-sparkles">
                30分枠を自動生成
            </x-filament::button>

            <x-filament::button color="info" wire:click="generateDailySlots60" icon="heroicon-m-clock">
                60分枠を自動生成
            </x-filament::button>

            <x-filament::button color="info" wire:click="generateDailySlots90" icon="heroicon-m-clock">
                90分枠を自動生成
            </x-filament::button>

            <x-filament::button color="gray" wire:click="refreshCalendar" icon="heroicon-m-arrow-path">
                カレンダーを再読み込み
            </x-filament::button>

            <x-filament::button color="warning" wire:click="runIntegrityCheck" icon="heroicon-m-exclamation-triangle">
                整合性チェック
            </x-filament::button>

            <x-filament::button color="danger" wire:click="deleteInvalidUnreservedSlots" icon="heroicon-m-trash">
                未予約の不整合枠を削除
            </x-filament::button>
        </div>

        @if (! empty($this->integrityIssues))
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                <h3 class="text-sm font-semibold text-amber-900">整合性チェック結果</h3>
                <p class="mt-1 text-sm text-amber-800">予約済みの時間枠は削除されません。未予約のみ一括削除できます。</p>

                <div class="mt-4 space-y-2 text-sm text-slate-700">
                    @foreach ($this->integrityIssues as $issue)
                        <div class="rounded-lg border border-amber-200 bg-white px-3 py-2">
                            <div class="font-semibold text-slate-900">
                                {{ $issue['date'] }} {{ $issue['start'] }} - {{ $issue['end'] }}
                                @if ($issue['is_reserved'])
                                    <span class="ml-2 text-xs text-rose-600">予約済み</span>
                                @endif
                            </div>
                            <div class="mt-1 text-amber-900">{{ implode(' / ', $issue['reasons']) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="bg-sky-50 rounded-xl shadow-sm border border-sky-200 p-4">
            <div id="slot-calendar" class="overflow-hidden" wire:ignore></div>
        </div>
    </div>
</x-filament-panels::page>

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
    <style>
        #slot-calendar {
            min-height: 720px;
        }

        /* 水色ベース */
        .fc-theme-standard .fc-scrollgrid,
        .fc-theme-standard td,
        .fc-theme-standard th,
        .fc .fc-daygrid-day,
        .fc .fc-timegrid-slot,
        .fc .fc-timegrid-col-frame {
            background: #eff6ff;
            border-color: #bfdbfe;
        }

        .fc .fc-col-header,
        .fc .fc-col-header-cell {
            background: #dbeafe;
        }

        /* 曜日ヘッダーを目立たせる */
        .fc .fc-col-header-cell {
            background: #93c5fd;
        }

        .fc .fc-col-header-cell-cushion {
            color: #0c4a6e;
            font-weight: 700;
        }

        .fc .fc-toolbar-title,
        .fc .fc-col-header-cell-cushion,
        .fc .fc-daygrid-day-number,
        .fc .fc-timegrid-slot-label,
        .fc .fc-list-day-text,
        .fc-theme-standard th,
        .fc-theme-standard .fc-scrollgrid {
            color: #0f172a;
            font-weight: 600;
        }

        .fc .fc-timegrid-axis-cushion,
        .fc .fc-daygrid-day-top {
            color: #0f172a;
        }

        /* 日付表示を目立たせる */
        .fc .fc-daygrid-day-top {
            background: #38bdf8;
            color: #082f49;
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-flex;
            margin: 2px;
        }

        .fc .fc-daygrid-day-number {
            color: #082f49;
            font-weight: 700;
        }

        .fc .fc-toolbar-chunk .fc-button {
            background: #0ea5e9;
            border: 1px solid #0284c7;
            color: #f8fafc;
        }

        .fc .fc-toolbar-chunk .fc-button:hover,
        .fc .fc-toolbar-chunk .fc-button:focus {
            background: #0369a1;
            border-color: #075985;
            color: #ffffff;
        }

        .fc .fc-toolbar-chunk .fc-button.fc-button-active {
            background: #075985;
            border-color: #0c4a6e;
            color: #ffffff;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script>
        (function () {
            const init = () => {
                const componentId = @js($this->getId());
                const livewireComponent = window.Livewire ? window.Livewire.find(componentId) : null;
                const calendarEl = document.getElementById('slot-calendar');

                if (!livewireComponent || !calendarEl) {
                    return;
                }

                if (calendarEl.__slotCalendar) {
                    calendarEl.__slotCalendar.destroy();
                    calendarEl.__slotCalendar = null;
                }

                const getMenuId = () => livewireComponent.get('data.menu_id');

                const calendar = new FullCalendar.Calendar(calendarEl, {
                    locale: 'ja',
                    timeZone: 'local',
                    initialView: 'timeGridWeek',
                    firstDay: 1,
                    dayHeaderFormat: { weekday: 'short', month: 'numeric', day: 'numeric' },
                    dayHeaderContent: (arg) => FullCalendar.formatDate(arg.date, {
                        weekday: 'short',
                        month: 'numeric',
                        day: 'numeric'
                    }, { locale: 'ja' }),
                    titleFormat: { year: 'numeric', month: 'long', day: 'numeric' },
                    slotDuration: '00:30:00',
                    nowIndicator: true,
                    selectable: true,
                    editable: true,
                    selectMirror: true,
                    height: 'auto',
                    expandRows: true,
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    events: (fetchInfo, successCallback, failureCallback) => {
                        livewireComponent
                            .call('getCalendarEvents', fetchInfo.startStr, fetchInfo.endStr, getMenuId())
                            .then((events) => successCallback(events))
                            .catch(() => failureCallback());
                    },
                    select: (info) => {
                        const menuId = getMenuId();

                        if (!menuId) {
                            calendar.unselect();
                            return;
                        }

                        livewireComponent
                            .call('createSlotFromCalendar', menuId, info.startStr, info.endStr)
                            .then(() => calendar.refetchEvents())
                            .finally(() => calendar.unselect());
                    },
                    eventDrop: (info) => {
                        livewireComponent
                            .call('updateSlotFromCalendar', Number(info.event.id), info.event.startStr, info.event.endStr)
                            .then(() => calendar.refetchEvents())
                            .catch(() => calendar.refetchEvents());
                    },
                    eventResize: (info) => {
                        livewireComponent
                            .call('updateSlotFromCalendar', Number(info.event.id), info.event.startStr, info.event.endStr)
                            .then(() => calendar.refetchEvents())
                            .catch(() => calendar.refetchEvents());
                    },
                    eventClick: (info) => {
                        const isReserved = info.event.extendedProps && info.event.extendedProps.is_reserved;

                        if (isReserved) {
                            return;
                        }

                        if (window.confirm('この時間枠を削除しますか？')) {
                            livewireComponent
                                .call('deleteSlotFromCalendar', Number(info.event.id))
                                .then(() => calendar.refetchEvents())
                                .catch(() => calendar.refetchEvents());
                        }
                    }
                });

                window.addEventListener('slot-calendar-refresh', () => calendar.refetchEvents());

                calendar.render();

                calendarEl.__slotCalendar = calendar;
            };

            document.addEventListener('livewire:load', init);
            document.addEventListener('livewire:navigated', init);
        })();
    </script>
@endpush