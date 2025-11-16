@extends('layouts._partials.layout')

@section('title', 'Tratamientos')
@section('subtitle')
{{ __('Tratamientos') }}
@endsection

@section('content')
<div class="flex flex-wrap justify-between items-center p-5 gap-2">
    <!-- Buscador -->
    <form method="POST" action="{{ route('treatments.search') }}" class="flex gap-2 items-center flex-wrap">
        @csrf
        <input 
            type="text" 
            name="search" 
            placeholder="{{ __('Buscar presupuesto...') }}" 
            class="px-4 py-2 rounded-full border border-gray-300 text-gray-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 w-full sm:w-auto sm:flex-1 max-w-xs"
        />
        <input class="botton2 px-4 py-2 rounded-full" type="submit" value="{{ __('Buscar') }}" />
    </form>

    <!-- Botón atrás -->
    <a href="{{ route('treatments.index') }}" class="botton1">{{ __('Atrás') }}</a>
</div>

<h1 class="title1 text-center my-4">{{ __('Resultados de la búsqueda') }}</h1>

<!-- Desktop Table -->
<div class="hidden sm:block max-w-6xl mx-auto bg-white rounded-xl p-3 text-gray-900 shadow-md">
    <div class="grid grid-cols-7 gap-4 border-b border-gray-300 pb-2 mb-3 text-center font-semibold">
        <div class="title4">{{ __('Paciente') }}</div>
        <div class="title4">{{ __('C.I.') }}</div>
        <div class="title4">{{ __('Total') }}</div>
        <div class="title4">{{ __('Descuento') }}</div>
        <div class="title4">{{ __('Costo Final') }}</div>
        <div class="title4">{{ __('PDF') }}</div>
        <div class="title4">{{ __('Acciones') }}</div>
    </div>

    @forelse($treatments as $t)
    <div class="grid grid-cols-7 gap-4 items-center border-b border-gray-200 py-3 text-gray-800 hover:bg-gray-50 transition text-center">
        <div>{{ $t->name ?? 'N/A' }}</div>
        <div>{{ $t->ci_patient ?? 'N/A' }}</div>
        <div>Bs {{ number_format($t->total_amount, 2) }}</div>
        <div>Bs {{ number_format($t->discount, 2) }}</div>
        <div>Bs {{ number_format($t->amount, 2) }}</div>
        <div>
            @if ($t->pdf_path)
                <a href="{{ asset($t->pdf_path) }}" class="botton2">{{ __('Ver PDF') }}</a>
            @else
                —
            @endif
        </div>
        <div class="flex justify-center gap-2">
            <a href="{{ route('treatments.show', $t->id) }}" class="botton3">{{ __('Registrar Pago') }}</a>
            @auth
                @if(Auth::user()->role === 'admin')  
                <form method="POST" action="{{ route('treatments.destroy', $t->id) }}" 
                      onsubmit="return confirm('{{ __('¿Estás seguro de que quieres eliminar este tratamiento?') }}');">
                    @csrf
                    @method('DELETE')
                    <input type="submit" value="{{ __('Eliminar') }}" class="bottonDelete cursor-pointer"/>
                </form>
                @endif
            @endauth
        </div>
    </div>
    @empty
    <p class="text-gray-600 text-center py-4">{{ __('No se encontraron resultados para su búsqueda.') }}</p>
    @endforelse
</div>

<!-- Mobile Cards -->
<div class="sm:hidden max-w-6xl mx-auto flex flex-col gap-3">
    @forelse($treatments as $t)
    <div class="bg-white rounded-lg shadow-md p-4 flex flex-col gap-2 hover:shadow-lg transition">
        <div class="flex justify-between items-center">
            <div class="font-semibold text-gray-700">{{ $t->name ?? 'N/A' }}</div>
            <div>
                <a href="{{ route('treatments.show', $t->id) }}" class="botton3">{{ __('Registrar Pago') }}</a>
            </div>
        </div>
        <div class="text-gray-600">CI: {{ $t->ci_patient ?? 'N/A' }}</div>
        <div class="text-gray-600">Total: Bs {{ number_format($t->total_amount, 2) }}</div>
        <div class="text-gray-600">Descuento: Bs {{ number_format($t->discount, 2) }}</div>
        <div class="text-gray-600">Costo Final: Bs {{ number_format($t->amount, 2) }}</div>
        <div class="text-gray-500">
            PDF: 
            @if ($t->pdf_path)
                <a href="{{ asset($t->pdf_path) }}" class="text-cyan-600 hover:underline">{{ __('Ver PDF') }}</a>
            @else
                —
            @endif
        </div>
        @auth
            @if(Auth::user()->role === 'admin')
            <form method="POST" action="{{ route('treatments.destroy', $t->id) }}" 
                  onsubmit="return confirm('{{ __('¿Estás seguro de que quieres eliminar este tratamiento?') }}');" class="mt-2">
                @csrf
                @method('DELETE')
                <input type="submit" value="{{ __('Eliminar') }}" class="bottonDelete cursor-pointer"/>
            </form>
            @endif
        @endauth
    </div>
    @empty
    <p class="text-gray-600 text-center py-4">{{ __('No se encontraron resultados para su búsqueda.') }}</p>
    @endforelse
</div>

<div class="pt-4">
    {{ $treatments->links() }}
</div>
@endsection
