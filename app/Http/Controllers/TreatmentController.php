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

        if ($user->role === 'superadmin') {
            $treatments = Treatment::orderBy('id', 'DESC')->paginate(10);
        } else {
            $treatments = Treatment::where('clinic_id', $user->clinic_id)
                ->orderBy('id', 'DESC')
                ->paginate(10);
        }

        return view('treatments.index', compact('treatments'));
    }

    public function create()
    {
        $user = Auth::user();

        if ($user->role === 'superadmin') {
            $budgets = Budget::all();
        } else {
            $budgets = Budget::where('clinic_id', $user->clinic_id)->get();
        }

        return view('treatments.create', compact('budgets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'ci_patient' => 'required|numeric',
            'selected_budgets' => 'required|array',
            'quantity' => 'nullable|array',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|string|in:fixed,percentage',
            'details' => 'nullable|string|max:255',
        ]);

        $selectedBudgets = $request->input('selected_budgets', []);
        $quantities = $request->input('quantity', []);

        $user = Auth::user();
        $totalAmount = 0;
        $budgetCodes = [];

        foreach ($selectedBudgets as $id) {

            $budget = Budget::where('id', $id)
                ->when($user->role !== 'superadmin', function ($query) use ($user) {
                    return $query->where('clinic_id', $user->clinic_id);
                })
                ->first();

            if ($budget) {
                $quantity = isset($quantities[$id]) ? (int) $quantities[$id] : 1;
                $totalAmount += $budget->total_amount * $quantity;
                $budgetCodes[$id] = $quantity;
            }
        }

        $discountType = $request->input('discount_type', 'fixed');
        $discountValue = $request->input('discount', 0);
        $discount = ($discountType === 'percentage') ? ($discountValue / 100) * $totalAmount : $discountValue;
        $finalAmount = max($totalAmount - $discount, 0);

        $treatment = Treatment::create([
            'name' => $request->name,
            'ci_patient' => $request->ci_patient,
            'budget_codes' => json_encode($budgetCodes),
            'total_amount' => $totalAmount,
            'discount' => $discount,
            'amount' => $finalAmount,
            'details' => $request->details,
            'clinic_id' => $user->clinic_id, // <<-- CLÍNICA AGREGADA
            'pdf_path' => null,
        ]);

        $budgets = Budget::whereIn('id', array_keys($budgetCodes))->get();

        $pdf = Pdf::loadView('treatments.pdf', [
            'treatment' => $treatment,
            'budgets' => $budgets,
            'author' => $user->name ?? 'Unknown User',
        ])->setPaper('a4', 'portrait');

        $fileName = 'treatment_' . $treatment->id . '.pdf';
        $storageDir = 'treatments';
        $storagePath = $storageDir . '/' . $fileName;

        Storage::put($storagePath, $pdf->output());

        $treatment->update(['pdf_path' => $storagePath]);

        $fullPath = storage_path('app/' . $storagePath);

        if (ob_get_level()) {
            ob_end_clean();
        }

        return response()->download($fullPath, $fileName)->deleteFileAfterSend(false);
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

        return redirect()->route('treatments.index')->with('danger', 'Tratamiento eliminado con éxito.');
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $user = Auth::user();

        $treatments = Treatment::query()
            ->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('ci_patient', 'LIKE', '%' . $search . '%');
            })
            ->when($user->role !== 'superadmin', function ($query) use ($user) {
                return $query->where('clinic_id', $user->clinic_id);
            })
            ->get();

        return view('treatments.search', compact('treatments'));
    }
}
