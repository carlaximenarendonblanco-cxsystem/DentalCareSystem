<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Treatment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Aplica filtro por rol/clinic al query pasado.
     * Si el usuario es superadmin devuelve el query sin cambios.
     * Si no, agrega where('clinic_id', user->clinic_id).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function scopeByRole($query)
    {
        $user = Auth::user();

        if (!$user) {
            // por seguridad, devolver query vacío si no hay user
            return $query->whereRaw('1 = 0');
        }

        if ($user->role === 'superadmin') {
            return $query;
        }

        return $query->where('clinic_id', $user->clinic_id);
    }

    public function index()
    {
        $payments = $this->scopeByRole(Payment::query())
            ->with('treatment')
            ->orderBy('created_at', 'desc')
            ->simplePaginate(25);

        return view('payments.index', compact('payments'));
    }

    public function show($treatment_id)
    {
        // Obtener tratamiento respetando scope
        $treatment = $this->scopeByRole(Treatment::query())->findOrFail($treatment_id);

        // Si llegamos hasta aquí, el usuario puede ver ese tratamiento
        $payments = $treatment->payments()->latest()->get();
        $paid = $payments->sum('amount');
        $remaining = $treatment->amount - $paid;

        return view('payments.show', compact('treatment', 'payments', 'paid', 'remaining'));
    }

    public function create($treatment_id)
    {
        $treatment = $this->scopeByRole(Treatment::query())->findOrFail($treatment_id);

        $paid = $treatment->payments()->sum('amount');
        $remaining = $treatment->amount - $paid;

        return view('payments.create', compact('treatment', 'paid', 'remaining'));
    }

    public function store(Request $request, $treatment_id)
    {
        // Obtener tratamiento dentro del scope
        $treatment = $this->scopeByRole(Treatment::query())->findOrFail($treatment_id);

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'nullable|string|max:50',
            'notes'  => 'nullable|string|max:255',
        ]);

        $paid = $treatment->payments()->sum('amount');
        $remaining = $treatment->amount - $paid;

        if ($data['amount'] > $remaining) {
            return back()->withInput()->withErrors(['amount' => 'El pago excede el monto restante.']);
        }

        // Guardar con clinic_id y created_by
        $payment = Payment::create([
            'treatment_id' => $treatment->id,
            'clinic_id'    => $treatment->clinic_id,
            'amount'       => $data['amount'],
            'method'       => $data['method'] ?? 'Efectivo',
            'notes'        => $data['notes'] ?? null,
            'created_by'   => Auth::id(),
        ]);

        return redirect()->route('payments.show', $treatment->id)
            ->with('success', 'Pago registrado correctamente.');
    }

    public function destroy($treatment_id, $id)
    {
        // localizamos el pago solo dentro del scope
        $payment = $this->scopeByRole(Payment::query())
            ->where('treatment_id', $treatment_id)
            ->findOrFail($id);

        $payment->delete();

        return back()->with('danger', 'Pago eliminado correctamente.');
    }

    public function search(Request $request, $treatment_id = null)
    {
        $search = trim($request->input('search'));

        $query = $this->scopeByRole(Payment::query())->with('treatment');

        if ($treatment_id) {
            $query->where('treatment_id', $treatment_id);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('treatment', function ($t) use ($search) {
                    $t->where('name', 'like', "%{$search}%")
                      ->orWhere('ci_patient', 'like', "%{$search}%");
                })
                ->orWhere('method', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $payments = $query->latest()->paginate(20);

        return view('payments.search', compact('payments', 'search', 'treatment_id'));
    }
}
