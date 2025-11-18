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
            {{ __('Este paciente ya tiene un plan de pagos. Puedes verlo desde la página del plan de pagos.') }}
        </div>
    @endif

    <form action="{{ route('payment_plans.store', $treatment->id) }}" method="POST" class="flex flex-col gap-4">
        @csrf

        <!-- Tipo de Modo -->
        <div>
            <label class="title4">{{ __('Modo de generación') }}</label>
            <select name="mode" id="mode" class="input1">
                <option value="auto">{{ __('Automático (cuotas iguales)') }}</option>
                <option value="custom">{{ __('Personalizado') }}</option>
            </select>
        </div>

        <!-- Campo: nombre del plan -->
        <div>
            <label class="title4">{{ __('Nombre del Plan (Opcional)') }}</label>
            <input type="text" name="name" class="input1" placeholder="Ej: Plan básico">
        </div>

        <!-- AUTOMÁTICO -->
        <div id="auto-section">
            <div class="flex flex-col sm:flex-row gap-4">

                <div class="flex-1">
                    <label class="title4">{{ __('Número de cuotas') }}</label>
                    <input type="number" name="auto_installments" id="auto_installments" value="1" min="1" class="input1">
                </div>

                <div class="flex-1">
                    <label class="title4">{{ __('Monto por cuota (automático)') }}</label>
                    <input type="text" id="auto_amount" disabled 
                           value="{{ number_format($treatment->amount, 2, '.', '') }}" class="input1">
                </div>

                <div class="flex-1">
                    <label class="title4">{{ __('Fecha de inicio') }}</label>
                    <input type="date" name="start_date" value="{{ date('Y-m-d') }}" class="input1">
                </div>
            </div>
        </div>

        <!-- PERSONALIZADO -->
        <div id="custom-section" class="hidden">
            <p class="text-gray-700">{{ __('Agrega las cuotas manualmente:') }}</p>

            <div id="customList" class="flex flex-col gap-4"></div>

            <button type="button" id="addInstallment" class="botton2 w-40 mt-2">
                {{ __('Agregar Cuota') }}
            </button>
        </div>

        <div class="flex justify-end gap-2 mt-4">
            <a href="{{ url()->previous() }}" class="botton2">{{ __('Volver') }}</a>
            <button type="submit" class="botton1">{{ __('Generar Plan') }}</button>
        </div>

    </form>
</div>

<script>
    const modeSelect = document.getElementById('mode');
    const autoSection = document.getElementById('auto-section');
    const customSection = document.getElementById('custom-section');

    modeSelect.addEventListener('change', function () {
        if (this.value === 'auto') {
            autoSection.classList.remove('hidden');
            customSection.classList.add('hidden');
        } else {
            autoSection.classList.add('hidden');
            customSection.classList.remove('hidden');
        }
    });

    // Recalcular monto por cuota
    const totalAmount = {{ $treatment->amount }};
    const autoInstallments = document.getElementById('auto_installments');
    const autoAmount = document.getElementById('auto_amount');

    autoInstallments.addEventListener('input', () => {
        const n = parseInt(autoInstallments.value) || 1;
        autoAmount.value = (totalAmount / n).toFixed(2);
    });

    // PERSONALIZADO – agregar/duplicar cuotas
    document.getElementById('addInstallment').addEventListener('click', () => {
        const list = document.getElementById('customList');
        const index = list.children.length;

        const item = `
            <div class="flex flex-col sm:flex-row gap-4 p-3 border rounded">
                <div class="flex-1">
                    <label class="title4">{{ __('Monto') }}</label>
                    <input type="number" step="0.01" min="0.1" 
                        name="installments[${index}][amount]" class="input1" required>
                </div>
                <div class="flex-1">
                    <label class="title4">{{ __('Fecha de vencimiento') }}</label>
                    <input type="date" 
                        name="installments[${index}][due_date]" class="input1" required>
                </div>
            </div>
        `;

        list.insertAdjacentHTML('beforeend', item);
    });
</script>
@endsection
