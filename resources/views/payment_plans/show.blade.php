@extends('layouts._partials.layout')
@section('title', __('Plan de Pagos'))
@section('subtitle')
{{ __('Detalle del Plan de Pagos') }}
@endsection

@section('content')

<div class="flex justify-end pt-5 pr-5">
    <a href="{{ route('treatments.index') }}" class="botton1">{{ __('Presupuestos') }}</a>
</div>

<!-- Contenedor principal -->

<div class="max-w-5xl pt-2 mx-auto bg-white rounded-xl p-6 text-gray-900 dark:text-white">
    <div class="mt-4 mb-6">
        <h1 class="title1 text-center pb-5">{{ __('Plan de Pagos del Tratamiento') }}</h1>
    </div>

    <!-- Información general del tratamiento -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4 pb-5">
        <div class="flex gap-2">
            <h3 class="title4">{{ __('Paciente:') }}</h3><span class="txt">{{ $treatment->name ?? 'N/A' }}</span>
        </div>
        <div class="flex gap-2">
            <h3 class="title4">{{ __('C.I.:') }}</h3><span class="txt">{{ $treatment->ci_patient ?? 'N/A' }}</span>
        </div>
        <div class="flex gap-2">
            <h3 class="title4">{{ __('Monto total:') }}</h3><span class="txt">Bs. {{ number_format($treatment->amount, 2) }}</span>
        </div>
        <div class="flex gap-2">
            <h3 class="title4">{{ __('Número de cuotas:') }}</h3><span class="txt">{{ $plan->installments_count ?? 0 }}</span>
        </div>
        @if($plan->amount_per_installment)
        <div class="flex gap-2">
            <h3 class="title4">{{ __('Monto promedio por cuota:') }}</h3><span class="txt">Bs. {{ number_format($plan->amount_per_installment, 2) }}</span>
        </div>
        @endif
    </div>

    <!-- Cuotas -->
    <div class="mt-8">
        <h1 class="title1 text-center pb-4">{{ __('Cuotas Generadas') }}</h1>

        @if($plan->installments_relation->isEmpty())
        <p class="text-gray-700 pl-4 text-center">{{ __('No se han generado cuotas.') }}</p>
        @else
        <!-- Tabla escritorio -->
        <div class="hidden sm:block">
            <div class="grid grid-cols-4 gap-4 font-semibold border-b border-gray-300 pb-2 mb-2 text-center">
                <span>{{ __('Cuota') }}</span>
                <span>{{ __('Monto') }}</span>
                <span>{{ __('Fecha de vencimiento') }}</span>
                <span>{{ __('Acciones') }}</span>
            </div>

            @foreach($plan->installments_relation as $i => $cuota)
            <div class="grid grid-cols-4 gap-4 border-b border-gray-200 py-2 text-center items-center hover:bg-gray-50 transition">
                <div>{{ $i + 1 }}</div>
                <div>Bs. {{ number_format($cuota->amount, 2) }}</div>
                <div>{{ \Carbon\Carbon::parse($cuota->due_date)->format('d/m/Y') }}</div>
                <div>
                    @if(!$cuota->paid)
                    <a href="{{ route('payments.create', $treatment->id) }}" class="botton3">{{ __('Registrar Pago') }}</a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <!-- Tarjetas móvil -->
        <div class="sm:hidden flex flex-col gap-3">
            @foreach($plan->installments_relation as $i => $cuota)
            <div class="bg-white rounded-lg shadow-md p-4 flex flex-col gap-2 hover:shadow-lg transition">
                <div class="flex justify-between items-center">
                    <div class="font-semibold text-gray-700">{{ __('Cuota') }} {{ $i + 1 }}</div>
                    @if(!$cuota->paid)
                    <a href="{{ route('payments.create', $treatment->id) }}" class="botton3 text-sm px-2 py-1">{{ __('Registrar Pago') }}</a>
                    @endif
                </div>
                <div><strong>{{ __('Monto:') }}</strong> Bs. {{ number_format($cuota->amount, 2) }}</div>
                <div><strong>{{ __('Vencimiento:') }}</strong> {{ \Carbon\Carbon::parse($cuota->due_date)->format('d/m/Y') }}</div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    <div class="flex justify-end">
        <form action="{{ route('payment_plans.destroy', $treatment->id) }}" method="POST"
            onsubmit="return confirm('¿Seguro que deseas eliminar el plan de pagos? Esta acción eliminará todas las cuotas.')">
            @csrf
            @method('DELETE')
            <button class="botonDelete">Eliminar plan</button>
        </form>
    </div>
</div>
@endsection