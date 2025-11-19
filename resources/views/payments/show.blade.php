@extends('layouts._partials.layout')

@section('title', __('Pagos'))
@section('subtitle', __('Pagos del tratamiento'))

@section('content')

{{-- ENCABEZADO --}}
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-5 pb-1 gap-3">
    <h1 class="title1">{{ $treatment->name }} - {{ $treatment->ci_patient }}</h1>

    @if($remaining > 0)
        <a href="{{ route('payments.create', $treatment->id) }}" class="botton1">
            {{ __('Registrar Pago') }}
        </a>
    @endif
</div>


{{-- SECCIÓN DE PLAN DE PAGOS --}}
<div class="max-w-5xl mx-auto mb-4">

    {{-- SI NO TIENE PLAN → MENSAJE VERDE + BOTÓN --}}
    @if(!$treatment->paymentPlan)
        <div class="bg-green-100 border-l-4 border-green-700 text-green-900 p-4 rounded mb-4">
            <strong>{{ __('Este paciente no cuenta con plan de pagos') }}</strong>
        </div>

        <div class="flex justify-end mb-4">
            <a href="{{ route('payment_plans.create', $treatment->id) }}" 
               class="botton3 px-6 py-2 text-center">
                {{ __('Generar Plan de Pagos') }}
            </a>
        </div>

    {{-- SI YA TIENE PLAN → SE CARGA DIRECTO EL PLAN AQUÍ --}}
    @else
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded mb-4">
            <strong class="text-blue-900">{{ __('Plan de pagos activo') }}</strong>
        </div>

        {{-- PLAN DE PAGOS CARGADO DIRECTO --}}
        <div class="bg-white rounded-xl p-5 shadow mb-6">

            <h2 class="title2 text-center mb-4">{{ __('Plan de Pagos') }}</h2>

            <p><strong>{{ __('Paciente:') }}</strong> {{ $treatment->name }}</p>
            <p><strong>{{ __('C.I.:') }}</strong> {{ $treatment->ci_patient }}</p>
            <p><strong>{{ __('Monto total:') }}</strong> Bs. {{ number_format($treatment->amount, 2) }}</p>
            <p><strong>{{ __('Número de cuotas:') }}</strong> {{ $plan->installments_count }}</p>

            @if($plan->amount_per_installment)
                <p><strong>{{ __('Monto por cuota:') }}</strong> Bs. {{ number_format($plan->amount_per_installment, 2) }}</p>
            @endif

            <h3 class="title3 text-center mt-4 mb-2">{{ __('Cuotas') }}</h3>

            <div class="grid grid-cols-6 font-semibold border-b border-gray-300 pb-2 mb-2 text-center">
                <div>#</div>
                <div>{{ __('Monto') }}</div>
                <div>{{ __('Fecha de vencimiento') }}</div>
                <div>{{ __('Estado') }}</div>
                <div>{{ __('Pagado') }}</div>
                <div>{{ __('Acciones') }}</div>
            </div>

            @foreach($plan->installments_relation as $i => $cuota)
                <div class="grid grid-cols-6 border-b border-gray-200 py-2 text-center items-center">
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

                    <div>
                        @if(!$cuota->paid)
                            <a href="{{ route('payments.create', $treatment->id) }}" 
                               class="botton1 text-sm px-2">
                                {{ __('Registrar Pago') }}
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach

        </div>
    @endif
</div>
