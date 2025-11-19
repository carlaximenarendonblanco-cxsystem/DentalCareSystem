<?php

namespace App\Http\Controllers;

use App\Models\Treatment;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class TreatmentController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $query = Treatment::orderBy('id', 'DESC');

        if ($user->role !== 'superadmin') {
            $query->where('clinic_id', $user->clinic_id);
        }

        $treatments = $query->paginate(10);

        return view('treatments.index', compact('treatments'));
    }

    public function create()
    {
        $user = Auth::user();

        $budgets = ($user->role === 'superadmin')
            ? Budget::all()
            : Budget::where('clinic_id', $user->clinic_id)->get();

        return view('treatments.create', compact('budgets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'nullable|string|max:255',
            'ci_patient'        => 'required|numeric',
            'selected_budgets'  => 'required|array',
            'quantity'          => 'nullable|array',
            'discount'          => 'nullable|numeric|min:0',
            'discount_type'     => 'nullable|string|in:fixed,percentage',
            'details'           => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        // Validar clínica
        if ($user->role !== 'superadmin' && !$user->clinic_id) {
            return back()->withErrors(['error' => 'Este usuario no tiene clínica asignada.']);
        }

        $selectedBudgets = $request->input('selected_budgets', []);
        $quantities = $request->input('quantity', []);

        $totalAmount = 0;
        $budgetCodes = [];

        foreach ($selectedBudgets as $id) {

            $budget = Budget::when($user->role !== 'superadmin', function ($q) use ($user) {
                    $q->where('clinic_id', $user->clinic_id);
                })
                ->where('id', $id)
                ->first();

            if ($budget) {
                $qty = isset($quantities[$id]) ? (int)$quantities[$id] : 1;
                $totalAmount += $budget->total_amount * $qty;
                $budgetCodes[$id] = $qty;
            }
        }

        // Calcular descuentos
        $discountValue = $request->input('discount', 0);
        $discountType = $request->input('discount_type', 'fixed');

        $discount = ($discountType === 'percentage')
            ? ($discountValue / 100) * $totalAmount
            : $discountValue;

        $finalAmount = max($totalAmount - $discount, 0);

        // Crear el tratamiento
        $treatment = Treatment::create([
            'name'         => $request->name,
            'ci_patient'   => $request->ci_patient,
            'budget_codes' => json_encode($budgetCodes),
            'total_amount' => $totalAmount,
            'discount'     => $discount,
            'amount'       => $finalAmount,
            'details'      => $request->details,
            'clinic_id'    => $user->clinic_id,
            'created_by'   => Auth::id(), // <<<<<< AGREGADO
            'edit_by'      => Auth::id(), // <<<<<< AGREGADO
            'pdf_path'     => null,
        ]);

        // PDF
        $budgets = Budget::whereIn('id', array_keys($budgetCodes))->get();
        $clinic = $user->clinic;
        $pdf = Pdf::loadView('cotizacion.pdf', [
            'treatment' => $treatment,
            'budgets'   => $budgets,
            'author'    => $user->name,
            'clinic'    => $clinic,
        ])->setPaper('letter', 'portrait');

        $fileName = 'Cotizacion' . $treatment->name . $treatment->id . '.pdf';
        $storagePath = 'treatments/' . $fileName;

        Storage::put($storagePath, $pdf->output());

        $treatment->update(['pdf_path' => $storagePath]);

        if (ob_get_level()) {
            ob_end_clean();
        }

        return response()->download(storage_path('app/' . $storagePath), $fileName);
    }

    public function show($id)
    {
        $treatment = Treatment::findOrFail($id);
        $user = Auth::user();

        if ($user->role !== 'superadmin' && $treatment->clinic_id !== $user->clinic_id) {
            abort(403, 'No autorizado');
        }

        $budgetIds = array_keys(json_decode($treatment->budget_codes, true) ?? []);
        $budgets = Budget::whereIn('id', $budgetIds)->get();

        return view('treatments.show', compact('treatment', 'budgets'));
    }

    public function destroy($id)
    {
        $treatment = Treatment::findOrFail($id);

        $user = Auth::user();
        if ($user->role !== 'superadmin' && $treatment->clinic_id !== $user->clinic_id) {
            abort(403, 'No autorizado');
        }

        if ($treatment->pdf_path && Storage::exists($treatment->pdf_path)) {
            Storage::delete($treatment->pdf_path);
        }

        $treatment->delete();

        return redirect()->route('treatments.index')->with('danger', 'Tratamiento eliminado correctamente.');
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $user = Auth::user();

        $treatments = Treatment::where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%$search%")
                    ->orWhere('ci_patient', 'LIKE', "%$search%");
            })
            ->when($user->role !== 'superadmin', function ($q) use ($user) {
                $q->where('clinic_id', $user->clinic_id);
            })
            ->get();

        return view('treatments.search', compact('treatments'));
    }
}