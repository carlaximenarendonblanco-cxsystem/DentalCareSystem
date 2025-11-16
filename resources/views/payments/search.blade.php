@extends('layouts._partials.layout')

@section('title', 'Pagos')
@section('subtitle')
{{ __('Pagos') }}
@endsection

@section('content')
<div class="flex flex-wrap justify-between items-center p-5 gap-2">
    <!-- Formulario de búsqueda -->
    <form method="POST" action="{{ route('payments.search', $treatment->id ?? 0) }}" class="flex gap-2 items-center flex-wrap">
        @csrf
        <input 
            type="text" 
            name="search" 
            placeholder="{{ __('Buscar pago...') }}" 
            value="{{ old('search', $search ?? '') }}"
            class="px-4 py-2 rounded-full border border-gray-300 text-gray-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 w-full sm:w-auto sm:flex-1 max-w-xs"
        />
        <input class="botton2 px-4 py-2 rounded-full" type="submit" value="{{ __('Buscar') }}" />
    </form>

    <!-- Botones -->
    <div class="flex gap-2 flex-wrap">
        <a href="{{ route('payments.index') }}" class="botton1">{{ __('Atrás') }}</a>
        @isset($treatment)
            <a href="{{ route('payments.create', $treatment->id) }}" class="botton1">{{ __('Añadir Pago') }}</a>
        @endisset
    </div>
</div>

<h1 class="title1 text-center my-4">{{ __('Resultados de Búsqueda') }}</h1>

<!-- Desktop Table -->
<div class="hidden sm:block max-w-6xl mx-auto bg-white rounded-xl p-3 text-gray-900 shadow-md">
    <div class="grid grid-cols-8 gap-4 border-b border-gray-300 pb-2 mb-3 text-center font-semibold">
        <div class="title4">{{ __('Fecha') }}</div>
        <div class="title4">{{ __('Nombre') }}</div>
        <div class="title4">{{ __('C.I.') }}</div>
        <div class="title4">{{ __('Total') }}</div>
        <div class="title4">{{ __('Monto') }}</div>
        <div class="title4">{{ __('Método') }}</div>
        <div class="title4">{{ __('Detalles') }}</div>
        <div class="title4">{{ __('Acciones') }}</div>
    </div>

    @forelse($payments as $p)
    <div class="grid grid-cols-8 gap-4 items-center border-b border-gray-200 py-3 text-gray-800 hover:bg-gray-50 transition text-center">
        <div>{{ $p->created_at->format('d/m/Y H:i') }}</div>
        <div>{{ $p->treatment->name }}</div>
        <div>{{ $p->treatment->ci_patient }}</div>
        <div>Bs. {{ number_format($p->treatment->amount, 2) }}</div>
        <div>Bs. {{ number_format($p->amount, 2) }}</div>
        <div>{{ $p->method ?? '-' }}</div>
        <div>{{ $p->notes ?? '-' }}</div>
        <div class="flex justify-center gap-2">
            <a href="{{ route('payments.show', $p->treatment->id) }}" class="botton3">{{ __('Ver') }}</a>
        </div>
    </div>
    @empty
    <p class="text-gray-600 text-center py-4">{{ __('No se encontraron pagos con los criterios especificados.') }}</p>
    @endforelse
</div>

<!-- Mobile Cards -->
<div class="sm:hidden max-w-6xl mx-auto flex flex-col gap-3">
    @forelse($payments as $p)
    <div class="bg-white rounded-lg shadow-md p-4 flex flex-col gap-2 hover:shadow-lg transition">
        <div class="flex justify-between items-center">
            <div class="font-semibold text-gray-700">{{ $p->created_at->format('d/m/Y H:i') }}</div>
            <a href="{{ route('payments.show', $p->treatment->id) }}" class="text-cyan-600 hover:underline text-sm font-medium">{{ __('Ver') }}</a>
        </div>
        <div class="text-gray-800 font-medium">{{ $p->treatment->name }}</div>
        <div class="text-gray-600">CI: {{ $p->treatment->ci_patient }}</div>
        <div class="text-gray-600">Monto: Bs. {{ number_format($p->amount,2) }}</div>
        <div class="text-gray-500 text-sm">Método: {{ $p->method ?? '-' }}</div>
        <div class="text-gray-500 text-sm">Detalles: {{ $p->notes ?? '-' }}</div>
    </div>
    @empty
    <p class="text-gray-600 text-center py-4">{{ __('No se encontraron pagos con los criterios especificados.') }}</p>
    @endforelse
</div>

<div class="pt-4">
    {{ $payments->links() }}
</div>
@endsection
