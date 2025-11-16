@extends('layouts._partials.layout')
@section('title','Pagos')
@section('subtitle')
    {{ __('Pagos') }}
@endsection

@section('content')
<div class="flex flex-col md:flex-row justify-between items-center p-5 pb-1 gap-3 md:gap-0">
    <form method="POST" action="{{ route('payments.search', $treatment->id ?? 0) }}" class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
        @csrf
        <input type="text" name="search" placeholder="{{ __('Buscar pago...') }}" 
            class="px-4 py-2 rounded-full border border-gray-300 text-gray-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 w-full sm:w-auto"/>
        <input class="botton2 w-full sm:w-auto" type="submit" value="{{ __('Buscar') }}" />
    </form>

    <!-- Buttons -->
    <div class="flex flex-col sm:flex-row justify-end gap-2 w-full sm:w-auto">
        <a href="{{ route('treatments.index') }}" class="botton4 w-full sm:w-auto">{{ __('Tratamientos') }}</a>
        @isset($treatment)
        <a href="{{ route('payments.create', $treatment->id) }}" class="botton1 w-full sm:w-auto">{{ __('Añadir Pago') }}</a>
        @endisset
    </div>
</div>

<!-- Main title -->
<h1 class="title1 text-center my-4">{{ __('Lista de Pagos') }}</h1>

<!-- Payments table -->
<div class="max-w-6xl mx-auto bg-white rounded-xl p-3 text-gray-900 shadow-md">
    <!-- Table header: ocultar en móvil -->
    <div class="hidden md:grid grid-cols-7 gap-4 border-b border-gray-300 pb-2 mb-3 text-center font-semibold">
        <h3>{{ __('Fecha') }}</h3>
        <h3>{{ __('Nombre') }}</h3>
        <h3>{{ __('C.I.') }}</h3>
        <h3>{{ __('Total') }}</h3>
        <h3>{{ __('Monto') }}</h3>
        <h3>{{ __('Método') }}</h3>
        <h3>{{ __('Detalles') }}</h3>
    </div>

    <!-- Table body -->
    @forelse($payments as $p)
    <div class="grid md:grid-cols-7 gap-2 md:gap-4 items-center border-b border-gray-200 py-3 text-gray-800 hover:bg-gray-50 transition">
        <!-- Fecha -->
        <div class="flex justify-between md:justify-center">
            <span class="font-semibold md:hidden">{{ __('Fecha: ') }}</span>
            <a href="{{ route('payments.show',$p->treatment->id) }}" class="hover:text-cyan-600 text-center md:text-left">{{ $p->created_at->format('d/m/Y H:i') }}</a>
        </div>

        <!-- Nombre -->
        <div class="flex justify-between md:justify-center">
            <span class="font-semibold md:hidden">{{ __('Nombre: ') }}</span>
            <a href="{{ route('payments.show',$p->treatment->id) }}" class="hover:text-cyan-600 text-center md:text-left">{{ $p->treatment->name ?? 'Sin tratamiento' }}</a>
        </div>

        <!-- CI -->
        <div class="flex justify-between md:justify-center">
            <span class="font-semibold md:hidden">{{ __('C.I.: ') }}</span>
            <a href="{{ route('payments.show',$p->treatment->id) }}" class="hover:text-cyan-600 text-center md:text-left">{{ $p->treatment->ci_patient ?? '-' }}</a>
        </div>

        <!-- Total -->
        <div class="flex justify-between md:justify-center">
            <span class="font-semibold md:hidden">{{ __('Total: ') }}</span>
            <a href="{{ route('payments.show',$p->treatment->id) }}" class="hover:text-cyan-600 text-center md:text-left">Bs. {{ number_format($p->treatment->amount, 2) }}</a>
        </div>

        <!-- Monto -->
        <div class="flex justify-between md:justify-center">
            <span class="font-semibold md:hidden">{{ __('Monto: ') }}</span>
            <a href="{{ route('payments.show',$p->treatment->id) }}" class="hover:text-cyan-600 text-center md:text-left">Bs. {{ number_format($p->amount, 2) }}</a>
        </div>

        <!-- Método -->
        <div class="flex justify-between md:justify-center">
            <span class="font-semibold md:hidden">{{ __('Método: ') }}</span>
            <a href="{{ route('payments.show',$p->treatment->id) }}" class="hover:text-cyan-600 text-center md:text-left">{{ $p->method ?? '-' }}</a>
        </div>

        <!-- Detalles -->
        <div class="flex justify-between md:justify-center">
            <span class="font-semibold md:hidden">{{ __('Detalles: ') }}</span>
            <a href="{{ route('payments.show',$p->treatment->id) }}" class="hover:text-cyan-600 text-center md:text-left">{{ $p->notes ?? '-' }}</a>
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
