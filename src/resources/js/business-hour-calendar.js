import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import interactionPlugin from "@fullcalendar/interaction";
import jaLocale from "@fullcalendar/core/locales/ja";
import timeGridPlugin from "@fullcalendar/timegrid";

(function () {
    const init = () => {
        const calendarEl = document.getElementById("business-hour-calendar");

        if (!calendarEl) {
            return;
        }

        const componentId = calendarEl.dataset.componentId;
        const livewireComponent = window.Livewire
            ? window.Livewire.find(componentId)
            : null;
        const selectedMonth = calendarEl.dataset.selectedMonth || null;

        if (!livewireComponent) {
            return;
        }

        if (calendarEl.__businessHourCalendar) {
            calendarEl.__businessHourCalendar.destroy();
            calendarEl.__businessHourCalendar = null;
        }

        const calendar = new Calendar(calendarEl, {
            plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
            locale: jaLocale,
            timeZone: "Asia/Tokyo",
            initialView: "dayGridMonth",
            initialDate: selectedMonth ? selectedMonth + "-01" : undefined,
            firstDay: 1,
            selectable: true,
            editable: false,
            height: "auto",
            headerToolbar: {
                left: "today",
                center: "title",
                right: "dayGridMonth,timeGridWeek,timeGridDay",
            },
            events: (fetchInfo, successCallback, failureCallback) => {
                livewireComponent
                    .call(
                        "getCalendarEvents",
                        fetchInfo.startStr,
                        fetchInfo.endStr,
                    )
                    .then((events) => successCallback(events))
                    .catch(() => failureCallback());
            },
            dateClick: (info) => {
                livewireComponent.call(
                    "openCreateSpecificDateModal",
                    info.dateStr,
                );
            },
            eventClick: (info) => {
                const source =
                    info.event.extendedProps && info.event.extendedProps.source;
                const clickedDate =
                    info.event.extendedProps.date ||
                    info.event.startStr.slice(0, 10);

                if (source !== "specific") {
                    livewireComponent.call(
                        "openCreateSpecificDateModal",
                        clickedDate,
                    );
                    return;
                }

                const businessHourId = Number(
                    info.event.extendedProps.business_hour_id,
                );

                if (!businessHourId) {
                    return;
                }

                livewireComponent.call(
                    "openEditSpecificDateModal",
                    businessHourId,
                );
            },
        });

        window.addEventListener("business-hour-calendar-refresh", () =>
            calendar.refetchEvents(),
        );

        livewireComponent.on("business-hour-calendar-refresh", () => {
            calendar.refetchEvents();
        });

        livewireComponent.on(
            "business-hour-calendar-month-updated",
            (payload) => {
                if (payload && payload.month) {
                    calendar.gotoDate(payload.month + "-01");
                }

                calendar.refetchEvents();
            },
        );

        calendar.render();
        calendarEl.__businessHourCalendar = calendar;
    };

    document.addEventListener("livewire:load", init);
    document.addEventListener("livewire:navigated", init);
})();
