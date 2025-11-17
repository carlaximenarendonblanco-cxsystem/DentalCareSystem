@extends('layouts._partials.layout')
@section('title', __('Editar Cita'))
@section('subtitle')
{{ __('Editar Cita') }}
@endsection
@section('content')

<div class="flex justify-end p-3">
    <a href="{{ route('events.index')}}" class="botton1">{{ __('Calendario') }}</a>
</div>

<div class="bg-white rounded-lg max-w-5xl mx-auto">
    <form method="POST" action="{{ route('events.update', $event->id) }}">
        @csrf
        @method('PUT')
        <h1 class="title1 text-center mb-6">{{ __('Editar detalles de la cita') }}</h1>

        {{-- Paciente --}}
        <div class="flex flex-wrap items-center gap-4 mb-4">
            <div class="flex-1 min-w-[280px]">
                <label class="title4 block mb-1">{{ __('Paciente') }}:</label>
                <select name="patient_id" class="w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm">
                    <option value="">{{ __('-- Seleccione un paciente --') }}</option>
                    @foreach($patients as $patient)
                        <option value="{{ $patient->id }}" 
                            {{ old('patient_id', $event->patient_id) == $patient->id ? 'selected' : '' }}>
                            {{ $patient->name_patient }} - CI: {{ $patient->ci_patient }}
                        </option>
                    @endforeach
                </select>
                @error('patient_id') <p class="error mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex justify-end mb-6">
            <p>{{ __('Paciente no registrado?') }}</p>
            <a href="{{ route('patient.create')}}" class="botton3 ml-5">{{ __('Registrar Paciente') }}</a>
        </div>

        {{-- Fecha y duración --}}
        <div class="flex flex-wrap gap-4 mb-4">
            <div class="flex-1 min-w-[240px]">
                <label class="title4 block mb-1">{{ __('Hora de Inicio') }}:</label>
                <input type="datetime-local" name="start_date"
                    value="{{ old('start_date', \Carbon\Carbon::parse($event->start_date)->format('Y-m-d\TH:i')) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm"/>
                @error('start_date') <p class="error mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex-1 min-w-[200px]">
                <label class="title4 block mb-1">{{ __('Duración Aproximada (minutos)') }}:</label>
                <input type="number" min="1" required name="duration_minutes"
                    value="{{ old('duration_minutes', $event->duration_minutes) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm"/>
                @error('duration_minutes') <p class="error mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Consultorio + Doctor --}}
        <div class="flex flex-wrap gap-4 mb-4">

            {{-- CONSULTORIOS DINÁMICOS --}}
            <div class="flex-1 min-w-[200px]">
                <label class="title4 block mb-1">{{ __('Consultorio') }}:</label>
                <select name="room" class="w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm">
                    @for($i = 1; $i <= $clinic->rooms_count; $i++)
                        <option value="Consultorio {{ $i }}" 
                            {{ old('room', $event->room) == "Consultorio $i" ? 'selected' : '' }}>
                            Consultorio {{ $i }}
                        </option>
                    @endfor
                </select>
                @error('room') <p class="error mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- DOCTOR --}}
            <div class="flex-1 min-w-[250px]">
                <label class="title4 block mb-1">{{ __('Doctor') }}:</label>
                <select name="assigned_doctor" class="w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm">
                    <option value="">{{ __('-- Selecciona al Doctor asignado --') }}</option>
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}" 
                            {{ old('assigned_doctor', $event->assigned_doctor) == $doctor->id ? 'selected' : '' }}>
                            {{ $doctor->name }}
                        </option>
                    @endforeach
                </select>
                @error('assigned_doctor') <p class="error mt-1">{{ $message }}</p> @enderror
            </div>

        </div>

        {{-- Procedimiento + Detalles --}}
        <div class="flex flex-wrap gap-4 mb-4">
            <div class="flex-1 min-w-[250px]">
                <label class="title4 block mb-1">{{ __('Procedimiento') }}:</label>
                <input type="text" name="event"
                    value="{{ old('event', $event->event) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm"/>
                @error('event') <p class="error mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex-1 min-w-[250px]">
                <label class="title4 block mb-1">{{ __('Detalles') }}:</label>
                <textarea name="details" rows="2"
                    class="w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm">{{ old('details', $event->details) }}</textarea>
                @error('details') <p class="error mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex justify-center pb-6">
            <button type="submit" class="botton2">{{ __('Actualizar') }}</button>
        </div>

    </form>
</div>

@endsection
