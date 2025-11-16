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

<!-- Desktop Table -->
<div class="hidden sm:block max-w-6xl mx-auto bg-white rounded-xl p-3 text-gray-900 shadow-md">
    <div class="grid grid-cols-7 gap-4 border-b border-gray-300 pb-2 mb-3 text-center">
        <h3 class="title4">{{ __('Fecha') }}</h3>
        <h3 class="title4">{{ __('Nombre') }}</h3>
        <h3 class="title4">{{ __('C.I.') }}</h3>
        <h3 class="title4">{{ __('Total') }}</h3>
        <h3 class="title4">{{ __('Monto') }}</h3>
        <h3 class="title4">{{ __('Método') }}</h3>
        <h3 class="title4">{{ __('Detalles') }}</h3>
    </div>

    @forelse($payments as $p)
    <div class="grid grid-cols-7 gap-4 items-center border-b border-gray-200 py-3 text-gray-800 hover:bg-gray-50 transition text-center">
        <div><a href="{{ route('payments.show',$p->treatment->id) }}" class="hover:text-cyan-600">{{ $p->created_at->format('d/m/Y H:i') }}</a></div>
        <div><a href="{{ route('payments.show',$p->treatment->id) }}" class="hover:text-cyan-600">{{ $p->treatment->name ?? 'Sin tratamiento' }}</a></div>
        <div><a href="{{ route('payments.show',$p->treatment->id) }}" class="hover:text-cyan-600">{{ $p->treatment->ci_patient ?? '-' }}</a></div>
        <div><a href="{{ route('payments.show',$p->treatment->id) }}" class="hover:text-cyan-600">Bs. {{ number_format($p->treatment->amount, 2) }}</a></div>
        <div><a href="{{ route('payments.show',$p->treatment->id) }}" class="hover:text-cyan-600">Bs. {{ number_format($p->amount, 2) }}</a></div>
        <div><a href="{{ route('payments.show',$p->treatment->id) }}" class="hover:text-cyan-600">{{ $p->method ?? '-' }}</a></div>
        <div><a href="{{ route('payments.show',$p->treatment->id) }}" class="hover:text-cyan-600">{{ $p->notes ?? '-' }}</a></div>
    </div>
    @empty
    <p class="text-gray-600 text-center py-4">{{ __('Aún no se han registrado pagos.') }}</p>
    @endforelse

    <div class="pt-4">
        {{ $payments->links() }}
    </div>
</div>

<!-- Mobile Cards -->
<div class="sm:hidden max-w-6xl mx-auto flex flex-col gap-3">
    @forelse($payments as $p)
    <div class="bg-white rounded-lg shadow-md p-4 flex flex-col gap-2 hover:shadow-lg transition">
        <div class="flex justify-between items-center">
            <div class="font-semibold text-gray-700">{{ $p->created_at->format('d/m/Y') }}</div>
            <a href="{{ route('payments.show',$p->treatment->id) }}" class="text-cyan-600 hover:underline text-sm font-medium">{{ __('Ver') }}</a>
        </div>
        <div class="text-gray-800 font-medium">{{ $p->treatment->name ?? 'Sin tratamiento' }}</div>
        <div class="text-gray-600">Monto: Bs. {{ number_format($p->amount,2) }}</div>
        <div class="text-gray-500 text-sm">Método: {{ $p->method ?? '-' }}</div>
    </div>
    @empty
    <p class="text-gray-600 text-center py-4">{{ __('Aún no se han registrado pagos.') }}</p>
    @endforelse

    <div class="pt-4">
        {{ $payments->links() }}
    </div>
</div>

@endsection
