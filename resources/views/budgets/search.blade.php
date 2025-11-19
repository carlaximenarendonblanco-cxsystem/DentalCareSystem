@extends('layouts._partials.layout')
@section('title','Tratamientos')
@section('subtitle')
{{ __('Tratamientos') }}
@endsection

@section('content')
<div class="flex flex-wrap justify-between items-center p-5 gap-2">
    <!-- Buscador -->
    <form method="POST" action="{{ route('budgets.search') }}" class="flex gap-2 items-center flex-wrap">
        @csrf
        <input type="text" name="search" placeholder="{{ __('Buscar tratamiento...') }}"
            class="px-4 py-2 rounded-full border border-gray-300 text-gray-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 w-full sm:w-auto sm:flex-1 max-w-xs" />
        <input class="botton2 px-4 py-2 rounded-full" type="submit" value="{{ __('Buscar') }}" />
    </form>

    <!-- Botón atrás -->
    <a href="{{ route('budgets.index') }}" class="botton1">{{ __('Atrás') }}</a>
</div>

<h1 class="title1 text-center my-4">{{ __('Resultados de la búsqueda') }}</h1>

<!-- Desktop Table -->
<div class="hidden sm:block max-w-6xl mx-auto bg-white rounded-xl p-3 text-gray-900 shadow-md">
    <div class="grid grid-cols-4 gap-4 border-b border-gray-300 pb-2 mb-3 text-center font-semibold">
        <div class="title4">{{ __('Código') }}</div>
        <div class="title4">{{ __('Procedimiento') }}</div>
        <div class="title4">{{ __('Costo Total') }}</div>
        <div class="title4">{{ __('Acciones') }}</div>
    </div>

    @forelse($budgets as $budget)
    <div class="grid grid-cols-4 gap-4 items-center border-b border-gray-200 py-3 text-gray-800 hover:bg-gray-50 transition text-center">
        <div><a href="{{ route('budgets.show', $budget->id) }}" class="hover:text-cyan-600">{{ $budget->budget }}</a></div>
        <div><a href="{{ route('budgets.show', $budget->id) }}" class="hover:text-cyan-600">{{ $budget->procedure }}</a></div>
        <div>Bs. {{ number_format($budget->total_amount, 2) }}</div>
        <div class="flex justify-center gap-2">
            <a href="{{ route('budgets.edit', $budget->id) }}" class="botton3">{{ __('Editar') }}</a>
            @auth
                @if(Auth::user()->role === 'admin')
                <form method="POST" action="{{ route('budgets.destroy', $budget->id) }}"
                      onsubmit="return confirm('{{ __('¿Estás seguro de que quieres eliminar este presupuesto?') }}');">
                    @csrf
                    @method('DELETE')
                    <input type="submit" value="{{ __('Eliminar') }}" class="bottonDelete cursor-pointer"/>
                </form>
                @endif
            @endauth
        </div>
    </div>
    @empty
    <p class="text-gray-600 text-center py-4">{{ __('No se encontraron resultados para la búsqueda.') }}</p>
    @endforelse
</div>

<!-- Mobile Cards -->
<div class="sm:hidden max-w-6xl mx-auto flex flex-col gap-3">
    @forelse($budgets as $budget)
    <div class="bg-white rounded-lg shadow-md p-4 flex flex-col gap-2 hover:shadow-lg transition">
        <div class="font-semibold text-gray-700">{{ $budget->procedure }}</div>
        <div class="text-gray-800">Código: {{ $budget->budget }}</div>
        <div class="text-gray-600">Costo Total: Bs. {{ number_format($budget->total_amount,2) }}</div>
        <div class="flex gap-2 mt-2">
            <a href="{{ route('budgets.edit', $budget->id) }}" class="botton3 flex-1 text-center">{{ __('Editar') }}</a>
            @auth
                @if(Auth::user()->role === 'admin')
                <form method="POST" action="{{ route('budgets.destroy', $budget->id) }}"
                      onsubmit="return confirm('{{ __('¿Estás seguro de que quieres eliminar este presupuesto?') }}');" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <input type="submit" value="{{ __('Eliminar') }}" class="bottonDelete w-full cursor-pointer"/>
                </form>
                @endif
            @endauth
        </div>
    </div>
    @empty
    <p class="text-gray-600 text-center py-4">{{ __('No se encontraron resultados para la búsqueda.') }}</p>
    @endforelse
</div>

@endsection
