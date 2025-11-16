<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tratamiento {{ $treatment->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            margin: 30px;
            color: #000;
        }
        header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        header img {
            max-height: 80px;
            margin-right: 15px;
        }
        header .clinic-info {
            font-size: 16px;
            font-weight: bold;
        }
        header .clinic-info p {
            margin: 2px 0;
            font-size: 13px;
        }
        h1 {
            text-align: center;
            margin: 0 0 20px 0;
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .right {
            text-align: right;
        }
        .totals td {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        footer {
            margin-top: 30px;
            text-align: center;
            font-size: 11px;
            color: #000;
        }
        .info {
            margin-bottom: 15px;
            font-size: 13px;
        }
        .patient-info p {
            margin: 2px 0;
        }
    </style>
</head>
<body>
    <header>
        @if($clinic->logo)
            <img src="{{ storage_path('app/' . $clinic->logo) }}" alt="Logo Clínica">
        @endif
        <div class="clinic-info">
            <p>{{ $clinic->name ?? 'Nombre de la Clínica' }}</p>
            <p>{{ $clinic->address ?? 'Dirección no registrada' }}</p>
            <p>Teléfono: {{ $clinic->phone ?? 'N/A' }}</p>
        </div>
    </header>

    <h1>PRESUPUESTO DE TRATAMIENTO</h1>

    <p class="info"><strong>Nota:</strong> Los valores presentados a continuación representan un <em>aproximado del costo del tratamiento necesario</em>. Pueden estar sujetos a cambios según el procedimiento final y materiales utilizados.</p>

    <div class="patient-info">
        <p><strong>Paciente:</strong> {{ $treatment->name ?? 'N/A' }}</p>
        <p><strong>CI:</strong> {{ $treatment->ci_patient }}</p>
        <p><strong>Fecha del presupuesto:</strong> {{ $treatment->created_at->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Descripción</th>
                <th class="right">Cantidad</th>
                <th class="right">Costo Unitario</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $budgetCodes = json_decode($treatment->budget_codes, true) ?? [];
            @endphp

            @foreach ($budgets as $budget)
                @php
                    $quantity = $budgetCodes[$budget->id] ?? 1;
                    $lineTotal = $budget->total_amount * $quantity;
                @endphp
                <tr>
                    <td>{{ $budget->budget }}</td>
                    <td>{{ $budget->description }}</td>
                    <td class="right">{{ $quantity }}</td>
                    <td class="right">{{ number_format($budget->total_amount, 2) }}</td>
                    <td class="right">{{ number_format($lineTotal, 2) }}</td>
                </tr>
            @endforeach

            <tr class="totals">
                <td colspan="4" class="right">Subtotal</td>
                <td class="right">{{ number_format($treatment->total_amount, 2) }}</td>
            </tr>
            <tr class="totals">
                <td colspan="4" class="right">Descuento</td>
                <td class="right">{{ number_format($treatment->discount, 2) }}</td>
            </tr>
            <tr class="totals">
                <td colspan="4" class="right">Total Final</td>
                <td class="right">{{ number_format($treatment->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <p><strong>Detalles del tratamiento:</strong> {{ $treatment->details ?? 'Sin información adicional' }}</p>

    <footer>
        <p>Emitido por: <strong>{{ $author }}</strong></p>
        <p>Fecha de emisión: {{ now()->format('d/m/Y H:i') }}</p>
    </footer>
</body>
</html>
