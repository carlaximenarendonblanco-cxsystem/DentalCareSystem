@extends('layouts._partials.layout')
@section('title','Información del Paciente')
@section('subtitle')
{{ __('Información del Paciente') }}
@endsection

@section('content')
<div class="flex justify-end pt-5 pr-5">
    <a href="{{ route('patient.index')}}" class="botton1">{{ __('Pacientes') }}</a>
</div>

<!-- Contenedor principal -->
<div class="max-w-5xl pt-2 mx-auto bg-white rounded-xl p-8 text-gray-900 dark:text-white">
    <div class="mt-10 mb-5">
        <h1 class="title1 text-center pb-5">{{ __('Información del Paciente') }}</h1>
    </div>

    <!-- Información general del paciente -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4 pb-5">
        <div class="flex gap-2">
            <h3 class="title4">{{ __('Nombre del paciente:') }}</h3><span class="txt">{{ $patient->name_patient }}</span>
        </div>
        <div class="flex gap-2">
            <h3 class="title4">{{ __('Carnet de Identidad:') }}</h3><span class="txt">{{ $patient->ci_patient }}</span>
        </div>
        <div class="flex gap-2">
            <h3 class="title4">{{ __('Fecha de nacimiento:') }}</h3><span class="txt">{{ $patient->birth_date }}</span>
        </div>
        <div class="flex gap-2">
            <h3 class="title4">{{ __('Género:') }}</h3><span class="txt">{{ $patient->gender }}</span>
        </div>
        <div class="flex gap-2">
            <h3 class="title4">{{ __('Número de celular:') }}</h3><span class="txt">{{ $patient->patient_contact }}</span>
        </div>
    </div>

    @auth
    @if(auth()->user()->role === 'superadmin')
    <!-- Información del sistema -->
    <div class="mt-10 mb-5">
        <h1 class="title1 text-center pb-5">{{ __('Información del Registro') }}</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-12 gap-y-4 pb-5">

            <div class="flex gap-2">
                <h3 class="title4">{{ __('Clínica asignada:') }}</h3>
                <span class="txt">
                    {{ $patient->clinic->name ?? 'Sin clínica' }}
                </span>
            </div>

            <div class="flex gap-2">
                <h3 class="title4">{{ __('Creado por:') }}</h3>
                <span class="txt">
                    {{ $patient->creator->name ?? 'N/A' }}
                </span>
            </div>

            <div class="flex gap-2">
                <h3 class="title4">{{ __('Última edición por:') }}</h3>
                <span class="txt">
                    {{ $patient->editor->name ?? 'Sin ediciones' }}
                </span>
            </div>

        </div>
    </div>
    @endif
    @endauth

    <!-- Tratamientos del paciente -->
    <div class="mt-8">
        <h1 class="title1 text-center pb-4">{{ __('Tratamientos del Paciente') }}</h1>

        @if($patient->treatments->isEmpty())
            <p class="text-gray-700 pl-4">{{ __('El paciente no tiene tratamientos.') }}</p>
        @else
            <!-- Tabla escritorio -->
            <div class="hidden sm:block">
                <div class="grid grid-cols-6 gap-4 font-semibold border-b border-gray-300 pb-2 mb-2 text-center">
                    <span>{{ __('C.I.') }}</span>
                    <span>{{ __('Nombre') }}</span>
                    <span>{{ __('Total') }}</span>
                    <span>{{ __('Descuento') }}</span>
                    <span>{{ __('Costo Final') }}</span>
                    <span>{{ __('Acciones') }}</span>
                </div>
                @foreach($patient->treatments as $treatment)
                <div class="grid grid-cols-6 gap-4 border-b border-gray-200 py-2 text-center items-center hover:bg-gray-50 transition">
                    <div>{{ $treatment->ci_patient ?? 'N/A' }}</div>
                    <div>{{ $treatment->name ?? 'N/A' }}</div>
                    <div>Bs. {{ number_format($treatment->total_amount, 2) }}</div>
                    <div>Bs. {{ number_format($treatment->discount, 2) }}</div>
                    <div>Bs. {{ number_format($treatment->amount, 2) }}</div>
                    <div>
                        <a href="{{ route('payments.show',$treatment->id) }}" class="botton3">{{ __('Ver Pagos') }}</a>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Tarjetas móvil -->
            <div class="sm:hidden flex flex-col gap-3">
                @foreach($patient->treatments as $treatment)
                <div class="bg-white rounded-lg shadow-md p-4 flex flex-col gap-2 hover:shadow-lg transition">
                    <div class="flex justify-between items-center">
                        <div class="font-semibold text-gray-700">{{ $treatment->name ?? 'N/A' }}</div>
                        <div>
                            <a href="{{ route('payments.show',$treatment->id) }}" class="botton3 text-sm px-2 py-1">{{ __('Ver Pagos') }}</a>
                        </div>
                    </div>
                    <div><strong>{{ __('C.I.') }}:</strong> {{ $treatment->ci_patient ?? 'N/A' }}</div>
                    <div><strong>{{ __('Total') }}:</strong> Bs. {{ number_format($treatment->total_amount, 2) }}</div>
                    <div><strong>{{ __('Descuento') }}:</strong> Bs. {{ number_format($treatment->discount, 2) }}</div>
                    <div><strong>{{ __('Costo Final') }}:</strong> Bs. {{ number_format($treatment->amount, 2) }}</div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Citas del paciente -->
    <div class="mt-8">
        <h1 class="title1 text-center pb-4">{{ __('Citas del Paciente') }}</h1>

        @if($patient->events->isEmpty())
            <p class="text-gray-700 pl-4">{{ __('El paciente no tiene citas programadas.') }}</p>
        @else
            <!-- Tabla escritorio -->
            <div class="hidden sm:block">
                <div class="grid grid-cols-5 gap-4 font-semibold border-b border-gray-300 pb-2 mb-2 text-center">
                    <span>{{ __('Fecha') }}</span>
                    <span>{{ __('Descripción') }}</span>
                    <span>{{ __('Doctor') }}</span>
                    <span>{{ __('Consultorio') }}</span>
                    <span>{{ __('Acciones') }}</span>
                </div>
                @foreach($patient->events as $event)
                <div class="grid grid-cols-5 gap-4 items-center border-b border-gray-200 mb-2 p-2 text-center hover:bg-gray-50 transition">
                    <div>{{ \Carbon\Carbon::parse($event->start_date)->format('d-m-Y H:i') }}</div>
                    <div>{{ $event->details }}</div>
                    <div>{{ $event->assignedDoctor->name ?? __('Not assigned') }}</div>
                    <div>{{ $event->room }}</div>
                    <div>
                        <a href="{{ route('events.show', $event->id ) }}" class="botton2">{{ __('Detalles') }}</a>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Tarjetas móvil -->
            <div class="sm:hidden flex flex-col gap-3">
                @foreach($patient->events as $event)
                <div class="bg-white rounded-lg shadow-md p-4 flex flex-col gap-2 hover:shadow-lg transition">
                    <div class="flex justify-between items-center">
                        <div class="font-semibold text-gray-700">{{ \Carbon\Carbon::parse($event->start_date)->format('d-m-Y H:i') }}</div>
                        <div>
                            <a href="{{ route('events.show', $event->id ) }}" class="botton2 text-sm px-2 py-1">{{ __('Detalles') }}</a>
                        </div>
                    </div>
                    <div><strong>{{ __('Descripción') }}:</strong> {{ $event->details }}</div>
                    <div><strong>{{ __('Doctor') }}:</strong> {{ $event->assignedDoctor->name ?? __('Not assigned') }}</div>
                    <div><strong>{{ __('Consultorio') }}:</strong> {{ $event->room }}</div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
