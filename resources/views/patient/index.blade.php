@extends('layouts._partials.layout')
@section('title','Pacientes')
@section('subtitle')
    {{ __('Pacientes') }}
@endsection

@section('content')
<div class="flex flex-wrap justify-between items-center p-5 gap-2">
    <!-- Buscador -->
    <form method="POST" action="{{ route('patient.search') }}" class="flex gap-2 items-center flex-wrap">
        @csrf
        <input type="text" name="search" placeholder="{{ __('Buscar paciente...') }}"
            class="px-4 py-2 rounded-full border border-gray-300 text-gray-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 w-full sm:w-auto sm:flex-1 max-w-xs" />
        <input class="botton2 px-4 py-2 rounded-full" type="submit" value="{{ __('Buscar') }}" />
    </form>

    <div class="flex gap-2 flex-wrap">
        <a href="{{ route('patient.create') }}" class="botton1">{{ __('Crear Paciente') }}</a>
    </div>
</div>

<h1 class="title1 text-center my-4">{{ __('Lista de Pacientes') }}</h1>

<!-- Desktop Table -->
<div class="hidden sm:block max-w-6xl mx-auto bg-white rounded-xl p-3 text-gray-900 shadow-md">
    <div class="grid grid-cols-5 gap-4 border-b border-gray-300 pb-2 mb-3 text-center font-semibold">
        <div class="title4">{{ __('Carnet de Identidad') }}</div>
        <div class="title4">{{ __('Nombre') }}</div>
        <div class="title4">{{ __('Fecha de nacimiento') }}</div>
        <div class="title4">{{ __('Celular') }}</div>
        <div class="title4">{{ __('Acciones') }}</div>
    </div>

    @forelse($patients as $patient)
    <div class="grid grid-cols-5 gap-4 items-center border-b border-gray-200 py-3 text-gray-800 hover:bg-gray-50 transition text-center">
        <div><a href="{{ route('patient.show', $patient->id) }}" class="hover:text-cyan-600">{{ $patient->ci_patient }}</a></div>
        <div><a href="{{ route('patient.show', $patient->id) }}" class="hover:text-cyan-600">{{ $patient->name_patient }}</a></div>
        <div><a href="{{ route('patient.show', $patient->id) }}" class="hover:text-cyan-600">{{ $patient->birth_date }}</a></div>
        <div><a href="{{ route('patient.show', $patient->id) }}" class="hover:text-cyan-600">{{ $patient->patient_contact }}</a></div>
        <div class="flex justify-center gap-2">
            <a href="{{ route('patient.edit', $patient->id) }}" class="botton3">{{ __('Editar') }}</a>
            @auth
                @if(Auth::user()->role === 'admin')  
                <form method="POST" action="{{ route('patient.destroy', $patient->id) }}" 
                      onsubmit="return confirm('{{ __('¿Estás seguro de que quieres eliminar este paciente?') }}');">
                    @csrf
                    @method('DELETE')
                    <input type="submit" value="{{ __('Eliminar') }}" class="bottonDelete cursor-pointer"/>
                </form>
                @endif
            @endauth
        </div>
    </div>
    @empty
    <p class="text-gray-600 text-center py-4">{{ __('Aún no hay pacientes registrados.') }}</p>
    @endforelse

    <div class="pt-4">
        {{ $patients->links() }}
    </div>
</div>

<!-- Mobile Cards -->
<div class="sm:hidden max-w-6xl mx-auto flex flex-col gap-3">
    @forelse($patients as $patient)
    <div class="bg-white rounded-lg shadow-md p-4 flex flex-col gap-2 hover:shadow-lg transition">
        <div class="flex justify-between items-center">
            <div class="font-semibold text-gray-700"><a href="{{ route('patient.show', $patient->id) }}">{{ $patient->name_patient }}</a></div>
            <div class="text-gray-500 text-sm">{{ $patient->ci_patient }}</div>
        </div>
        <div class="text-gray-600">Fecha de nacimiento: {{ $patient->birth_date }}</div>
        <div class="text-gray-600">Celular: {{ $patient->patient_contact }}</div>
        <div class="flex gap-2 mt-2">
            <a href="{{ route('patient.edit', $patient->id) }}" class="botton3 flex-1 text-center">{{ __('Editar') }}</a>
            @auth
                @if(Auth::user()->role === 'admin')  
                <form method="POST" action="{{ route('patient.destroy', $patient->id) }}" 
                      onsubmit="return confirm('{{ __('¿Estás seguro de que quieres eliminar este paciente?') }}');" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <input type="submit" value="{{ __('Eliminar') }}" class="bottonDelete w-full cursor-pointer"/>
                </form>
                @endif
            @endauth
        </div>
    </div>
    @empty
    <p class="text-gray-600 text-center py-4">{{ __('Aún no hay pacientes registrados.') }}</p>
    @endforelse

    <div class="pt-4">
        {{ $patients->links() }}
    </div>
</div>
@endsection
