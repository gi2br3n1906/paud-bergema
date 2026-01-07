<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClassroomController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Classroom::with(['academicYear', 'teacher'])
            ->withCount('students')
            ->when($request->academic_year_id, function ($query, $academicYearId) {
                $query->where('academic_year_id', $academicYearId);
            });

        $classrooms = $query->latest()->get();

        $academicYears = AcademicYear::orderBy('is_active', 'desc')->get();
        $teachers = User::where('role', 'teacher')->where('is_active', true)->get();

        return Inertia::render('Admin/Classrooms/Index', [
            'classrooms' => $classrooms,
            'academicYears' => $academicYears,
            'teachers' => $teachers,
            'filters' => $request->only(['academic_year_id']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'level' => 'required|string|max:50',
            'academic_year_id' => 'required|exists:academic_years,id',
            'teacher_id' => 'nullable|exists:users,id',
            'capacity' => 'required|integer|min:1|max:100',
            'description' => 'nullable|string',
        ]);

        Classroom::create($validated);

        return redirect()->route('admin.classrooms.index')
            ->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function update(Request $request, Classroom $classroom): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'level' => 'required|string|max:50',
            'academic_year_id' => 'required|exists:academic_years,id',
            'teacher_id' => 'nullable|exists:users,id',
            'capacity' => 'required|integer|min:1|max:100',
            'description' => 'nullable|string',
        ]);

        $classroom->update($validated);

        return redirect()->route('admin.classrooms.index')
            ->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Classroom $classroom): RedirectResponse
    {
        // Check if there are students in this classroom
        if ($classroom->students()->count() > 0) {
            return redirect()->route('admin.classrooms.index')
                ->with('error', 'Tidak dapat menghapus kelas yang memiliki siswa.');
        }

        $classroom->delete();

        return redirect()->route('admin.classrooms.index')
            ->with('success', 'Kelas berhasil dihapus.');
    }
}
