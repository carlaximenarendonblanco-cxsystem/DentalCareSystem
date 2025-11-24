@extends('layouts._partials.layout')

@section('title', __('Generar Plan de Pagos'))
@section('subtitle')
    {{ __('Plan de Pagos del Tratamiento') }}
@endsection

@section('content')
<div class="bg-white rounded-lg max-w-5xl mx-auto p-6 text-gray-900 shadow-sm">

    <h1 class="title1 text-center mb-8">{{ __('Generar Plan de Pagos') }}</h1>

    <!-- Datos del paciente -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div>
            <label class="title4 block mb-1">{{ __('Paciente') }}:</label>
            <div class="p-3 border rounded-xl bg-gray-50 shadow-sm">
                {{ $treatment->name ?? 'N/A' }}
            </div>
        </div>
        <div>
            <label class="title4 block mb-1">{{ __('C.I.') }}:</label>
            <div class="p-3 border rounded-xl bg-gray-50 shadow-sm">
                {{ $treatment->ci_patient ?? 'N/A' }}
            </div>
        </div>
        <div>
            <label class="title4 block mb-1">{{ __('Monto Total') }}:</label>
            <div class="p-3 border rounded-xl bg-gray-50 shadow-sm">
                Bs. {{ number_format($treatment->amount, 2) }}
            </div>
        </div>
    </div>

    @if($treatment->paymentPlan)
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded mb-8">
            {{ __('Este paciente ya tiene un plan de pagos. Puedes verlo desde la página del plan de pagos.') }}
        </div>
    @endif

    <form action="{{ route('payment_plans.store', $treatment->id) }}" method="POST" class="space-y-6">
        @csrf

        <!-- Modo -->
        <div>
            <label class="title4 block mb-2">{{ __('Modo de generación') }}</label>
            <select name="mode" id="mode"
                class="w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:border-cyan-500 
                       focus:ring focus:ring-cyan-300 focus:ring-opacity-50 transition duration-200 ease-in-out bg-white">
                <option value="auto">{{ __('Automático (cuotas iguales)') }}</option>
                <option value="custom">{{ __('Personalizado') }}</option>
            </select>
        </div>

        <!-- Nombre del plan -->
        <div>
            <label class="title4 block mb-2">{{ __('Nombre del Plan (Opcional)') }}</label>
            <input type="text" name="name" placeholder="Ej: Plan básico"
                class="w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:border-cyan-500 
                       focus:ring focus:ring-cyan-300 focus:ring-opacity-50 transition duration-200 ease-in-out bg-white">
        </div>

        <!-- AUTOMÁTICO -->
        <div id="auto-section">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="title4 block mb-2">{{ __('Número de cuotas') }}</label>
                    <input type="number" id="auto_installments" name="auto_installments" min="1" value="1"
                        class="w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:border-cyan-500 
                               focus:ring focus:ring-cyan-300 focus:ring-opacity-50 transition duration-200 ease-in-out bg-white">
                </div>

                <div>
                    <label class="title4 block mb-2">{{ __('Monto por cuota') }}</label>
                    <input type="text" id="auto_amount" disabled
                        value="{{ number_format($treatment->amount, 2, '.', '') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm bg-gray-50">
                </div>

                <div>
                    <label class="title4 block mb-2">{{ __('Fecha de inicio') }}</label>
                    <input type="date" name="start_date" value="{{ date('Y-m-d') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:border-cyan-500 
                               focus:ring focus:ring-cyan-300 focus:ring-opacity-50 transition duration-200 ease-in-out bg-white">
                </div>
            </div>
        </div>

        <!-- PERSONALIZADO -->
        <div id="custom-section" class="hidden">
            <p class="text-gray-700 mb-3">{{ __('Agrega las cuotas manualmente:') }}</p>

            <div id="customList" class="flex flex-col gap-4"></div>

            <button type="button" id="addInstallment" class="botton3 w-48">
                {{ __('Agregar Cuota') }}
            </button>
        </div>

        <div class="flex justify-end gap-3 pt-4">
            <a href="{{ url()->previous() }}" class="botton3">{{ __('Volver') }}</a>
            <button type="submit" class="botton2">{{ __('Generar Plan') }}</button>
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

    // Cuotas personalizadas
    document.getElementById('addInstallment').addEventListener('click', () => {
        const list = document.getElementById('customList');
        const index = list.children.length;

        const row = document.createElement('div');
        row.className = "grid grid-cols-1 md:grid-cols-3 gap-4 border p-4 rounded-xl shadow-sm bg-gray-50";

        row.innerHTML = `
            <div>
                <label class="title4 block mb-2">{{ __('Monto') }}</label>
                <input type="number" step="0.01" min="0.1"
                    name="installments[${index}][amount]"
                    class="w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm bg-white" required>
            </div>
            <div>
                <label class="title4 block mb-2">{{ __('Fecha de vencimiento') }}</label>
                <input type="date"
                    name="installments[${index}][due_date]"
                    class="w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm bg-white" required>
            </div>
            <div class="flex items-end">
                <button type="button" class="bottonDelete">
                    {{ __('Eliminar') }}
                </button>
            </div>
        `;

        list.appendChild(row);

        row.querySelector('.removeInstallment').addEventListener('click', () => row.remove());
    });
</script>
@endsection
