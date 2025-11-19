@extends('layouts._partials.layout')

@section('title', __('Pagos'))
@section('subtitle', __('Pagos del tratamiento'))

@section('content')
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-5 pb-1 gap-3">
    <h1 class="title1">{{ $treatment->name ?? 'Unnamed treatment' }} - {{ $treatment->ci_patient }}</h1>
    @if($remaining > 0)
    <a href="{{ route('payments.create', $treatment->id) }}" class="botton1">{{ __('Registrar Pago') }}</a>
    @endif
</div>


<div class="p-5 pb-1">
    <!-- Cuotas del plan de pagos -->
    @if($treatment->paymentPlan)

    @php
    $plan = $treatment->paymentPlan;
    $installments = $plan->installments_relation ?? collect();
    @endphp

    <h2 class="title2 text-center py-4">{{ __('Cuotas Generadas') }}</h2>

    @if($installments->isEmpty())
    <p class="text-gray-600 text-center">{{ __('No se han generado cuotas.') }}</p>
    @else
    <!-- Tabla para escritorio -->
    <div class="hidden sm:block max-w-5xl mx-auto bg-gray-100 rounded-lg p-4 shadow-sm">
        <div class="grid grid-cols-6 font-semibold border-b border-gray-300 pb-2 mb-2 text-center">
            <div>#</div>
            <div>{{ __('Monto') }}</div>
            <div>{{ __('Fecha de vencimiento') }}</div>
            <div>{{ __('Estado') }}</div>
            <div>{{ __('Pagado') }}</div>
            <div>{{ __('Acciones') }}</div>
        </div>

        @foreach($installments as $i => $cuota)
        <div class="grid grid-cols-6 border-b border-gray-200 py-2 text-center items-center hover:bg-gray-50 transition">
            <div>{{ $i + 1 }}</div>
            <div>Bs. {{ number_format($cuota->amount, 2) }}</div>
            <div>{{ \Carbon\Carbon::parse($cuota->due_date)->format('d/m/Y') }}</div>
            <div>
                @if($cuota->paid)
                <span class="text-green-700 font-semibold">{{ __('Pagado') }}</span>
                @else
                <span class="text-red-700 font-semibold">{{ __('Pendiente') }}</span>
                @endif
            </div>
            <div>Bs. {{ number_format($cuota->paid_amount ?? 0, 2) }}</div>
            <div class="flex justify-center gap-2">
                @if(!$cuota->paid)
                <a href="{{ route('payments.create', $treatment->id) }}" class="botton1 text-sm px-2">
                    {{ __('Registrar Pago') }}
                </a>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Tarjetas para móvil -->
    <div class="sm:hidden max-w-5xl mx-auto flex flex-col gap-3">
        @foreach($installments as $i => $cuota)
        <div class="bg-gray-100 rounded-lg shadow-md p-4 flex flex-col gap-2 hover:shadow-lg transition">
            <div class="flex justify-between items-center">
                <div class="font-semibold text-gray-700">{{ __('Cuota') }} #{{ $i + 1 }}</div>
                <div class="text-gray-500 text-sm">{{ \Carbon\Carbon::parse($cuota->due_date)->format('d/m/Y') }}</div>
            </div>
            <div><strong>{{ __('Monto') }}:</strong> Bs. {{ number_format($cuota->amount, 2) }}</div>
            <div><strong>{{ __('Estado') }}:</strong>
                @if($cuota->paid)
                <span class="text-green-700 font-semibold">{{ __('Pagado') }}</span>
                @else
                <span class="text-red-700 font-semibold">{{ __('Pendiente') }}</span>
                @endif
            </div>
            <div><strong>{{ __('Pagado') }}:</strong> Bs. {{ number_format($cuota->paid_amount ?? 0, 2) }}</div>
            @if(!$cuota->paid)
            <div class="flex justify-end">
                <a href="{{ route('payments.create', $treatment->id) }}" class="botton1 text-sm px-2">
                    {{ __('Registrar Pago') }}
                </a>
            </div>
            @endif
        </div>
        @endforeach
    </div>

    @endif

    @else
    <p class="mb-2">{{ __('El paciente no cuenta con plan de pagos') }}</p>
    <div class="flex justify-end">
        <a href="{{ route('payment_plans.create', $treatment->id) }}" class="botton3">
            {{ __('Generar Plan de Pagos') }}
        </a>
    </div>
    @endif

</div>




<div class="max-w-5xl mx-auto bg-white rounded-xl p-4 text-gray-900">
    <!-- Resumen del tratamiento -->
    <div class="mb-4 gap-4 text-center sm:text-left">
        <p class="txt"><strong>{{ __('Total') }}:</strong> Bs. {{ number_format($treatment->amount, 2) }}</p>
        <p class="txt"><strong>{{ __('Pagado') }}:</strong> Bs. {{ number_format($paid, 2) }}</p>
        <p class="txt"><strong>{{ __('Restante') }}:</strong> Bs. {{ number_format($remaining, 2) }}</p>
    </div>

    <h2 class="title2 text-center py-5">{{ __('Historial de Pagos') }}</h2>

    @if($payments->isEmpty())
    <p class="text-gray-600 text-center py-4">{{ __('Aún no se han registrado pagos.') }}</p>
    @else
    <!-- Tabla para escritorio -->
    <div class="hidden sm:block">
        <div class="grid grid-cols-6 font-semibold border-b border-gray-300 pb-2 mb-2 text-center">
            <div>{{ __('Fecha') }}</div>
            <div>{{ __('Monto') }}</div>
            <div>{{ __('Método') }}</div>
            <div>{{ __('Detalles') }}</div>
            <div>{{ __('Registrador') }}</div>
            <div>{{ __('Acciones') }}</div>
        </div>
        @foreach($payments as $p)
        <div class="grid grid-cols-6 border-b border-gray-200 py-2 text-center items-center hover:bg-gray-50 transition">
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

    <!-- Tarjetas para móvil -->
    <div class="sm:hidden flex flex-col gap-3">
        @foreach($payments as $p)
        <div class="bg-white rounded-lg shadow-md p-4 flex flex-col gap-2 hover:shadow-lg transition">
            <div class="flex justify-between items-center">
                <div class="font-semibold text-gray-700">{{ $p->created_at->format('d/m/Y H:i') }}</div>
                <div class="flex gap-2">
                    <form method="POST"
                        action="{{ route('payments.destroy', ['treatment' => $treatment->id, 'id' => $p->id]) }}"
                        onsubmit="return confirm('{{ __('¿Eliminar este pago?') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bottonDelete text-sm px-2 py-1">{{ __('Eliminar') }}</button>
                    </form>
                </div>
            </div>
            <div><strong>{{ __('Monto') }}:</strong> Bs. {{ number_format($p->amount, 2) }}</div>
            <div><strong>{{ __('Método') }}:</strong> {{ $p->method ?? '-' }}</div>
            <div><strong>{{ __('Detalles') }}:</strong> {{ $p->notes ?? '-' }}</div>
            <div><strong>{{ __('Registrador') }}:</strong> {{ $p->creator->name ?? 'N/A' }}</div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection