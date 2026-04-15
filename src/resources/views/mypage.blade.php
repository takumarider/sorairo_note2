<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-sky-900 leading-tight">
            マイページ
        </h2>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-slate-900">あなたの予約</h3>
                <a href="{{ route('menus.index') }}" class="text-sky-600 hover:text-sky-700 text-sm font-semibold">新しく予約する</a>
            </div>

            @if(session('success'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @php
                $reservationPayload = $reservations->map(function ($reservation) {
                    return [
                        'id' => $reservation->id,
                        'number' => str_pad((string) $reservation->id, 6, '0', STR_PAD_LEFT),
                        'date' => $reservation->date->format('Y-m-d'),
                        'date_label' => $reservation->date->isoFormat('Y年M月D日(ddd)'),
                        'time_label' => $reservation->start_time->format('H:i').' - '.$reservation->end_time->format('H:i'),
                        'menu_name' => $reservation->menu->name,
                        'price_label' => '¥'.number_format($reservation->menu->price),
                        'cancel_url' => route('reservations.cancel', $reservation),
                        'api_cancel_url' => url('/api/reservations/'.$reservation->id),
                    ];
                })->values();
            @endphp

            <div class="bg-white shadow-sm rounded-2xl p-4 sm:p-6">
                @if($reservations->count() > 0)
                    <div id="mypage-calendar-data"
                         data-reservations='@json($reservationPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE)'
                         data-csrf='{{ csrf_token() }}'
                        data-events-url='{{ url('/api/reservations/events') }}'
                         class="hidden"></div>

                    <div class="mb-4 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900">
                        カレンダーの日付または予約イベントを選ぶと、右側に予約詳細を表示します。
                    </div>

                    <div class="grid gap-4 lg:grid-cols-5">
                        <div class="lg:col-span-3 overflow-x-auto rounded-2xl border border-slate-200 p-3 sm:p-4">
                            <div id="mypage-calendar"></div>
                        </div>

                        <div class="lg:col-span-2 space-y-4">
                            <div class="rounded-2xl border border-slate-200 p-4">
                                <h5 class="text-sm font-semibold text-slate-600">選択した日</h5>
                                <p id="selected-date-label" class="mt-1 text-lg font-bold text-slate-900">日付未選択</p>

                                <div id="daily-reservations" class="mt-3 space-y-2">
                                    <p class="text-sm text-slate-500">日付を選択してください。</p>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 p-4">
                                <h5 class="text-sm font-semibold text-slate-600">予約詳細</h5>
                                <div id="reservation-detail" class="mt-3 text-sm text-slate-500">
                                    左の一覧から予約を選択すると詳細を表示します。
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="py-8 text-center text-slate-500">
                        未来の予約はありません。
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($reservations->count() > 0)
        <style>
            #mypage-calendar {
                min-width: 320px;
            }

            #mypage-calendar .fc {
                font-size: 0.82rem;
            }

            #mypage-calendar .fc-toolbar {
                gap: 0.5rem;
                margin-bottom: 0.75rem;
            }

            #mypage-calendar .fc-toolbar-title {
                font-size: 1rem;
                font-weight: 700;
                line-height: 1.2;
                white-space: nowrap;
            }

            #mypage-calendar .fc-button {
                padding: 0.35rem 0.65rem;
                font-size: 0.72rem;
                line-height: 1.2;
                white-space: nowrap;
            }

            #mypage-calendar .fc-col-header-cell-cushion,
            #mypage-calendar .fc-daygrid-day-number {
                padding: 0.25rem;
                font-size: 0.72rem;
                white-space: nowrap;
            }

            #mypage-calendar .fc-daygrid-day-frame {
                min-height: 5rem;
            }

            #mypage-calendar .fc-daygrid-event {
                margin-top: 0.15rem;
                border-radius: 0.5rem;
                padding: 0.1rem 0.35rem;
            }

            #mypage-calendar .fc-event-main {
                overflow: hidden;
            }

            #mypage-calendar .fc-event-title {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            #mypage-calendar .fc-daygrid-more-link {
                font-size: 0.68rem;
                white-space: nowrap;
            }

            .mypage-calendar-event {
                display: flex;
                align-items: center;
                gap: 0.3rem;
                min-width: 0;
                white-space: nowrap;
                overflow: hidden;
            }

            .mypage-calendar-event__time {
                flex-shrink: 0;
                font-size: 0.64rem;
                font-weight: 700;
                letter-spacing: 0.01em;
                opacity: 0.95;
            }

            .mypage-calendar-event__menu {
                min-width: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                font-size: 0.68rem;
                font-weight: 600;
            }

            @media (max-width: 640px) {
                #mypage-calendar {
                    min-width: 560px;
                }

                #mypage-calendar .fc {
                    font-size: 0.76rem;
                }

                #mypage-calendar .fc-toolbar {
                    flex-wrap: nowrap;
                    align-items: center;
                }

                #mypage-calendar .fc-toolbar-title {
                    font-size: 0.9rem;
                }

                #mypage-calendar .fc-button {
                    padding: 0.28rem 0.5rem;
                    font-size: 0.66rem;
                }

                #mypage-calendar .fc-daygrid-day-frame {
                    min-height: 4.6rem;
                }

                #mypage-calendar .fc-daygrid-event {
                    padding-inline: 0.25rem;
                }
            }
        </style>
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales-all.global.min.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const dataNode = document.getElementById('mypage-calendar-data');
                if (!dataNode) {
                    return;
                }

                let reservations = JSON.parse(dataNode.dataset.reservations || '[]');
                const csrfToken = dataNode.dataset.csrf || '';
                const eventsUrl = dataNode.dataset.eventsUrl || '';

                const selectedDateLabel = $('#selected-date-label');
                const dailyReservations = $('#daily-reservations');
                const reservationDetail = $('#reservation-detail');

                let selectedDate = reservations.length > 0 ? reservations[0].date : null;
                let selectedReservationId = reservations.length > 0 ? reservations[0].id : null;
                let calendar = null;

                const loadReservations = async () => {
                    if (!eventsUrl) {
                        return;
                    }

                    const response = await fetch(eventsUrl, {
                        method: 'GET',
                        headers: {
                            Accept: 'application/json',
                        },
                        credentials: 'same-origin',
                    });

                    if (!response.ok) {
                        throw new Error('予約データの取得に失敗しました。');
                    }

                    const payload = await response.json();
                    reservations = Array.isArray(payload.reservations) ? payload.reservations : [];

                    if (!selectedDate && reservations.length > 0) {
                        selectedDate = reservations[0].date;
                    }
                };

                const formatDateLabel = (dateStr) => {
                    const date = new Date(dateStr + 'T00:00:00');
                    return date.toLocaleDateString('ja-JP', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        weekday: 'short',
                    });
                };

                const renderDetail = (reservation) => {
                    if (!reservation) {
                        reservationDetail.html('<p class="text-sm text-slate-500">左の一覧から予約を選択すると詳細を表示します。</p>');
                        return;
                    }

                    const wrapper = $('<div>').addClass('space-y-3');
                    wrapper.append(
                        $('<p>').addClass('text-sm text-slate-500').html('予約番号 <span class="font-semibold text-slate-900">#' + reservation.number + '</span>')
                    );
                    wrapper.append($('<p>').addClass('text-lg font-bold text-slate-900').text(reservation.menu_name));
                    wrapper.append($('<p>').addClass('text-sm text-slate-700').text(reservation.date_label));
                    wrapper.append($('<p>').addClass('text-sm text-slate-700').text(reservation.time_label));
                    wrapper.append($('<p>').addClass('text-sm font-semibold text-sky-700').text(reservation.price_label));

                    const form = $('<form>', {
                        method: 'POST',
                        action: reservation.cancel_url,
                        class: 'mt-4',
                    });

                    form.append($('<input>', { type: 'hidden', name: '_token', value: csrfToken }));
                    form.append($('<input>', { type: 'hidden', name: '_method', value: 'DELETE' }));
                    form.append(
                        $('<p>', {
                            class: 'mb-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700',
                            text: 'キャンセルする場合は下の赤いボタンを押してください。',
                        })
                    );
                    form.append(
                        $('<button>', {
                            type: 'submit',
                            id: 'cancel-button-' + reservation.id,
                            class: 'w-full rounded-xl border-2 border-rose-800 bg-rose-600 px-4 py-3 text-base font-bold text-white shadow-lg hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-300 focus:ring-offset-2',
                            style: 'background-color:#e11d48;color:#ffffff;',
                            text: 'この予約をキャンセル',
                        })
                    );

                    form.on('submit', async function (event) {
                        event.preventDefault();

                        if (!window.confirm('この予約をキャンセルしますか？')) {
                            return;
                        }

                        const submitButton = $('#cancel-button-' + reservation.id);
                        submitButton.prop('disabled', true).text('キャンセル中...');

                        try {
                            const response = await fetch(reservation.api_cancel_url, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    Accept: 'application/json',
                                },
                                credentials: 'same-origin',
                            });

                            const payload = await response.json();
                            if (!response.ok || !payload.success) {
                                throw new Error(payload.message || 'キャンセルに失敗しました。');
                            }

                            await loadReservations();

                            if (calendar) {
                                calendar.refetchEvents();
                            }

                            if (selectedDate) {
                                renderReservationList(selectedDate);
                            }
                        } catch (error) {
                            window.alert(error instanceof Error ? error.message : 'キャンセルに失敗しました。');
                        } finally {
                            submitButton.prop('disabled', false).text('この予約をキャンセル');
                        }
                    });

                    wrapper.append(form);
                    reservationDetail.empty().append(wrapper);
                };

                const renderReservationList = (dateStr) => {
                    selectedDate = dateStr;
                    selectedDateLabel.text(formatDateLabel(dateStr));

                    const matched = reservations.filter((reservation) => reservation.date === dateStr);
                    dailyReservations.empty();

                    if (matched.length === 0) {
                        dailyReservations.append('<p class="text-sm text-slate-500">この日に予約はありません。</p>');
                        selectedReservationId = null;
                        renderDetail(null);
                        return;
                    }

                    matched.forEach((reservation) => {
                        const isActive = selectedReservationId === reservation.id;
                        const btn = $('<button>', {
                            type: 'button',
                            class: 'w-full rounded-xl border px-3 py-2 text-left transition ' +
                                (isActive ? 'border-sky-500 bg-sky-50' : 'border-slate-200 bg-white hover:border-sky-300'),
                        });

                        btn.append($('<p>').addClass('text-sm font-bold text-slate-900').text(reservation.time_label));
                        btn.append($('<p>').addClass('text-xs text-slate-600').text(reservation.menu_name));
                        btn.on('click', function () {
                            selectedReservationId = reservation.id;
                            renderReservationList(dateStr);
                        });

                        dailyReservations.append(btn);
                    });

                    const selected = matched.find((reservation) => reservation.id === selectedReservationId) || matched[0];
                    selectedReservationId = selected.id;
                    renderDetail(selected);
                };

                calendar = new FullCalendar.Calendar(document.getElementById('mypage-calendar'), {
                    locale: 'ja',
                    initialView: 'dayGridMonth',
                    firstDay: 0,
                    height: 'auto',
                    dayMaxEvents: true,
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: '',
                    },
                    buttonText: {
                        today: '今月',
                    },
                    eventColor: '#0ea5e9',
                    events: function (_info, successCallback, failureCallback) {
                        try {
                            const calendarEvents = reservations.map((reservation) => ({
                                id: String(reservation.id),
                                title: reservation.menu_name,
                                start: reservation.date,
                                allDay: true,
                                extendedProps: {
                                    reservationId: reservation.id,
                                    timeLabel: reservation.time_label,
                                    menuName: reservation.menu_name,
                                },
                            }));

                            successCallback(calendarEvents);
                        } catch (_error) {
                            failureCallback();
                        }
                    },
                    eventContent: function (arg) {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'mypage-calendar-event';

                        const time = document.createElement('span');
                        time.className = 'mypage-calendar-event__time';
                        time.textContent = arg.event.extendedProps.timeLabel;

                        const menu = document.createElement('span');
                        menu.className = 'mypage-calendar-event__menu';
                        menu.textContent = arg.event.extendedProps.menuName;

                        wrapper.appendChild(time);
                        wrapper.appendChild(menu);

                        return { domNodes: [wrapper] };
                    },
                    dateClick: function (info) {
                        selectedReservationId = null;
                        renderReservationList(info.dateStr);
                    },
                    eventClick: function (info) {
                        const reservationId = Number(info.event.extendedProps.reservationId);
                        selectedReservationId = reservationId;
                        renderReservationList(info.event.startStr.slice(0, 10));
                    },
                });

                calendar.render();

                loadReservations()
                    .then(() => {
                        if (calendar) {
                            calendar.refetchEvents();
                        }

                        if (selectedDate) {
                            renderReservationList(selectedDate);
                        }
                    })
                    .catch(() => {
                        if (selectedDate) {
                            renderReservationList(selectedDate);
                        }
                    });
            });
        </script>
    @endif
</x-app-layout>
