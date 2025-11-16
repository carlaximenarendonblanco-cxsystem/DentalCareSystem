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

      /* General calendar style */
      .fc {
        background-color: #fff;
        border-radius: 8px;
      }

      .fc-scrollgrid, .fc-scrollgrid-section, .fc-scrollgrid-sync-table {
        border-color: #b0b0b0 !important;
      }

      .fc-daygrid-day-number {
        color: #333 !important;
        font-weight: 500;
      }

      .fc-day-today {
        background-color: rgba(17, 140, 255, 0.23) !important;
      }

      /* Event colors by room */
      .fc-event.Sala-1 {
        background-color: rgba(91, 101, 255, 0.59) !important;
        border-color: rgba(35, 31, 255, 0.7) !important;
        color: white !important;
      }

      .fc-event.Sala-2 {
        background-color: #18ffcde3 !important;
        border-color: #33ffc2ff !important;
        color: white !important;
      }

      .fc-event:hover {
        cursor: pointer !important;
      }

      /* Buttons */
      .fc-button {
        background-color: #fff !important;
        color: #333 !important;
        border: 1px solid #b0b0b0 !important;
        border-radius: 6px !important;
        padding: 5px 10px !important;
        font-weight: 500;
        transition: all 0.2s ease-in-out;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
      }

      .fc-button:hover {
        background-color: #e0f0ff !important;
        color: #000 !important;
        border-color: #7bb0ffff !important;
      }

      /* Smaller buttons and font on mobile */
      @media (max-width: 768px) {
        .fc-toolbar .fc-button {
          padding: 3px 6px !important;
          font-size: 0.7rem !important;
        }
      }

      /* Toolbar title smaller on mobile */
      @media (max-width: 768px) {
        .fc-toolbar-title {
          font-size: 1rem !important;
        }
      }
    </style>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const events = @json($events);
        const calendarEl = document.getElementById('calendar');

        // Detect mobile
        const isMobile = window.innerWidth <= 768;
        const initialView = isMobile ? 'timeGridDay' : 'dayGridMonth'; // solo un día en móvil

        const calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: initialView,
          locale: 'es',
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: isMobile ? 'timeGridDay,timeGridWeek' : 'dayGridMonth,timeGridWeek,timeGridDay'
          },
          buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            day: 'Día',
            list: 'Lista'
          },
          events: events,
          allDaySlot: false,
          slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
          eventClassNames: function(arg) {
            if (arg.event.extendedProps.room === 'Consultorio 1') return ['Sala-1'];
            if (arg.event.extendedProps.room === 'Consultorio 2') return ['Sala-2'];
            return [];
          },
          eventClick: function(info) {
            const eventId = info.event.id;
            window.location.href = `/events/${eventId}`;
          }
        });

        calendar.render();

        // Re-render on resize to switch view
        window.addEventListener('resize', () => {
          const isMobileResize = window.innerWidth <= 768;
          const newView = isMobileResize ? 'timeGridDay' : 'dayGridMonth';
          if (calendar.view.type !== newView) {
            calendar.changeView(newView);
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
