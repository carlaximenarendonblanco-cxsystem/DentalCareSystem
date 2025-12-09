<?php

namespace App\Http\Controllers;

use App\Models\Treatment;
use App\Models\PaymentPlan;
use App\Models\PaymentPlanInstallment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PaymentPlanController extends Controller
{
    /**
     * Mostrar formulario para crear un plan de pagos
     */
    public function create(Treatment $treatment)
    {
        if ($treatment->paymentPlan) {
            return redirect()->route('payment_plans.show', $treatment->id)
                ->with('info', 'Este tratamiento ya tiene un plan de pagos.');
        }

        return view('payment_plans.create', compact('treatment'));
    }

    /**
     * Mostrar el plan de pagos y sus cuotas
     */
    public function show(Treatment $treatment)
    {
        $plan = $treatment->paymentPlan;

        if (!$plan) {
            // Si no hay plan, crear uno temporal con colección vacía
            $plan = new PaymentPlan();
            $plan->installments_relation = collect();  // colección vacía de cuotas
            $plan->installments_count = 0;
            $plan->amount_per_installment = 0;
        } else {
            // Obtener la relación de cuotas
            $plan->installments_relation = $plan->installments()->get();

            // Número de cuotas generadas
            $plan->installments_count = $plan->installments_relation->count();

            // Calcular monto por cuota si no está definido
            if (!$plan->amount_per_installment && $plan->installments_count > 0) {
                $plan->amount_per_installment = round(
                    $plan->installments_relation->sum('amount') / $plan->installments_count,
                    2
                );
            }
        }

        return view('payment_plans.show', [
            'treatment' => $treatment,
            'plan' => $plan,
        ]);
    }


    /**
     * Guardar plan de pagos
     */
    public function store(Request $request, Treatment $treatment)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'mode' => 'required|in:auto,custom',

            // AUTOMÁTICO
            'auto_installments' => 'required_if:mode,auto|integer|min:1',
            'start_date' => 'required_if:mode,auto|date',

            // PERSONALIZADO
            'installments' => 'required_if:mode,custom|array|min:1',
            'installments.*.amount' => 'required_if:mode,custom|numeric|min:0.1',
            'installments.*.due_date' => 'required_if:mode,custom|date',
        ]);

        if ($treatment->paymentPlan) {
            return back()->with('error', 'Este tratamiento ya tiene un plan de pagos.');
        }

        return $request->mode === 'auto'
            ? $this->storeAutomatic($request, $treatment)
            : $this->storeCustom($request, $treatment);
    }

    /**
     * Guardar plan automático
     */
    private function storeAutomatic(Request $request, Treatment $treatment)
    {
        $installments = $request->auto_installments;
        $amountPerInstallment = round($treatment->amount / $installments, 2);

        $plan = PaymentPlan::create([
            'treatment_id' => $treatment->id,
            'name' => $request->name,
            'installments' => $installments,
            'amount_per_installment' => $amountPerInstallment,
            'start_date' => $request->start_date,
            'created_by' => Auth::id(),
        ]);

        for ($i = 1; $i <= $installments; $i++) {
            PaymentPlanInstallment::create([
                'payment_plan_id' => $plan->id,
                'number' => $i,
                'amount' => $amountPerInstallment,
                'due_date' => Carbon::parse($request->start_date)->addMonths($i - 1),
            ]);
        }

        return redirect()->route('payment_plans.show', $treatment->id)
            ->with('success', 'Plan automático generado.');
    }

    /**
     * Guardar plan personalizado
     */
    private function storeCustom(Request $request, Treatment $treatment)
    {
        $total = collect($request->installments)->sum('amount');

        if (round($total, 2) !== round($treatment->amount, 2)) {
            return back()->with('error', 'La suma de montos no coincide con el costo total del tratamiento.');
        }

        $plan = PaymentPlan::create([
            'treatment_id' => $treatment->id,
            'name' => $request->name,
            'installments' => count($request->installments),
            'start_date' => $request->installments[0]['due_date'],
            'created_by' => Auth::id(),
        ]);

        foreach ($request->installments as $i => $data) {
            PaymentPlanInstallment::create([
                'payment_plan_id' => $plan->id,
                'number' => $i + 1,
                'amount' => $data['amount'],
                'due_date' => $data['due_date'],
            ]);
        }

        return redirect()->route('payment_plans.show', $treatment->id)
            ->with('success', 'Plan personalizado generado.');
    }
    public function destroy(Treatment $treatment)
    {
        $plan = $treatment->paymentPlan;

        if (!$plan) {
            return back()->with('error', 'Este tratamiento no tiene un plan de pagos.');
        }
        if ($plan->installments()->whereNotNull('paid_at')->exists()) {
            return back()->with('error', 'No se puede eliminar: existen cuotas pagadas.');
        }
        $plan->delete();
        return redirect()->route('payment_plans.show', $treatment->id)
            ->with('danger', 'Plan de pagos eliminado correctamente.');
    }
}
