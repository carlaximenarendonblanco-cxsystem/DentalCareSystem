<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'superadmin') {
            $events = Event::orderBy('start_date', 'ASC')->get();
        } else {
            $events = Event::where('clinic_id', $user->clinic_id)
                ->orderBy('start_date', 'ASC')
                ->get();
        }

        return view('events.index', compact('events'));
    }

    public function create()
    {
        $user = Auth::user();

        // Filtrar doctores y radiólogos por clínica (menos el superadmin)
        if ($user->role === 'superadmin') {
            $doctors = User::where('role', 'doctor')->get();
            $radiologists = User::where('role', 'radiology')->get();
            $patients = Patient::all();
        } else {
            $doctors = User::where('role', 'doctor')
                ->where('clinic_id', $user->clinic_id)
                ->get();

            $radiologists = User::where('role', 'radiology')
                ->where('clinic_id', $user->clinic_id)
                ->get();

            $patients = Patient::where('clinic_id', $user->clinic_id)->get();
        }

        return view('events.create', compact('doctors', 'radiologists', 'patients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'event' => 'required|string',
            'start_date' => 'required|date',
            'duration_minutes' => 'required|integer|min:1',
            'room' => 'required|in:Consultorio 1,Consultorio 2',
            'patient_id' => 'required|exists:patients,id',
        ]);

        $start = Carbon::parse($request->start_date);
        $duration = $request->duration_minutes + 10;
        $end = $start->copy()->addMinutes($duration);

        // Verificar conflicto de horario por sala
        $conflict = Event::where('room', $request->room)
            ->where('clinic_id', Auth::user()->clinic_id)
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($query) use ($start, $end) {
                        $query->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            })
            ->exists();

        if ($conflict) {
            return back()->withErrors(['room' => 'La sala ya está ocupada en ese horario.'])->withInput();
        }

        Event::create([
            'event' => $request->event,
            'details' => $request->details,
            'start_date' => $start,
            'end_date' => $end,
            'duration_minutes' => $request->duration_minutes,
            'room' => $request->room,
            'assigned_doctor' => $request->assigned_doctor ?: $request->custom_doctor,
            'assigned_radiologist' => $request->assigned_radiologist ?: $request->custom_radiologist,
            'patient_id' => $request->patient_id,
            'clinic_id' => Auth::user()->clinic_id, // ← IMPORTANTE
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('events.index')->with('success', 'Cita creada');
    }

    public function calendar()
    {
        $user = Auth::user();

        if ($user->role === 'superadmin') {
            $all_events = Event::all();
        } else {
            $all_events = Event::where('clinic_id', $user->clinic_id)->get();
        }

        $events = [];

        foreach ($all_events as $event) {
            $events[] = [
                'id' => $event->id,
                'title' => $event->event,
                'start' => date('Y-m-d\TH:i:s', strtotime($event->start_date)),
                'end' => date('Y-m-d\TH:i:s', strtotime($event->end_date)),
                'room' => $event->room,
                'allDay' => false,
                'doctor' => $event->assignedDoctor->name ?? 'No asignado',
                'creator_name' => $event->creator->name ?? 'Sin información',
            ];
        }

        return view('events.index', compact('events'));
    }

    public function show($id)
    {
        $event = Event::findOrFail($id);

        // Bloquear acceso si no pertenece a la clínica
        if (Auth::user()->role !== 'superadmin' && $event->clinic_id !== Auth::user()->clinic_id) {
            abort(403, 'No tienes permiso para ver esta cita.');
        }

        return view('events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        if (Auth::user()->role !== 'superadmin' && $event->clinic_id !== Auth::user()->clinic_id) {
            abort(403, 'No tienes permiso para editar esta cita.');
        }

        $user = Auth::user();

        if ($user->role === 'superadmin') {
            $doctors = User::where('role', 'doctor')->get();
            $radiologists = User::where('role', 'radiology')->get();
            $patients = Patient::all();
        } else {
            $doctors = User::where('role', 'doctor')
                ->where('clinic_id', $user->clinic_id)
                ->get();

            $radiologists = User::where('role', 'radiology')
                ->where('clinic_id', $user->clinic_id)
                ->get();

            $patients = Patient::where('clinic_id', $user->clinic_id)->get();
        }

        return view('events.edit', compact('doctors', 'radiologists', 'patients', 'event'));
    }

    public function update(Request $request, Event $event)
    {
        if (Auth::user()->role !== 'superadmin' && $event->clinic_id !== Auth::user()->clinic_id) {
            abort(403, 'No tienes permiso para modificar esta cita.');
        }

        $start = Carbon::parse($request->start_date);
        $duration = $request->duration_minutes + 10;
        $end = $start->copy()->addMinutes($duration);

        $event->update([
            'event' => $request->event,
            'details' => $request->details,
            'start_date' => $start,
            'end_date' => $end,
            'duration_minutes' => $request->duration_minutes,
            'room' => $request->room,
            'assigned_doctor' => $request->assigned_doctor ?: $request->custom_doctor,
            'assigned_radiologist' => $request->assigned_radiologist ?: $request->custom_radiologist,
            'patient_id' => $request->patient_id,
        ]);

        return redirect()->route('events.index')->with('success', 'Información actualizada');
    }

    public function destroy(Event $event)
    {
        if (Auth::user()->role !== 'superadmin' && $event->clinic_id !== Auth::user()->clinic_id) {
            abort(403, 'No tienes permiso para eliminar esta cita.');
        }

        $event->delete();

        return redirect()->route('events.index')->with('danger', 'Cita eliminada');
    }
}
