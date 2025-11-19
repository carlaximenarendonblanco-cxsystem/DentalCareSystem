@extends('layouts._partials.layout')
@section('title', __('Presupuestos'))
@section('subtitle', __('Lista de Presupuestos'))

@section('content')
<div class="flex flex-wrap justify-between items-center p-5 gap-2">
    <form method="POST" action="{{ route('treatments.search') }}" class="flex gap-2 items-center flex-wrap">
        @csrf
        <input type="text" name="search" placeholder="{{ __('Buscar presupuesto...') }}"
            class="px-4 py-2 rounded-full border border-gray-300 text-gray-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 w-full sm:w-auto sm:flex-1 max-w-xs" />
        <input class="botton2 px-4 py-2 rounded-full" type="submit" value="{{ __('Buscar') }}" />
    </form>

    <div class="flex gap-2 flex-wrap">
        <a href="{{ route('treatments.create') }}" class="botton1">{{ __('Crear Presupuesto') }}</a>
    </div>
</div>

<h1 class="title1 text-center my-4">{{ __('Lista de Presupuestos') }}</h1>

<!-- Desktop Table -->
<div class="hidden sm:block max-w-6xl mx-auto bg-white rounded-xl p-3 text-gray-900 shadow-md">
    <div class="grid grid-cols-8 gap-4 border-b border-gray-300 pb-2 mb-3 text-center font-semibold">
        <div class="title4">{{ __('Paciente') }}</div>
        <div class="title4">{{ __('C.I.') }}</div>
        <div class="title4">{{ __('Total') }}</div>
        <div class="title4">{{ __('Descuento') }}</div>
        <div class="title4">{{ __('Costo Final') }}</div>
        <div class="title4">{{ __('Presupuestado por') }}</div>
        <div class="title4">{{ __('Pagos') }}</div>
        <div class="title4">{{ __('Acciones') }}</div>
    </div>

    @forelse($treatments as $treatment)
    <div class="grid grid-cols-8 gap-4 items-center border-b border-gray-200 py-3 text-gray-800 hover:bg-gray-50 transition text-center">
        <div><a href="{{ route('payments.show',$treatment->id) }}" class="hover:text-cyan-600">{{ $treatment->name ?? 'N/A' }}</a></div>
        <div><a href="{{ route('payments.show',$treatment->id) }}" class="hover:text-cyan-600">{{ $treatment->ci_patient ?? 'N/A' }}</a></div>
        <div><a href="{{ route('payments.show',$treatment->id) }}" class="hover:text-cyan-600">Bs. {{ number_format($treatment->total_amount, 2) }}</a></div>
        <div><a href="{{ route('payments.show',$treatment->id) }}" class="hover:text-cyan-600">Bs. {{ number_format($treatment->discount, 2) }}</a></div>
        <div><a href="{{ route('payments.show',$treatment->id) }}" class="hover:text-cyan-600">Bs. {{ number_format($treatment->amount, 2) }}</a></div>
        <div><a href="{{ route('payments.show',$treatment->id) }}" class="hover:text-cyan-600">{{ $treatment->creator->name ?? '—' }}</a></div>
        <div class="flex justify-end">
            <a href="{{ route('payments.show',$treatment->id) }}" class="botton3">{{ __('Ver Pagos') }}</a>
        </div>
        <div class="flex justify-center gap-1">
            @auth
            @if(Auth::user()->role === 'admin')  
            <form method="POST" action="{{ route('treatments.destroy', $treatment->id) }}" 
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
    <p class="text-gray-600 text-center py-4">{{ __('Aún no hay presupuestos registrados.') }}</p>
    @endforelse

    <div class="pt-4">
        {{ $treatments->links() }}
    </div>
</div>

<!-- Mobile Cards -->
<div class="sm:hidden max-w-6xl mx-auto flex flex-col gap-3">
    @forelse($treatments as $treatment)
    <div class="bg-white rounded-lg shadow-md p-4 flex flex-col gap-2 hover:shadow-lg transition">
        <div class="flex justify-between items-center">
            <div class="font-semibold text-gray-700">{{ $treatment->name ?? 'N/A' }}</div>
            <a href="{{ route('payments.show',$treatment->id) }}" class="text-cyan-600 hover:underline text-sm font-medium">{{ __('Ver Pagos') }}</a>
        </div>
        <div class="text-gray-600">C.I.: {{ $treatment->ci_patient ?? 'N/A' }}</div>
        <div class="text-gray-600">Total: Bs. {{ number_format($treatment->total_amount, 2) }}</div>
        <div class="text-gray-600">Descuento: Bs. {{ number_format($treatment->discount, 2) }}</div>
        <div class="text-gray-600 font-medium">Costo Final: Bs. {{ number_format($treatment->amount, 2) }}</div>
        <div class="text-gray-500 text-sm">Presupuestado por: {{ $treatment->creator->name ?? '—' }}</div>
        @auth
        @if(Auth::user()->role === 'admin')  
        <form method="POST" action="{{ route('treatments.destroy', $treatment->id) }}" 
              onsubmit="return confirm('{{ __('¿Estás seguro de que quieres eliminar este presupuesto?') }}');" class="mt-2">
            @csrf
            @method('DELETE')
            <input type="submit" value="{{ __('Eliminar') }}" class="bottonDelete cursor-pointer w-full"/>
        </form>
        @endif
        @endauth
    </div>
    @empty
    <p class="text-gray-600 text-center py-4">{{ __('Aún no hay presupuestos registrados.') }}</p>
    @endforelse

    <div class="pt-4">
        {{ $treatments->links() }}
    </div>
</div>
@endsection
