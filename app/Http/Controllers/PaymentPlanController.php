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
        $plan = $treatment->paymentPlan()->with('installments')->first();

        // Asegurarse que $plan->installments siempre sea colección
        if ($plan) {
            $plan->installments = $plan->installments ?? collect();
        } else {
            $plan = new PaymentPlan(); // evitar errores en la vista
            $plan->installments = collect();
        }

        return view('payment_plans.show', compact('treatment', 'plan'));
    }

    /**
     * Guardar un nuevo plan de pagos
     */
    public function store(Request $request, Treatment $treatment)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'installments' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'amount_per_installment' => 'nullable|numeric|min:0',
        ]);

        if ($treatment->paymentPlan) {
            return redirect()->back()->with('error', 'El tratamiento ya tiene un plan de pagos.');
        }

        $installments = $request->installments;
        $amountPerInstallment = $request->amount_per_installment ?? round($treatment->amount / $installments, 2);

        // Crear plan
        $plan = PaymentPlan::create([
            'treatment_id' => $treatment->id,
            'name' => $request->name,
            'installments' => $installments,
            'amount_per_installment' => $amountPerInstallment,
            'start_date' => $request->start_date,
            'created_by' => Auth::id(),
        ]);

        // Generar cuotas
        for ($i = 1; $i <= $installments; $i++) {
            PaymentPlanInstallment::create([
                'payment_plan_id' => $plan->id,
                'number' => $i,
                'amount' => $amountPerInstallment,
                'due_date' => Carbon::parse($request->start_date)->addMonths($i - 1)->toDateString(),
            ]);
        }

        return redirect()->route('payment_plans.show', $treatment->id)
            ->with('success', 'Plan de pagos generado correctamente.');
    }

    /**
     * Editar una cuota
     */
    public function editInstallment(PaymentPlanInstallment $installment)
    {
        return view('payment_plans.edit_installment', compact('installment'));
    }

    /**
     * Actualizar cuota individual
     */
    public function updateInstallment(Request $request, PaymentPlanInstallment $installment)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
        ]);

        $installment->update([
            'amount' => $request->amount,
            'due_date' => $request->due_date,
        ]);

        return redirect()->route('payment_plans.show', $installment->paymentPlan->treatment_id)
            ->with('success', 'Cuota actualizada correctamente.');
    }

    /**
     * Eliminar plan de pagos completo junto a sus cuotas
     */
    public function destroy(PaymentPlan $plan)
    {
        // Eliminar cuotas asociadas
        $plan->installments()->delete();

        $plan->delete();
        return redirect()->back()->with('success', 'Plan de pagos eliminado.');
    }
}
