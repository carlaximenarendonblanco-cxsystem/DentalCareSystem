@extends('layouts._partials.layout')
@section('title','Estudios RX')
@section('subtitle')
{{ __('Estudios RX') }}
@endsection

@section('content')
<div class="flex flex-wrap justify-between items-center p-5 gap-2">
    <!-- Buscador -->
    <form method="POST" action="{{ route('multimedia.search') }}" class="flex gap-2 items-center flex-wrap">
        @csrf
        <input type="text" name="search" placeholder="{{ __('Buscar estudio...') }}"
            class="px-4 py-2 rounded-full border border-gray-300 text-gray-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 w-full sm:w-auto sm:flex-1 max-w-xs" />
        <input class="botton2 px-4 py-2 rounded-full" type="submit" value="{{ __('Buscar') }}" />
    </form>

    <div class="flex gap-2 flex-wrap">
        <a href="{{ route('multimedia.create') }}" class="botton1">{{ __('Subir Estudio') }}</a>
    </div>
</div>

<h1 class="title1 text-center my-4">{{ __('Lista de Estudios Multimedia') }}</h1>

<!-- Desktop Table -->
<div class="hidden sm:block max-w-6xl mx-auto bg-white rounded-xl p-3 text-gray-900 shadow-md">
    <div class="grid grid-cols-6 gap-4 border-b border-gray-300 pb-2 mb-3 text-center font-semibold">
        <div class="title4">{{ __('Fecha') }}</div>
        <div class="title4">{{ __('Nombre') }}</div>
        <div class="title4">{{ __('C.I. Paciente') }}</div>
        <div class="title4">{{ __('Código') }}</div>
        <div class="title4">{{ __('Tipo') }}</div>
        <div class="title4">{{ __('Acciones') }}</div>
    </div>

    @forelse($studies as $study)
    <div class="grid grid-cols-6 gap-4 items-center border-b border-gray-200 py-3 text-gray-800 hover:bg-gray-50 transition text-center">
        <div><a href="{{ route('multimedia.show', $study->id) }}" class="hover:text-cyan-600">{{ $study->study_date }}</a></div>
        <div><a href="{{ route('multimedia.show', $study->id) }}" class="hover:text-cyan-600">{{ $study->name_patient }}</a></div>
        <div><a href="{{ route('multimedia.show', $study->id) }}" class="hover:text-cyan-600">{{ $study->ci_patient }}</a></div>
        <div><a href="{{ route('multimedia.show', $study->id) }}" class="hover:text-cyan-600">{{ $study->study_code }}</a></div>
        <div><a href="{{ route('multimedia.show', $study->id) }}" class="hover:text-cyan-600">{{ $study->study_type }}</a></div>
        <div class="flex justify-center gap-2">
            <a href="{{ route('multimedia.edit', $study->id) }}" class="botton3">{{ __('Editar') }}</a>
            @auth
                @if(Auth::user()->role === 'admin')
                <form method="POST" action="{{ route('multimedia.destroy', $study->id) }}"
                      onsubmit="return confirm('{{ __('¿Seguro que deseas eliminar este estudio?') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bottonDelete cursor-pointer">{{ __('Eliminar') }}</button>
                </form>
                @endif
            @endauth
        </div>
    </div>
    @empty
    <p class="text-gray-600 text-center py-4">{{ __('No hay estudios multimedia registrados aún.') }}</p>
    @endforelse

    <div class="pt-4">{{ $studies->links() }}</div>
</div>

<!-- Mobile Cards -->
<div class="sm:hidden max-w-6xl mx-auto flex flex-col gap-3">
    @forelse($studies as $study)
    <div class="bg-white rounded-lg shadow-md p-4 flex flex-col gap-2 hover:shadow-lg transition">
        <div class="flex justify-between items-center">
            <div class="font-semibold text-gray-700">{{ $study->name_patient }}</div>
            <div class="text-gray-500 text-sm">{{ $study->study_date }}</div>
        </div>
        <div class="text-gray-600">C.I.: {{ $study->ci_patient }}</div>
        <div class="text-gray-600">Código: {{ $study->study_code }}</div>
        <div class="text-gray-600">Tipo: {{ $study->study_type }}</div>
        <div class="flex gap-2 mt-2">
            <a href="{{ route('multimedia.edit', $study->id) }}" class="botton3 flex-1 text-center">{{ __('Editar') }}</a>
            @auth
                @if(Auth::user()->role === 'admin')
                <form method="POST" action="{{ route('multimedia.destroy', $study->id) }}" 
                      onsubmit="return confirm('{{ __('¿Seguro que deseas eliminar este estudio?') }}');" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bottonDelete w-full cursor-pointer">{{ __('Eliminar') }}</button>
                </form>
                @endif
            @endauth
        </div>
    </div>
    @empty
    <p class="text-gray-600 text-center py-4">{{ __('No hay estudios multimedia registrados aún.') }}</p>
    @endforelse

    <div class="pt-4">{{ $studies->links() }}</div>
</div>
@endsection
