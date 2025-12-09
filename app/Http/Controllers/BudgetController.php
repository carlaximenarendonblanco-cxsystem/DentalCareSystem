<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $query = Budget::with(['creator', 'editor']);

        if ($user->role !== 'superadmin') {
            $query->where('clinic_id', $user->clinic_id);
        }

        $budgets = $query->orderBy('budget', 'ASC')->paginate(10);

        return view('budgets.index', compact('budgets'));
    }

    public function create()
    {
        return view('budgets.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'budget' => 'required|string|max:255',
            'procedure' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $validated['clinic_id'] = Auth::user()->clinic_id;
        $validated['created_by'] = Auth::id();

        Budget::create($validated);

        return redirect()->route('budgets.index')->with('success', 'Presupuesto creado correctamente.');
    }

    public function show($id)
    {
        $budget = Budget::with(['creator', 'editor'])->findOrFail($id);
        return view('budgets.show', compact('budget'));
    }

    public function edit($id)
    {
        $budget = Budget::findOrFail($id);

        if (Auth::user()->role !== 'superadmin' && $budget->clinic_id !== Auth::user()->clinic_id) {
            abort(403, 'Acceso no autorizado');
        }

        return view('budgets.edit', compact('budget'));
    }

    public function update(Request $request, $id)
    {
        $budget = Budget::findOrFail($id);

        if (Auth::user()->role !== 'superadmin' && $budget->clinic_id !== Auth::user()->clinic_id) {
            abort(403, 'Acceso no autorizado');
        }

        $validated = $request->validate([
            'budget' => 'required|string|max:255',
            'procedure' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $validated['edit_by'] = Auth::id();

        $budget->update($validated);

        return redirect()->route('budgets.index')->with('success', 'Presupuesto actualizado correctamente.');
    }

    public function destroy($id)
    {
        $budget = Budget::findOrFail($id);

        if (Auth::user()->role !== 'superadmin' && $budget->clinic_id !== Auth::user()->clinic_id) {
            abort(403, 'Acceso no autorizado');
        }

        $budget->delete();

        return redirect()->route('budgets.index')->with('success', 'Presupuesto eliminado correctamente.');
    }
    public function search(Request $request)
    {
        $search = trim($request->input('search'));
        $user = Auth::user();

        $query = Budget::with(['creator', 'editor']);

        // Restringir por clínica si no es superadmin
        if ($user->role !== 'superadmin') {
            $query->where('clinic_id', $user->clinic_id);
        }

        // Aplicar búsqueda si hay texto
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('budget', 'like', "%{$search}%")
                    ->orWhere('procedure', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $budgets = $query->orderBy('budget', 'ASC')->paginate(10);

        return view('budgets.index', compact('budgets', 'search'));
    }
}
