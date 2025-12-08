@include('layouts._partials.messages') 
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Citas') }}
        </h2>

        {{-- FullCalendar --}}
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar/index.global.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales-all.min.js"></script>

        <style>
            body {
                background-color: #ffffff !important;
                color: #333;
            }

            /* Contenedor del calendario */
            #calendar {
                overflow-x: auto;
            }

            .fc {
                background-color: #fff;
                border-radius: 8px;
            }

            .fc-scrollgrid,
            .fc-scrollgrid-section,
            .fc-scrollgrid-sync-table {
                border-color: #b0b0b0 !important;
            }

            .fc-daygrid-day-frame {
                border-color: #b0b0b0 !important;
            }

            .fc-daygrid-day-number {
                color: #333 !important;
                font-weight: 500;
            }

            .fc-day-today {
                background-color: rgba(17, 140, 255, 0.23) !important;
            }

            .fc-event.Sala-1 {
                background-color: rgba(91, 101, 255, 0.59);
                border-color: rgba(35, 31, 255, 0.7);
                color: white;
            }

            .fc-event.Sala-2 {
                background-color: #18ffcde3;
                border-color: #33ffc2ff;
                color: white;
            }

            .fc-event.Sala-3 {
                background-color: #FF47F2;
                border-color: #e215d5ff;
                color: white;
            }

            .fc-event.Sala-4 {
                background-color: #477EFF;
                border-color: #0947d6ff;
                color: white;
            }

            .fc-event.Sala-5 {
                background-color: #27F568;
                border-color: #02bb3cff;
                color: white;
            }

            .fc-event:hover {
                cursor: pointer !important;
            }

            .fc-button {
                background-color: #fff !important;
                color: #333 !important;
                border: 1px solid #b0b0b0 !important;
                border-radius: 6px !important;
                padding: 5px 10px !important;
                font-weight: 500;
                transition: all 0.2s ease-in-out;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            }

            .fc-button:hover {
                background-color: #e0f0ff !important;
                color: #000 !important;
                border-color: #7bb0ffff !important;
            }

            .fc-button-primary:not(:disabled).fc-button-active,
            .fc-button-primary:not(:disabled):active {
                background-color: #cde8ff !important;
                color: #000 !important;
                border-color: #7bb0ffff !important;
            }

            /* Iconos de flechas */
            .fc-icon {
                color: #333 !important;
                font-size: 1rem;
            }

            /* Título y encabezados de días */
            .fc-toolbar-title {
                color: #222222ff !important;
                font-weight: 600 !important;
            }

            .fc-col-header-cell-cushion {
                color: #444 !important;
                font-weight: 600;
            }

            /* Limitar texto de eventos */
            .fc-event {
                white-space: normal !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                font-size: 0.75rem;
                line-height: 1.1;
            }

            .fc-event .fc-event-title {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: normal !important;
                word-break: break-word;
            }

            /* Eventos más compactos en móviles */
            @media (max-width: 640px) {
                .fc .fc-daygrid-event {
                    font-size: 0.65rem !important;
                    padding: 2px 3px !important;
                    line-height: 1.1 !important;
                    white-space: normal !important;
                }

                .fc .fc-event-title {
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                /* Botones más pequeños en móvil */
                .fc-button {
                    padding: 3px 6px !important;
                    font-size: 0.75rem !important;
                }

                .fc-toolbar-title {
                    font-size: 1rem !important;
                }

                /* Limitar altura del calendario scrollable */
                .fc-scroller {
                    max-height: 60vh;
                    overflow-y: auto;
                }
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const events = @json($events);
                const calendarEl = document.getElementById('calendar');

                const isMobile = window.innerWidth < 640;

                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: isMobile ? 'listWeek' : 'dayGridMonth', 
                    locale: 'es',
                    slotEventOverlap: false, 
                    buttonText: {
                        today: 'Hoy',
                        month: 'Mes',
                        week: 'Semana',
                        day: 'Día',
                        list: 'Lista'
                    },
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: isMobile ? 'listWeek,dayGridMonth' : 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    events: events,
                    allDaySlot: false,
                    slotLabelFormat: {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false
                    },
                    eventClassNames: function(arg) {
                        const colors = ['Sala-1', 'Sala-2', 'Sala-3', 'Sala-4', 'Sala-5'];
                        let roomNumber = 0;
                        const roomText = arg.event.extendedProps.room || '';
                        const match = roomText.match(/\d+/);
                        if (match) roomNumber = parseInt(match[0], 10);
                        if (roomNumber > 0) return [colors[(roomNumber - 1) % colors.length]];
                        return [];
                    },
                    eventContent: function(arg) {
                        if (arg.view.type !== 'dayGridMonth') {
                            const start = arg.event.start;
                            const end = arg.event.end;
                            const doctor = arg.event.extendedProps.doctor || '';
                            const formatHour = (date) => date ? date.toLocaleTimeString([], {
                                hour: '2-digit',
                                minute: '2-digit',
                                hour12: false
                            }) : '';
                            return {
                                html: `<div style="font-size: 0.7rem; font-family: sans-serif; color: #000000ff; white-space: normal; overflow:hidden;">
                                  <b>${arg.event.title}</b> 
                                  ${formatHour(start)} - ${formatHour(end)}<br>
                                  Doctor: ${doctor}
                                </div>`
                            };
                        }
                    },
                    eventDidMount: function(info) {
                        if (info.view.type === 'dayGridMonth') {
                            const start = info.event.start;
                            const end = info.event.end;
                            const room = info.event.extendedProps.room || '';
                            const doctor = info.event.extendedProps.doctor || '';
                            const formatHour = (date) => date ? date.toLocaleTimeString([], {
                                hour: '2-digit',
                                minute: '2-digit',
                                hour12: false
                            }) : '';
                            const mobile = window.innerWidth < 640;
                            info.el.innerHTML = `<div style="font-size:${mobile ? '0.6rem' : '0.75rem'}; line-height:1.1; font-family:sans-serif; color:#000; white-space: normal; overflow:hidden;">
                                <b>${info.event.title}</b>
                                ${!mobile ? `<br>${room}<br>Horario: ${formatHour(start)}-${formatHour(end)}<br>Doctor: ${doctor}<br>` : ''}
                            </div>`;
                        }
                    },
                    eventClick: function(info) {
                        const eventId = info.event.id;
                        window.location.href = `/events/${eventId}`;
                    }
                });

                calendar.render();

                // Actualizar vista si se redimensiona la pantalla
                window.addEventListener('resize', () => {
                    const newMobile = window.innerWidth < 640;
                    if (newMobile !== isMobile) {
                        calendar.changeView(newMobile ? 'listWeek' : 'timeGridWeek');
                    }
                });
            });
        </script>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="flex justify-end p-5">
                    <a href="{{ route('events.create') }}" class="botton1">{{ __('Crear Cita') }}</a>
                </div>
                <div id="calendar" class="p-4 text-gray-800 font-sans"></div>
            </div>
        </div>
    </div>
</x-app-layout>
