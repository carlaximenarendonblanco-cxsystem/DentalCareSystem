@extends('layouts._partials.layout')
@section('title','Pagos')
@section('subtitle')
    {{ __('Pagos') }}
@endsection

@section('content')
<div class="flex flex-wrap justify-between items-center p-5 gap-2">
    <form method="POST" action="{{ route('payments.search', $treatment->id ?? 0) }}" class="flex gap-2 flex-1 min-w-[200px]">
        @csrf
        <input type="text" name="search" placeholder="{{ __('Buscar pago...') }}" 
            class="px-4 py-2 rounded-full border border-gray-300 text-gray-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 flex-1"/>
        <input class="botton2" type="submit" value="{{ __('Buscar') }}" />
    </form>

    <div class="flex gap-2 flex-wrap">
        <a href="{{ route('treatments.index') }}" class="botton4">{{ __('Tratamientos') }}</a>
        @isset($treatment)
        <a href="{{ route('payments.create', $treatment->id) }}" class="botton1">{{ __('Añadir Pago') }}</a>
        @endisset
    </div>
</div>

<h1 class="title1 text-center my-4">{{ __('Lista de Pagos') }}</h1>

<!-- Payments list -->
<div class="max-w-6xl mx-auto flex flex-col gap-3">

    @forelse($payments as $p)
    <div class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <!-- Información principal -->
        <div class="flex flex-col sm:flex-row sm:gap-4 flex-1">
            <div class="font-semibold text-gray-700">{{ $p->created_at->format('d/m/Y') }}</div>
            <div class="text-gray-800 truncate">{{ $p->treatment->name ?? 'Sin tratamiento' }}</div>
            <div class="text-gray-600 sm:hidden">Bs. {{ number_format($p->amount,2) }}</div>
        </div>

        <!-- Información secundaria visible solo en desktop -->
        <div class="hidden sm:flex sm:items-center gap-4 text-gray-600">
            <div>CI: {{ $p->treatment->ci_patient ?? '-' }}</div>
            <div>Total: Bs. {{ number_format($p->treatment->amount,2) }}</div>
            <div>Método: {{ $p->method ?? '-' }}</div>
            <div>Notas: {{ $p->notes ?? '-' }}</div>
        </div>

        <!-- Link al detalle -->
        <div>
            <a href="{{ route('payments.show',$p->treatment->id) }}" 
               class="text-cyan-600 hover:underline text-sm font-medium">{{ __('Ver') }}</a>
        </div>
    </div>
    @empty
    <p class="text-gray-600 text-center py-4">{{ __('Aún no se han registrado pagos.') }}</p>
    @endforelse

    <!-- Pagination -->
    <div class="pt-4">
        {{ $payments->links() }}
    </div>
</div>
@endsection
