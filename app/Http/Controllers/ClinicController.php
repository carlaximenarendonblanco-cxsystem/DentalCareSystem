<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $clinics = Clinic::paginate(10);
        return view('clinics.index', compact('clinics'));
    }

    public function create()
    {
        return view('clinics.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'address'     => 'nullable|string|max:255',
            'phone'       => 'nullable|string|max:20',
            'logo'        => 'nullable|image|max:2048',
            'rooms_count' => 'required|integer|min:1',
        ]);

        // Convertir el logo a base64 si existe
        if ($request->hasFile('logo')) {
            $image = file_get_contents($request->file('logo')->getRealPath());
            $validated['logo'] = base64_encode($image);
        }

        Clinic::create($validated);

        return redirect()->route('clinics.index')
            ->with('success', 'Clínica creada exitosamente');
    }

    public function show(Clinic $clinic)
    {
        return view('clinics.show', compact('clinic'));
    }

    public function edit(Clinic $clinic)
    {
        return view('clinics.edit', compact('clinic'));
    }

    public function update(Request $request, Clinic $clinic)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'address'     => 'nullable|string|max:255',
            'phone'       => 'nullable|string|max:20',
            'logo'        => 'nullable|image|max:2048',
            'rooms_count' => 'required|integer|min:1',
        ]);

        // Reemplazar logo solo si se sube uno nuevo
        if ($request->hasFile('logo')) {
            $image = file_get_contents($request->file('logo')->getRealPath());
            $validated['logo'] = base64_encode($image);
        }

        $clinic->update($validated);

        return redirect()->route('clinics.index')
            ->with('success', 'Clínica actualizada exitosamente');
    }

    public function destroy(Clinic $clinic)
    {
        $clinic->delete();

        return redirect()->route('clinics.index')
            ->with('danger', 'Clínica eliminada');
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $clinics = Clinic::where('name', 'LIKE', "%$search%")
            ->orWhere('phone', 'LIKE', "%$search%")
            ->paginate(10)
            ->withQueryString();

        return view('clinics.search', compact('clinics'));
    }
}
