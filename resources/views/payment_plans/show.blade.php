@extends('layouts._partials.layout')

@section('title', __('Pagos'))
@section('subtitle', __('Pagos del tratamiento'))

@section('content')
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-5 pb-1 gap-3">
    <h1 class="title1">{{ $treatment->name ?? 'Unnamed treatment' }} - {{ $treatment->ci_patient }}</h1>

    @if($remaining > 0)
        @if(!$treatment->paymentPlan)
            <a href="{{ route('payment_plans.create', $treatment->id) }}" class="botton1">
                {{ __('Generar Plan de Pagos') }}
            </a>
        @else
            <a href="{{ route('payments.create', $treatment->id) }}" class="botton1">
                {{ __('Registrar Pago') }}
            </a>
        @endif
    @endif
</div>

<div class="flex justify-end mb-3">
    @if($treatment->paymentPlan)
        {{-- Ya no mostramos "Ver plan de pagos" --}}
    @else
        <a href="{{ route('payment_plans.create', $treatment->id) }}" class="botton3">
            {{ __('Generar Plan de Pagos') }}
        </a>
    @endif
</div>

<div class="max-w-5xl mx-auto bg-white rounded-xl p-4 text-gray-900">

    <!-- Resumen del tratamiento -->
    <div class="mb-4 gap-4 text-center sm:text-left">
        <p class="txt"><strong>{{ __('Total') }}:</strong> Bs. {{ number_format($treatment->amount, 2) }}</p>
        <p class="txt"><strong>{{ __('Pagado') }}:</strong> Bs. {{ number_format($paid, 2) }}</p>
        <p class="txt"><strong>{{ __('Restante') }}:</strong> Bs. {{ number_format($remaining, 2) }}</p>
    </div>

    {{-- ========================================= --}}
    {{--        BLOQUE DEL PLAN DE PAGOS           --}}
    {{-- ========================================= --}}
    @if($treatment->paymentPlan)
        @php $plan = $treatment->paymentPlan; @endphp

        <h2 class="title2 text-center py-4">{{ __('Plan de Pagos') }}</h2>

        <div class="mb-4 text-sm">
            <p><strong>{{ __('Monto total:') }}</strong> Bs. {{ number_format($treatment->amount, 2) }}</p>
            <p><strong>{{ __('Número de cuotas:') }}</strong> {{ $plan->installments_count }}</p>

            @if($plan->amount_per_installment)
                <p><strong>{{ __('Monto por cuota:') }}</strong> Bs. {{ number_format($plan->amount_per_installment, 2) }}</p>
            @endif
        </div>

        <h3 class="font-semibold text-lg py-3">{{ __('Cuotas generadas') }}</h3>

        @if($plan->installments_relation->isEmpty())
            <p class="text-gray-600 text-center">{{ __('No se han generado cuotas.') }}</p>
        @else
            <div class="grid grid-cols-5 font-semibold border-b border-gray-300 pb-2 mb-2 text-center">
                <div>#</div>
                <div>{{ __('Monto') }}</div>
                <div>{{ __('Fecha de vencimiento') }}</div>
                <div>{{ __('Estado') }}</div>
                <div>{{ __('Pagado') }}</div>
            </div>

            @foreach($plan->installments_relation as $i => $cuota)
                <div class="grid grid-cols-5 border-b border-gray-200 py-2 text-center items-center">
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
                </div>
            @endforeach
        @endif

    @else
        <h2 class="title2 text-center py-4 text-red-600">
            {{ __('El paciente no tiene un plan de pagos asignado') }}
        </h2>

        <div class="text-center mb-5">
            <a href="{{ route('payment_plans.create', $treatment->id) }}" class="botton1">
                {{ __('Generar Plan de Pagos') }}
            </a>
        </div>
    @endif

    {{-- ========================================= --}}
    {{--           HISTORIAL DE PAGOS              --}}
    {{-- ========================================= --}}
    <h2 class="title2 text-center py-5">{{ __('Historial de Pagos') }}</h2>

    @if($payments->isEmpty())
        <p class="text-gray-600 text-center py-4">{{ __('Aún no se han registrado pagos.') }}</p>
    @else
        {{-- Tabla escritorio --}}
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

        {{-- Tarjetas móvil --}}
        <div class="sm:hidden flex flex-col gap-3">
            @foreach($payments as $p)
                <div class="bg-white rounded-lg shadow-md p-4 flex flex-col gap-2">
                    <div class="flex justify-between items-center">
                        <span class="font-semibold">{{ $p->created_at->format('d/m/Y H:i') }}</span>

                        <form method="POST"
                              action="{{ route('payments.destroy', ['treatment' => $treatment->id, 'id' => $p->id]) }}"
                              onsubmit="return confirm('{{ __('¿Eliminar este pago?') }}');">
                            @csrf
                            @method('DELETE')
                            <button class="bottonDelete text-sm px-2 py-1">{{ __('Eliminar') }}</button>
                        </form>
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
