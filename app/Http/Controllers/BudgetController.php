<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Budget;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'superadmin') {
            $budgets = Budget::orderBy('name_patient', 'ASC')->paginate(10);
        } else {
            $budgets = Budget::where('clinic_id', $user->clinic_id)
                ->orderBy('name_patient', 'ASC')
                ->paginate(10);
        }

        return view('budgets.index', compact('budgets'));
    }

    public function create()
    {
        return view('budgets.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'budget' => 'required|string|max:100',
            'procedure' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $data = $request->all();
        $data['clinic_id'] = Auth::user()->clinic_id;   // asignar clínica automáticamente
        $data['created_by'] = Auth::id();

        Budget::create($data);

        return redirect()->route('budgets.index')->with('success', 'Presupuesto creado exitosamente.');
    }

    public function show(Budget $budget)
    {
        return view('budgets.show', compact('budget'));
    }

    public function edit(Budget $budget)
    {
        return view('budgets.edit', compact('budget'));
    }

    public function update(Request $request, Budget $budget)
    {
        $request->validate([
            'budget' => 'required|string|max:100',
            'procedure' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $data = $request->all();
        $data['edit_by'] = Auth::id(); // registrar quien editó

        $budget->update($data);

        return redirect()->route('budgets.index')
            ->with('success', 'Presupuesto actualizado exitosamente.');
    }

    public function destroy(Budget $budget)
    {
        $budget->delete();

        return redirect()->route('budgets.index')
            ->with('danger', 'Presupuesto eliminado exitosamente.');
    }

    public function search(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search');

        $query = Budget::query();

        // Búsqueda por campos
        $query->where(function($q) use ($search) {
            $q->where('budget', 'LIKE', "%{$search}%")
              ->orWhere('procedure', 'LIKE', "%{$search}%");
        });

        // Aplicar filtro por clínica para todos excepto superadmin
        if ($user->role !== 'superadmin') {
            $query->where('clinic_id', $user->clinic_id);
        }

        $budgets = $query->paginate(10);

        return view('budgets.index', compact('budgets'));
    }
}
