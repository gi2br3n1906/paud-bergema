<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DapodikImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DapodikImportController extends Controller
{
    public function __construct(
        protected DapodikImportService $importService
    ) {}

    /**
     * Show the import form
     */
    public function index(): Response
    {
        return Inertia::render('Admin/DapodikImport', [
            'sampleCsvUrl' => asset('samples/dapodik-template.csv'),
        ]);
    }

    /**
     * Process the uploaded CSV file
     */
    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240', // Max 10MB
        ]);

        $file = $request->file('file');
        $filePath = $file->getRealPath();

        try {
            $results = $this->importService->importFromCsv($filePath);

            // Prepare flash message
            $message = "Import selesai! ";
            $message .= "Berhasil: {$results['success']} data. ";

            if (count($results['created_students']) > 0) {
                $message .= count($results['created_students']) . " siswa baru dibuat. ";
            }

            if (count($results['created_parents']) > 0) {
                $message .= count($results['created_parents']) . " orang tua baru dibuat. ";
            }

            if (count($results['errors']) > 0) {
                $message .= "Gagal: " . count($results['errors']) . " baris.";
            }

            return redirect()->route('admin.dapodik-import.index')
                ->with('success', $message)
                ->with('importResults', $results);

        } catch (\Exception $e) {
            return redirect()->route('admin.dapodik-import.index')
                ->with('error', 'Gagal mengimport file: ' . $e->getMessage());
        }
    }
}
