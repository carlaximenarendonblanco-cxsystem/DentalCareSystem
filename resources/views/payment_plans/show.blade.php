@extends('layouts._partials.layout')

@section('title', __('Plan de Pagos'))
@section('subtitle')
    {{ __('Detalle del Plan de Pagos') }}
@endsection

@section('content')
<div class="max-w-5xl mx-auto bg-white rounded-xl p-6 text-gray-900">

    <div class="flex justify-between items-center mb-6">
        <h1 class="title1">{{ __('Plan de Pagos del Tratamiento') }}</h1>
        <a href="{{ url()->previous() }}" class="botton2">{{ __('Volver') }}</a>
    </div>

    <div class="mb-4">
        <p><strong>{{ __('Paciente:') }}</strong> {{ $treatment->name ?? 'N/A' }}</p>
        <p><strong>{{ __('C.I.:') }}</strong> {{ $treatment->ci_patient ?? 'N/A' }}</p>
        <p><strong>{{ __('Tratamiento:') }}</strong> {{ $treatment->name ?? 'N/A' }}</p>
        <p><strong>{{ __('Monto total:') }}</strong> Bs. {{ number_format($treatment->amount, 2) }}</p>
        <p><strong>{{ __('Número de cuotas:') }}</strong> {{ $plan->installments ?? 0 }}</p>
        @if($plan->amount_per_installment)
            <p><strong>{{ __('Monto por cuota:') }}</strong> Bs. {{ number_format($plan->amount_per_installment, 2) }}</p>
        @endif
    </div>

    <h2 class="title2 text-center py-4">{{ __('Cuotas Generadas') }}</h2>

    @if($plan->installments->isEmpty())
        <p class="text-gray-600 text-center">{{ __('No se han generado cuotas.') }}</p>
    @else
        <div class="grid grid-cols-6 font-semibold border-b border-gray-300 pb-2 mb-2 text-center">
            <div>#</div>
            <div>{{ __('Monto') }}</div>
            <div>{{ __('Fecha de vencimiento') }}</div>
            <div>{{ __('Estado') }}</div>
            <div>{{ __('Pagado') }}</div>
            <div>{{ __('Acciones') }}</div>
        </div>

        @foreach($plan->installments as $i => $cuota)
            <div class="grid grid-cols-6 border-b border-gray-200 py-2 text-center items-center hover:bg-gray-50">

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

                <div class="flex justify-center">
                    @if(!$cuota->paid)
                        <a href="{{ route('payments.create', $treatment->id) }}" class="botton1 text-sm px-2">
                            {{ __('Registrar Pago') }}
                        </a>
                    @endif
                </div>

            </div>
        @endforeach
    @endif

</div>
@endsection
