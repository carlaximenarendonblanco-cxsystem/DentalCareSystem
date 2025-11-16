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
      body { background-color: #ffffff !important; color: #333; }
      #calendar { overflow-x: auto; }
      .fc { background-color: #fff; border-radius: 8px; }
      .fc-scrollgrid, .fc-scrollgrid-section, .fc-scrollgrid-sync-table { border-color: #b0b0b0 !important; }
      .fc-daygrid-day-frame { border-color: #b0b0b0 !important; }
      .fc-daygrid-day-number { color: #333 !important; font-weight: 500; }
      .fc-day-today { background-color: rgba(17, 140, 255, 0.23) !important; }

      .fc-event:hover { cursor: pointer !important; }

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
      .fc-button:hover { background-color: #e0f0ff !important; color: #000 !important; border-color: #7bb0ffff !important; }
      .fc-button-primary:not(:disabled).fc-button-active, .fc-button-primary:not(:disabled):active {
        background-color: #cde8ff !important; color: #000 !important; border-color: #7bb0ffff !important;
      }

      .fc-icon { color: #333 !important; font-size: 1rem; }
      .fc-toolbar-title { color: #222222ff !important; font-weight: 600 !important; }
      .fc-col-header-cell-cushion { color: #444 !important; font-weight: 600; }

      @media (max-width: 640px) {
        .fc .fc-daygrid-event { font-size: 0.65rem !important; padding: 2px 3px !important; line-height: 1.1 !important; white-space: normal !important; }
        .fc .fc-event-title { overflow: hidden; text-overflow: ellipsis; }
        .fc-button { padding: 3px 6px !important; font-size: 0.75rem !important; }
        .fc-toolbar-title { font-size: 1rem !important; }
        .fc-scroller { max-height: 60vh; overflow-y: auto; }
      }
    </style>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const events = @json($events);
        const calendarEl = document.getElementById('calendar');
        const isMobile = window.innerWidth < 640;

        // Colores para salas (hasta 5 consultorios)
        const roomColors = [
          { background: '#5B65FF', border: '#2320FF' },
          { background: '#18FFCD', border: '#33FFCA' },
          { background: '#FF7F50', border: '#FF5722' },
          { background: '#FFD700', border: '#FFC107' },
          { background: '#32CD32', border: '#228B22' }
        ];

        const calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: isMobile ? 'listWeek' : 'dayGridMonth',
          locale: 'es',
          buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana', day: 'Día', list: 'Lista' },
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: isMobile ? 'listWeek,dayGridMonth' : 'dayGridMonth,timeGridWeek,timeGridDay'
          },
          events: events,
          allDaySlot: false,
          slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },

          // Asignar colores según consultorio
          eventClassNames: function(arg) {
            const room = arg.event.extendedProps.room;
            if(!room) return [];
            const index = parseInt(room.split(' ')[1]) - 1;
            if(index >= 0 && index < roomColors.length){
              arg.event.backgroundColor = roomColors[index].background;
              arg.event.borderColor = roomColors[index].border;
              arg.event.textColor = '#fff';
            }
            return [];
          },

          // Vista de eventos fuera de month
          eventContent: function(arg) {
            if (arg.view.type !== 'dayGridMonth') {
              const start = arg.event.start;
              const end = arg.event.end;
              const formatHour = (date) => date ? date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false }) : '';
              return { html: `<div style="font-size: 0.7rem; font-family: sans-serif; color: #000;"><b>${arg.event.title}</b> ${formatHour(start)} - ${formatHour(end)}</div>` };
            }
          },

          // Vista dayGridMonth compacta
          eventDidMount: function(info) {
            if (info.view.type === 'dayGridMonth') {
              const start = info.event.start;
              const end = info.event.end;
              const room = info.event.extendedProps.room || '';
              const formatHour = (date) => date ? date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false }) : '';
              const mobile = window.innerWidth < 640;
              info.el.innerHTML = `<div style="font-size:${mobile ? '0.6rem' : '0.75rem'}; line-height:1.1; font-family:sans-serif; color:#000;">
                <b>${info.event.title}</b>
                ${!mobile ? `<br>${room}<br>Horario: ${formatHour(start)}-${formatHour(end)}` : ''}
              </div>`;
            }
          },

          eventClick: function(info) {
            window.location.href = `/events/${info.event.id}`;
          }
        });

        calendar.render();

        // Actualizar vista si se redimensiona
        window.addEventListener('resize', () => {
          const newMobile = window.innerWidth < 640;
          if (newMobile !== isMobile) {
            calendar.changeView(newMobile ? 'listWeek' : 'dayGridMonth');
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
