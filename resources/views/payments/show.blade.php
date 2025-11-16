@extends('layouts._partials.layout')

@section('title', __('Pagos'))
@section('subtitle', __('Pagos del tratamiento'))

@section('content')
<div class="flex flex-wrap justify-between items-center p-5 gap-2">
    <h1 class="title1">
        {{ $treatment->name ?? 'Unnamed treatment' }} - {{ $treatment->ci_patient }}
    </h1>

    @if($remaining > 0)
        <a href="{{ route('payments.create', $treatment->id) }}" class="botton1">{{ __('Registrar Pago') }}</a>
    @endif
</div>

<!-- Resumen del tratamiento -->
<div class="max-w-5xl mx-auto bg-white rounded-xl p-4 text-gray-900 shadow-md mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center sm:text-left">
        <div>
            <strong>{{ __('Total:') }}</strong> Bs. {{ number_format($treatment->amount, 2) }}
        </div>
        <div>
            <strong>{{ __('Pagado:') }}</strong> Bs. {{ number_format($paid, 2) }}
        </div>
        <div>
            <strong>{{ __('Restante:') }}</strong> Bs. {{ number_format($remaining, 2) }}
        </div>
    </div>
</div>

<!-- Historial de pagos -->
<h2 class="title2 text-center mb-4">{{ __('Historial de Pagos') }}</h2>

@if($payments->isEmpty())
    <p class="text-gray-600 text-center py-4">{{ __('Aún no se han registrado pagos.') }}</p>
@else
    <!-- Tabla desktop -->
    <div class="hidden sm:block max-w-6xl mx-auto bg-white rounded-xl p-3 text-gray-900 shadow-md">
        <div class="grid grid-cols-6 font-semibold border-b border-gray-300 pb-2 mb-2 text-center">
            <div>{{ __('Fecha') }}</div>
            <div>{{ __('Monto') }}</div>
            <div>{{ __('Método') }}</div>
            <div>{{ __('Detalles') }}</div>
            <div>{{ __('Registrado por') }}</div>
            <div>{{ __('Acciones') }}</div>
        </div>

        @foreach($payments as $p)
        <div class="grid grid-cols-6 border-b border-gray-200 py-2 text-center items-center">
            <div>{{ $p->created_at->format('d/m/Y H:i') }}</div>
            <div>Bs. {{ number_format($p->amount, 2) }}</div>
            <div>{{ $p->method ?? '-' }}</div>
            <div>{{ $p->notes ?? '-' }}</div>
            <div>{{ $p->creator->name ?? 'N/A' }}</div>
            <div class="flex justify-center gap-2">
                <form method="POST"
                    action="{{ route('payments.destroy', ['treatment' => $treatment->id, 'id' => $p->id]) }}"
                    onsubmit="return confirm('{{ __('¿Eliminar este pago?') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bottonDelete">{{ __('Eliminar') }}</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Mobile cards -->
    <div class="sm:hidden max-w-6xl mx-auto flex flex-col gap-3">
        @foreach($payments as $p)
        <div class="bg-white rounded-lg shadow-md p-4 flex flex-col gap-2 hover:shadow-lg transition">
            <div class="flex justify-between items-center">
                <div class="font-semibold">{{ $p->created_at->format('d/m/Y H:i') }}</div>
                <div>
                    <form method="POST"
                        action="{{ route('payments.destroy', ['treatment' => $treatment->id, 'id' => $p->id]) }}"
                        onsubmit="return confirm('{{ __('¿Eliminar este pago?') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bottonDelete text-sm">{{ __('Eliminar') }}</button>
                    </form>
                </div>
            </div>
            <div>{{ __('Monto:') }} Bs. {{ number_format($p->amount, 2) }}</div>
            <div>{{ __('Método:') }} {{ $p->method ?? '-' }}</div>
            <div>{{ __('Detalles:') }} {{ $p->notes ?? '-' }}</div>
            <div>{{ __('Registrado por:') }} {{ $p->creator->name ?? 'N/A' }}</div>

            @auth
                @if(auth()->user()->role === 'superadmin')
                <div class="mt-2 grid grid-cols-1 md:grid-cols-3 gap-2 text-sm text-gray-600">
                    <div><strong>{{ __('Clínica asignada:') }}</strong> {{ $p->clinic->name ?? 'Sin clínica' }}</div>
                    <div><strong>{{ __('Creado por:') }}</strong> {{ $p->creator->name ?? 'N/A' }}</div>
                    <div><strong>{{ __('Última edición por:') }}</strong> {{ $p->editor->name ?? 'Sin ediciones' }}</div>
                </div>
                @endif
            @endauth
        </div>
        @endforeach
    </div>
@endif

<div class="pt-4">
    {{ $payments->links() }}
</div>
@endsection
