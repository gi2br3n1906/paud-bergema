<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassJournal;
use App\Models\Classroom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ClassJournalController extends Controller
{
    /**
     * Display a listing of class journals
     */
    public function index(Request $request): Response
    {
        // Get all classrooms (teachers can access all classes for cross-class documentation)
        $classrooms = Classroom::with('academicYear')->get();

        $query = ClassJournal::with(['classroom.academicYear', 'teacher'])
            ->orderBy('date', 'desc');

        // Filter by classroom if specified
        if ($request->filled('classroom_id')) {
            $query->where('classroom_id', $request->classroom_id);
        }

        // Filter by date range if specified
        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        $journals = $query->paginate(15)->withQueryString();

        return Inertia::render('Teacher/ClassJournals/Index', [
            'journals' => $journals,
            'classrooms' => $classrooms,
            'filters' => $request->only(['classroom_id', 'start_date', 'end_date']),
        ]);
    }

    /**
     * Store a newly created journal
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'date' => 'required|date',
            'theme' => 'nullable|string|max:255',
            'activity_summary' => 'required|string',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|max:2048', // Max 2MB per photo
            'attendance_stats' => 'nullable|array',
            'attendance_stats.present' => 'nullable|integer|min:0',
            'attendance_stats.sick' => 'nullable|integer|min:0',
            'attendance_stats.permission' => 'nullable|integer|min:0',
            'attendance_stats.absent' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        // Handle photo uploads
        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('class-journals', 'public');
                $photoPaths[] = $path;
            }
        }

        $validated['teacher_id'] = Auth::id();
        $validated['photos'] = $photoPaths;

        ClassJournal::create($validated);

        return redirect()->route('teacher.class-journals.index')
            ->with('success', 'Berita acara kelas berhasil disimpan!');
    }

    /**
     * Update the specified journal
     */
    public function update(Request $request, ClassJournal $classJournal): RedirectResponse
    {
        $validated = $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'date' => 'required|date',
            'theme' => 'nullable|string|max:255',
            'activity_summary' => 'required|string',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|max:2048',
            'existing_photos' => 'nullable|array',
            'existing_photos.*' => 'string',
            'attendance_stats' => 'nullable|array',
            'attendance_stats.present' => 'nullable|integer|min:0',
            'attendance_stats.sick' => 'nullable|integer|min:0',
            'attendance_stats.permission' => 'nullable|integer|min:0',
            'attendance_stats.absent' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        // Handle photo uploads
        $photoPaths = $request->input('existing_photos', []);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('class-journals', 'public');
                $photoPaths[] = $path;
            }
        }

        // Delete removed photos
        $existingPhotos = $classJournal->photos ?? [];
        $removedPhotos = array_diff($existingPhotos, $photoPaths);
        foreach ($removedPhotos as $photo) {
            Storage::disk('public')->delete($photo);
        }

        $validated['photos'] = $photoPaths;

        $classJournal->update($validated);

        return redirect()->route('teacher.class-journals.index')
            ->with('success', 'Berita acara kelas berhasil diperbarui!');
    }

    /**
     * Remove the specified journal
     */
    public function destroy(ClassJournal $classJournal): RedirectResponse
    {
        // Delete associated photos
        if ($classJournal->photos) {
            foreach ($classJournal->photos as $photo) {
                Storage::disk('public')->delete($photo);
            }
        }

        $classJournal->delete();

        return redirect()->route('teacher.class-journals.index')
            ->with('success', 'Berita acara kelas berhasil dihapus!');
    }
}
