<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MultimediaFile;
use App\Models\Patient;
use Illuminate\Support\Str;
use Carbon\Carbon;
use ZipArchive;
use Illuminate\Support\Facades\File;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Google\Service\Drive\DriveFile;

class MultimediaFileController extends Controller
{
    /**
     * Obtiene el servicio de Google Drive
     */
    private function getDriveService()
    {
        $jsonPath = public_path('assets/google-drive/sixth-starlight-480520-h9-bd9751a4ceab.json');

        if (!File::exists($jsonPath)) {
            abort(500, "Archivo de credenciales de Google Drive no encontrado: {$jsonPath}");
        }

        $client = new GoogleClient();
        $client->setAuthConfig($jsonPath);
        $client->addScope(GoogleDrive::DRIVE);
        $client->setAccessType('offline');

        return new GoogleDrive($client);
    }

    public function index()
    {
        $query = MultimediaFile::with('patient')->latest();
        if (auth()->user()->role !== 'super_admin') {
            $query->where('clinic_id', auth()->user()->clinic_id);
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
            'images.*' => 'nullable|mimes:png,jpg,jpeg|max:10240',
            'folder' => 'nullable|file|mimetypes:application/zip,application/x-zip-compressed'
        ]);

        $clinicId = auth()->user()->clinic_id ?? null;
        if (!$clinicId) {
            abort(403, 'No tienes una clínica asociada. No se puede crear el estudio.');
        }

        $studyCode = strtoupper(Str::random(8));
        $studyDate = Carbon::now()->toDateString();
        $folderName = "{$studyCode}_{$studyDate}";

        $driveService = $this->getDriveService();
        $driveFolderId = '14TaS42svjlWVzQNwa-mdcyOtWRnqyDz6';

        $uploadedIds = [];
        $count = 0;

        // Subir imágenes individuales
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $fileMetadata = new DriveFile([
                    'name' => Str::uuid() . '.' . $img->getClientOriginalExtension(),
                    'parents' => [$driveFolderId]
                ]);
                $createdFile = $driveService->files->create($fileMetadata, [
                    'data' => file_get_contents($img->getRealPath()),
                    'mimeType' => $img->getMimeType(),
                    'uploadType' => 'multipart'
                ]);
                $uploadedIds[] = $createdFile->id;
                $count++;
            }
        }

        // Subir ZIP descomprimido
        if ($request->hasFile('folder')) {
            $zip = new ZipArchive;
            $zipPath = $request->file('folder')->getRealPath();
            if ($zip->open($zipPath) === true) {
                $tempDir = storage_path("app/temp/{$folderName}");
                if (!File::exists($tempDir)) File::makeDirectory($tempDir, 0775, true);
                $zip->extractTo($tempDir);
                $zip->close();

                $directoryIterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($tempDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::SELF_FIRST
                );

                $imagePattern = '/\.(png|jpg|jpeg)$/i';
                foreach ($directoryIterator as $file) {
                    if ($file->isFile() && preg_match($imagePattern, $file->getFilename())) {
                        $fileMetadata = new DriveFile([
                            'name' => $file->getFilename(),
                            'parents' => [$driveFolderId]
                        ]);
                        $createdFile = $driveService->files->create($fileMetadata, [
                            'data' => file_get_contents($file->getPathname()),
                            'mimeType' => mime_content_type($file->getPathname()),
                            'uploadType' => 'multipart'
                        ]);
                        $uploadedIds[] = $createdFile->id;
                        $count++;
                    }
                }

                File::deleteDirectory($tempDir);
            }
        }

        MultimediaFile::create([
            'name_patient' => $request->name_patient,
            'ci_patient' => $request->ci_patient,
            'study_code' => $studyCode,
            'study_date' => $studyDate,
            'study_type' => $request->study_type,
            'study_uri' => json_encode($uploadedIds), // Guardamos los IDs de Drive
            'description' => $request->input('description'),
            'image_count' => $count,
            'clinic_id' => $clinicId,
            'created_by' => auth()->id(),
            'edit_by' => auth()->id(),
        ]);

        return redirect()->route('multimedia.index')->with('success', 'Estudio cargado correctamente en Google Drive.');
    }

    public function show($id)
    {
        $study = MultimediaFile::findOrFail($id);
        $imageUrls = $this->getDriveImagesUrls($study);
        return view('multimedia.show', compact('study', 'imageUrls'));
    }

    public function tool($id)
    {
        $study = MultimediaFile::findOrFail($id);
        $imageUrls = $this->getDriveImagesUrls($study);
        return view('multimedia.tool', compact('study', 'imageUrls'));
    }

    public function measure($id)
    {
        $study = MultimediaFile::findOrFail($id);
        $imageUrls = $this->getDriveImagesUrls($study);
        return view('multimedia.measure', compact('study', 'imageUrls'));
    }

    public function driveImage($fileId)
    {
        $driveService = $this->getDriveService();

        // Obtener contenido del archivo desde Drive
        $content = $driveService->files->get($fileId, ['alt' => 'media']);

        // Obtener el mime type
        $file = $driveService->files->get($fileId);
        $mimeType = $file->getMimeType();

        return response($content, 200)->header('Content-Type', $mimeType);
    }

    public function destroy($id)
    {
        $study = MultimediaFile::findOrFail($id);
        $driveService = $this->getDriveService();
        $fileIds = json_decode($study->study_uri, true) ?? [];
        foreach ($fileIds as $fid) {
            try {
                $driveService->files->delete($fid);
            } catch (\Exception $e) {
                // Ignorar si ya no existe
            }
        }
        $study->delete();
        return redirect()->route('multimedia.index')->with('danger', 'Estudio eliminado correctamente.');
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $query = MultimediaFile::where('ci_patient', 'LIKE', "%{$search}%")
            ->orWhere('study_date', 'LIKE', "%{$search}%")
            ->orWhere('name_patient', 'LIKE', "%{$search}%");

        if (auth()->user()->role !== 'super_admin') {
            $query->where('clinic_id', auth()->user()->clinic_id);
        }

        $files = $query->latest()->paginate(10);
        return view('multimedia.search', compact('files'));
    }

    /**
     * Devuelve URLs de imágenes desde Drive
     */
    private function getDriveImagesUrls(MultimediaFile $study)
    {
        $fileIds = json_decode($study->study_uri, true) ?? [];
        $urls = [];
        foreach ($fileIds as $fid) {
            $urls[] = route('multimedia.driveImage', ['fileId' => $fid]);
        }
        return $urls;
    }
}
