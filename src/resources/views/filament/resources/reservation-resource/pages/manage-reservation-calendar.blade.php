<x-filament-panels::page>
    <div class="space-y-3">
        <section class="rounded-2xl border border-sky-200 bg-gradient-to-r from-sky-50 via-white to-cyan-50 p-3 shadow-sm sm:p-4">
            <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <h2 class="text-lg font-semibold tracking-tight text-slate-900">予約状況カレンダー</h2>
                    <div class="mt-1 inline-flex w-fit items-center gap-2 rounded-lg border border-sky-200 bg-white px-2.5 py-1 text-xs font-medium text-sky-800 shadow-sm">
                        <span class="h-2 w-2 rounded-full bg-sky-500"></span>
                        <span id="calendar-current-range">{{ now('Asia/Tokyo')->startOfWeek()->format('Y年n月j日') }} - {{ now('Asia/Tokyo')->startOfWeek()->addDays(6)->format('n月j日') }}</span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-1.5 text-xs font-semibold text-slate-700">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-teal-200 bg-teal-50 px-2.5 py-1">
                        <span class="h-2 w-2 rounded-full bg-teal-700"></span>
                        確定
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1">
                        <span class="h-2 w-2 rounded-full bg-amber-700"></span>
                        完了
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1">
                        <span class="h-2 w-2 rounded-full bg-rose-700"></span>
                        キャンセル
                    </span>
                </div>
            </div>
        </section>

        <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
            <div id="reservation-calendar" class="overflow-hidden" data-component-id="{{ $this->getId() }}" wire:ignore></div>
        </div>
    </div>

    <x-filament::modal
        id="reservation-calendar-detail"
        width="4xl"
        icon="heroicon-o-ticket"
        icon-color="info"
        sticky-header
    >
        <x-slot name="heading">
            予約詳細
        </x-slot>

        <x-slot name="description">
            選択した予約者情報とメニュー内容を確認できます。
        </x-slot>

        @if ($selectedReservation)
            <div class="reservation-modal space-y-4">
                <section class="reservation-modal__hero">
                    <div class="reservation-modal__hero-header">
                        <div class="reservation-modal__eyebrow">Reservation</div>
                        <span class="reservation-modal__status {{ $selectedReservation['status_badge_class'] }}">
                            {{ $selectedReservation['status_label'] }}
                        </span>
                    </div>

                    <div class="reservation-modal__hero-main">
                        <div>
                            <p class="reservation-modal__hero-number">#{{ $selectedReservation['number'] }}</p>
                            <div class="reservation-modal__hero-meta">
                                <span class="reservation-modal__date-pill">{{ $selectedReservation['date_label'] }}</span>
                                <span class="reservation-modal__time">{{ $selectedReservation['time_label'] }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="reservation-modal__stats">
                        <article class="reservation-modal__stat-card">
                            <p class="reservation-modal__label">予約者</p>
                            <p class="reservation-modal__value truncate">{{ $selectedReservation['customer_name'] }}</p>
                        </article>
                        <article class="reservation-modal__stat-card">
                            <p class="reservation-modal__label">合計料金</p>
                            <p class="reservation-modal__value">{{ $selectedReservation['total_price_label'] }}</p>
                        </article>
                        <article class="reservation-modal__stat-card">
                            <p class="reservation-modal__label">総所要時間</p>
                            <p class="reservation-modal__value">{{ $selectedReservation['total_duration_label'] }}</p>
                        </article>
                    </div>
                </section>

                <div class="reservation-modal__grid">
                    <section class="reservation-modal__panel">
                        <div class="reservation-modal__panel-header">
                            <h3 class="reservation-modal__panel-title">予約者情報</h3>
                            <span class="reservation-modal__tag reservation-modal__tag--sky">顧客カード</span>
                        </div>

                        <div class="reservation-modal__stack">
                            <div class="reservation-modal__info-card">
                                <p class="reservation-modal__label">氏名</p>
                                <p class="reservation-modal__value reservation-modal__value--lg">{{ $selectedReservation['customer_name'] }}</p>
                            </div>

                            <div class="reservation-modal__info-card">
                                <p class="reservation-modal__label">メールアドレス</p>
                                <p class="reservation-modal__subvalue break-all">{{ $selectedReservation['customer_email'] }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="reservation-modal__panel">
                        <div class="reservation-modal__panel-header">
                            <h3 class="reservation-modal__panel-title">メニュー情報</h3>
                            <span class="reservation-modal__tag reservation-modal__tag--cyan">メインメニュー</span>
                        </div>

                        <div class="reservation-modal__menu-card">
                            <p class="reservation-modal__menu-name">{{ $selectedReservation['menu_name'] }}</p>

                            <div class="reservation-modal__mini-grid">
                                <div class="reservation-modal__mini-card">
                                    <p class="reservation-modal__label">料金</p>
                                    <p class="reservation-modal__value">{{ $selectedReservation['menu_price_label'] }}</p>
                                </div>
                                <div class="reservation-modal__mini-card">
                                    <p class="reservation-modal__label">所要時間</p>
                                    <p class="reservation-modal__value">{{ $selectedReservation['menu_duration_label'] }}</p>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <section class="reservation-modal__panel">
                    <div class="reservation-modal__panel-header reservation-modal__panel-header--wrap">
                        <div>
                            <h3 class="reservation-modal__panel-title">追加オプション</h3>
                            <p class="reservation-modal__panel-copy">選択されているオプションをカードで表示します。</p>
                        </div>
                        <div class="reservation-modal__chip-group">
                            <span class="reservation-modal__chip">追加料金 {{ $selectedReservation['option_total_price_label'] }}</span>
                            <span class="reservation-modal__chip">追加時間 {{ $selectedReservation['option_total_duration_label'] }}</span>
                        </div>
                    </div>

                    @if (count($selectedReservation['options']))
                        <div class="reservation-modal__option-grid">
                            @foreach ($selectedReservation['options'] as $option)
                                <article class="reservation-modal__option-card">
                                    <div class="reservation-modal__option-header">
                                        <div class="min-w-0">
                                            <p class="reservation-modal__option-title">{{ $option['name'] }}</p>
                                            <p class="reservation-modal__option-copy">option</p>
                                        </div>
                                        <span class="reservation-modal__tag reservation-modal__tag--teal">追加</span>
                                    </div>

                                    <div class="reservation-modal__chip-group reservation-modal__chip-group--left">
                                        <span class="reservation-modal__chip reservation-modal__chip--white">{{ $option['price_label'] }}</span>
                                        <span class="reservation-modal__chip reservation-modal__chip--white">{{ $option['duration_label'] }}</span>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="reservation-modal__empty">
                            追加オプションはありません。
                        </div>
                    @endif
                </section>
            </div>
        @else
            <p class="text-sm text-slate-500">予約を選択すると詳細を表示します。</p>
        @endif

        <x-slot name="footer">
            <div class="flex justify-end">
                <x-filament::button color="gray" x-on:click="$dispatch('close-modal', { id: 'reservation-calendar-detail' })">
                    閉じる
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
    <style>
        .reservation-modal {
            --rm-sky-50: #f0f9ff;
            --rm-sky-100: #e0f2fe;
            --rm-sky-200: #bae6fd;
            --rm-sky-300: #7dd3fc;
            --rm-sky-700: #0369a1;
            --rm-sky-800: #075985;
            --rm-cyan-700: #0e7490;
            --rm-slate-50: #f8fafc;
            --rm-slate-100: #f1f5f9;
            --rm-slate-200: #e2e8f0;
            --rm-slate-400: #94a3b8;
            --rm-slate-500: #64748b;
            --rm-slate-700: #334155;
            --rm-slate-900: #0f172a;
        }

        .reservation-modal__hero {
            overflow: hidden;
            border: 1px solid var(--rm-sky-200);
            border-radius: 1.5rem;
            background: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 45%, #ecfeff 100%);
            box-shadow: 0 10px 30px rgba(14, 116, 144, 0.08);
        }

        .reservation-modal__hero-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 1.1rem 1.25rem 0;
        }

        .reservation-modal__eyebrow {
            color: var(--rm-sky-700);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.22em;
            text-transform: uppercase;
        }

        .reservation-modal__hero-main {
            padding: 0.6rem 1.25rem 0;
        }

        .reservation-modal__hero-number {
            color: var(--rm-slate-900);
            font-size: 1.45rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .reservation-modal__hero-meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem 0.75rem;
            margin-top: 0.65rem;
        }

        .reservation-modal__date-pill,
        .reservation-modal__time {
            display: inline-flex;
            align-items: center;
            min-height: 2rem;
            border-radius: 9999px;
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .reservation-modal__date-pill {
            background: rgba(255, 255, 255, 0.86);
            color: var(--rm-sky-800);
            border: 1px solid var(--rm-sky-200);
        }

        .reservation-modal__time {
            background: rgba(186, 230, 253, 0.55);
            color: var(--rm-cyan-700);
            border: 1px solid rgba(125, 211, 252, 0.8);
        }

        .reservation-modal__status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.25rem;
            border-radius: 9999px;
            padding: 0.45rem 0.9rem;
            font-size: 0.82rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .reservation-modal__stats {
            display: grid;
            gap: 0.75rem;
            padding: 1rem 1.25rem 1.25rem;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .reservation-modal__stat-card,
        .reservation-modal__info-card,
        .reservation-modal__mini-card {
            border: 1px solid var(--rm-slate-200);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.88);
            padding: 0.9rem 1rem;
        }

        .reservation-modal__grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
        }

        .reservation-modal__panel {
            border: 1px solid var(--rm-slate-200);
            border-radius: 1.5rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            padding: 1rem;
            box-shadow: 0 8px 24px rgba(14, 165, 233, 0.05);
        }

        .reservation-modal__panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .reservation-modal__panel-header--wrap {
            align-items: flex-start;
        }

        .reservation-modal__panel-title {
            color: var(--rm-sky-800);
            font-size: 0.9rem;
            font-weight: 700;
            line-height: 1.4;
        }

        .reservation-modal__panel-copy,
        .reservation-modal__option-copy,
        .reservation-modal__subvalue {
            color: var(--rm-slate-500);
            font-size: 0.82rem;
            line-height: 1.55;
        }

        .reservation-modal__stack {
            display: grid;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .reservation-modal__label {
            color: var(--rm-sky-700);
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            line-height: 1.4;
            text-transform: uppercase;
        }

        .reservation-modal__value {
            color: var(--rm-slate-900);
            font-size: 0.96rem;
            font-weight: 700;
            line-height: 1.45;
            margin-top: 0.28rem;
        }

        .reservation-modal__value--lg,
        .reservation-modal__menu-name,
        .reservation-modal__option-title {
            color: var(--rm-slate-900);
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.45;
        }

        .reservation-modal__menu-card {
            margin-top: 1rem;
            border: 1px solid var(--rm-sky-100);
            border-radius: 1.1rem;
            background: linear-gradient(180deg, var(--rm-sky-50) 0%, #ffffff 100%);
            padding: 1rem;
        }

        .reservation-modal__mini-grid {
            display: grid;
            gap: 0.65rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-top: 0.85rem;
        }

        .reservation-modal__chip-group {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 0.5rem;
        }

        .reservation-modal__chip-group--left {
            justify-content: flex-start;
            margin-top: 0.85rem;
        }

        .reservation-modal__chip {
            display: inline-flex;
            align-items: center;
            min-height: 1.95rem;
            border: 1px solid var(--rm-sky-200);
            border-radius: 9999px;
            background: var(--rm-sky-50);
            color: var(--rm-sky-800);
            padding: 0.35rem 0.8rem;
            font-size: 0.76rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .reservation-modal__chip--white {
            background: #ffffff;
            border-color: var(--rm-slate-200);
            color: var(--rm-slate-700);
        }

        .reservation-modal__tag {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 1.8rem;
            border-radius: 9999px;
            padding: 0.3rem 0.75rem;
            font-size: 0.72rem;
            font-weight: 700;
            line-height: 1.2;
            white-space: nowrap;
        }

        .reservation-modal__tag--sky {
            background: #e0f2fe;
            color: #075985;
            border: 1px solid #bae6fd;
        }

        .reservation-modal__tag--cyan {
            background: #ecfeff;
            color: #0e7490;
            border: 1px solid #a5f3fc;
        }

        .reservation-modal__tag--teal {
            background: #ccfbf1;
            color: #0f766e;
            border: 1px solid #99f6e4;
        }

        .reservation-modal__option-grid {
            display: grid;
            gap: 0.75rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-top: 1rem;
        }

        .reservation-modal__option-card {
            border: 1px solid var(--rm-sky-100);
            border-radius: 1.15rem;
            background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 100%);
            padding: 1rem;
        }

        .reservation-modal__option-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .reservation-modal__empty {
            margin-top: 1rem;
            border: 1px dashed var(--rm-slate-300);
            border-radius: 1rem;
            background: linear-gradient(180deg, #f8fafc 0%, #f0f9ff 100%);
            color: var(--rm-slate-500);
            font-size: 0.9rem;
            line-height: 1.5;
            padding: 1rem;
            text-align: center;
        }

        #reservation-calendar {
            min-height: 760px;
        }

        .fi-modal-content .reservation-modal .bg-teal-50.text-teal-700.ring-teal-200,
        .fi-modal-content .reservation-modal .bg-amber-50.text-amber-700.ring-amber-200,
        .fi-modal-content .reservation-modal .bg-rose-50.text-rose-700.ring-rose-200,
        .fi-modal-content .reservation-modal .bg-slate-100.text-slate-700.ring-slate-200 {
            box-shadow: none;
        }

        .fc-theme-standard .fc-scrollgrid,
        .fc-theme-standard td,
        .fc-theme-standard th,
        .fc .fc-daygrid-day,
        .fc .fc-timegrid-slot,
        .fc .fc-timegrid-col-frame {
            border-color: #e2e8f0;
        }

        .fc .fc-col-header,
        .fc .fc-col-header-cell {
            background: #f8fafc;
        }

        .fc .fc-toolbar-title,
        .fc .fc-col-header-cell-cushion,
        .fc .fc-daygrid-day-number,
        .fc .fc-timegrid-slot-label-cushion {
            color: #0f172a;
            font-weight: 700;
        }

        .fc .fc-button {
            background: #f59e0b;
            border: 1px solid #d97706;
            color: #fff;
            box-shadow: none;
        }

        .fc .fc-button:hover,
        .fc .fc-button:focus {
            background: #b45309;
            border-color: #92400e;
            color: #fff;
        }

        .fc .fc-button.fc-button-active {
            background: #0f766e;
            border-color: #115e59;
            color: #fff;
        }

        .fc .fc-event {
            border-radius: 10px;
            padding: 2px 4px;
            font-weight: 600;
        }

        .fc .fc-timegrid-event-harness .fc-event-main,
        .fc .fc-daygrid-event .fc-event-main {
            padding: 2px 4px;
        }

        @media (max-width: 768px) {
            .reservation-modal__hero-header,
            .reservation-modal__panel-header,
            .reservation-modal__panel-header--wrap,
            .reservation-modal__option-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .reservation-modal__stats,
            .reservation-modal__grid,
            .reservation-modal__mini-grid,
            .reservation-modal__option-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .reservation-modal__chip-group {
                justify-content: flex-start;
            }

            #reservation-calendar {
                min-height: 620px;
            }

            .fc .fc-toolbar {
                gap: 0.75rem;
            }

            .fc .fc-toolbar.fc-header-toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .fc .fc-toolbar-chunk {
                display: flex;
                justify-content: center;
            }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script>
        (function () {
            const init = () => {
                const calendarEl = document.getElementById('reservation-calendar');

                if (! calendarEl) {
                    return;
                }

                const componentId = calendarEl.dataset.componentId;
                const livewireComponent = window.Livewire ? window.Livewire.find(componentId) : null;

                if (! livewireComponent) {
                    return;
                }

                if (calendarEl.__reservationCalendar) {
                    calendarEl.__reservationCalendar.destroy();
                    calendarEl.__reservationCalendar = null;
                }

                const rangeEl = document.getElementById('calendar-current-range');
                const formatJaDate = (date) => {
                    const year = date.getFullYear();
                    const month = date.getMonth() + 1;
                    const day = date.getDate();

                    return `${year}年${month}月${day}日`;
                };

                const updateRangeLabel = (start, endExclusive) => {
                    if (!rangeEl || !start || !endExclusive) {
                        return;
                    }

                    const startDate = new Date(start);
                    const endDate = new Date(endExclusive);
                    endDate.setDate(endDate.getDate() - 1);

                    rangeEl.textContent = `${formatJaDate(startDate)} - ${formatJaDate(endDate)}`;
                };

                const calendar = new FullCalendar.Calendar(calendarEl, {
                    locale: 'ja',
                    initialView: 'timeGridWeek',
                    firstDay: 0,
                    nowIndicator: true,
                    height: 'auto',
                    expandRows: true,
                    dayMaxEvents: true,
                    slotMinTime: '08:00:00',
                    slotMaxTime: '22:00:00',
                    allDaySlot: false,
                    eventTimeFormat: {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false,
                    },
                    buttonText: {
                        today: '今日',
                        month: '月',
                        week: '週',
                        day: '日',
                    },
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay',
                    },
                    events: (fetchInfo, successCallback, failureCallback) => {
                        livewireComponent
                            .call('getCalendarEvents', fetchInfo.startStr, fetchInfo.endStr)
                            .then((events) => successCallback(events))
                            .catch(() => failureCallback());
                    },
                    eventClick: (info) => {
                        livewireComponent.call('openReservationModal', Number(info.event.id));
                    },
                    datesSet: (info) => {
                        updateRangeLabel(info.start, info.end);
                    },
                });

                calendar.render();
                calendarEl.__reservationCalendar = calendar;
            };

            document.addEventListener('livewire:load', init);
            document.addEventListener('livewire:navigated', init);
        })();
    </script>
@endpush