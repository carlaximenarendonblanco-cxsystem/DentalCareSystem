@extends('layouts._partials.layout')

@section('title', __('Generar Plan de Pagos'))
@section('subtitle')
    {{ __('Plan de Pagos del Tratamiento') }}
@endsection

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-xl p-6 text-gray-900">

    <h1 class="title1 text-center pb-5">{{ __('Generar Plan de Pagos') }}</h1>

    <div class="mb-6">
        <p><strong>{{ __('Paciente:') }}</strong> {{ $treatment->patient->name_patient ?? 'N/A' }}</p>
        <p><strong>{{ __('Tratamiento:') }}</strong> {{ $treatment->name ?? 'N/A' }}</p>
        <p><strong>{{ __('Monto total:') }}</strong> Bs. {{ number_format($treatment->amount, 2) }}</p>
    </div>

    @if($treatment->paymentPlan)
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6">
            {{ __('Este paciente ya tiene un plan de pagos. Puedes verlo o editarlo desde la página del plan de pagos.') }}
        </div>
    @endif

    <form action="{{ route('payment_plans.store', $treatment->id) }}" method="POST" class="flex flex-col gap-4">
        @csrf

        <div class="flex flex-col sm:flex-row gap-4">

            <div class="flex-1">
                <label class="title4">{{ __('Número de cuotas') }}</label>
                <input type="number" name="installments" value="1" min="1" class="input1" required>
            </div>

            <div class="flex-1">
                <label class="title4">{{ __('Fecha de inicio') }}</label>
                <input type="date" name="start_date" value="{{ date('Y-m-d') }}" class="input1" required>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-4">
            <a href="{{ url()->previous() }}" class="botton2">{{ __('Volver') }}</a>
            <button type="submit" class="botton1">{{ __('Generar Plan') }}</button>
        </div>
    </form>
</div>
@endsection
