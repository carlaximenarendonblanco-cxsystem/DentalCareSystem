<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MultimediaFile;
use App\Models\Patient;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Google\Service\Drive\DriveFile;

class MultimediaFileController extends Controller
{
    // Carpeta de Google Drive donde se subirán los archivos
    private $driveFolderId = '14TaS42svjlWVzQNwa-mdcyOtWRnqyDz6';

    private function getDriveService()
    {
        $client = new GoogleClient();
        $client->setAuthConfig(storage_path('app/google-drive/sixth-starlight-480520-h9-bd9751a4ceab.json'));
        $client->addScope(GoogleDrive::DRIVE);
        $client->setAccessType('offline');

        return new GoogleDrive($client);
    }

    public function index()
    {
        $query = MultimediaFile::with('patient')->latest();

        if (auth()->user()->role !== 'super_admin') {
            $clinicId = auth()->user()->clinic_id;
            $query->where('clinic_id', $clinicId);
        }

        $studies = $query->paginate(10);
        return view('multimedia.index', compact('studies'));
    }

    public function create()
    {
        $patients = Patient::all();
        return view('multimedia.create', compact('patients'));
    }

    public function edit(MultimediaFile $multimedia)
    {
        return view('multimedia.edit', compact('multimedia'));
    }

    public function update(Request $request, MultimediaFile $multimedia)
    {
        $validated = $request->validate([
            'name_patient' => 'required|string|max:255',
            'ci_patient' => 'required|max:50',
            'study_type' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $multimedia->fill($validated);
        $multimedia->edit_by = auth()->id();

        if ($multimedia->isDirty()) {
            $multimedia->save();
            return redirect()->route('multimedia.index')
                ->with('success', 'Información del estudio actualizada correctamente.');
        }

        return redirect()->route('multimedia.index')
            ->with('info', 'No se detectaron cambios en la información.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_patient' => 'required|string',
            'ci_patient' => 'required',
            'study_type' => 'required|string',
            'images.*' => 'required|mimes:png,jpg,jpeg|max:10240'
        ]);

        $clinicId = auth()->user()->clinic_id ?? null;
        if (!$clinicId) abort(403, 'No tienes una clínica asociada. No se puede crear el estudio.');

        $studyCode = strtoupper(Str::random(8));
        $studyDate = Carbon::now()->toDateString();

        $driveService = $this->getDriveService();
        $uploadedFileIds = [];

        foreach ($request->file('images') as $file) {
            $driveFile = new DriveFile();
            $driveFile->setName($file->getClientOriginalName());
            $driveFile->setParents([$this->driveFolderId]);

            $result = $driveService->files->create($driveFile, [
                'data' => file_get_contents($file->getRealPath()),
                'mimeType' => $file->getMimeType(),
                'uploadType' => 'multipart'
            ]);

            // Hacer el archivo público
            $driveService->permissions->create($result->id, new \Google\Service\Drive\Permission([
                'type' => 'anyone',
                'role' => 'reader'
            ]));

            $uploadedFileIds[] = $result->id;
        }

        MultimediaFile::create([
            'name_patient' => $request->name_patient,
            'ci_patient' => $request->ci_patient,
            'study_code' => $studyCode,
            'study_date' => $studyDate,
            'study_type' => $request->study_type,
            'study_uri' => implode(',', $uploadedFileIds), // IDs de Google Drive
            'clinic_id' => $clinicId,
            'created_by' => auth()->id(),
            'edit_by' => auth()->id(),
        ]);

        return redirect()->route('multimedia.index')
            ->with('success', 'Archivos subidos a Google Drive correctamente.');
    }

    public function show($id)
    {
        $study = MultimediaFile::findOrFail($id);
        $fileIds = explode(',', $study->study_uri);
        $imageUrls = [];

        foreach ($fileIds as $fileId) {
            $imageUrls[] = "https://drive.google.com/uc?id={$fileId}";
        }

        return view('multimedia.show', compact('study', 'imageUrls'));
    }

    public function destroy($id)
    {
        $study = MultimediaFile::findOrFail($id);
        $driveService = $this->getDriveService();

        $fileIds = explode(',', $study->study_uri);
        foreach ($fileIds as $fileId) {
            try {
                $driveService->files->delete($fileId);
            } catch (\Exception $e) {
                // Ignorar errores si el archivo ya no existe
            }
        }

        $study->delete();
        return redirect()->route('multimedia.index')
            ->with('danger', 'Estudio eliminado correctamente de Google Drive.');
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $query = MultimediaFile::where('ci_patient', 'LIKE', "%{$search}%")
            ->orWhere('study_date', 'LIKE', "%{$search}%")
            ->orWhere('name_patient', 'LIKE', "%{$search}%");

        if (auth()->user()->role !== 'super_admin') {
            $clinicId = auth()->user()->clinic_id;
            $query->where('clinic_id', $clinicId);
        }

        $files = $query->latest()->paginate(10);
        return view('multimedia.search', compact('files'));
    }

    // Las funciones measure y tool solo usan show
    public function measure($id)
    {
        return $this->show($id);
    }

    public function tool($id)
    {
        return $this->show($id);
    }
}
