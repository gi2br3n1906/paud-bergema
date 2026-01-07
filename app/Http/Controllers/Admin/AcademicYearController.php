<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AcademicYearController extends Controller
{
    public function index(): Response
    {
        $academicYears = AcademicYear::withCount(['academicTerms', 'classrooms'])
            ->latest()
            ->get();

        return Inertia::render('Admin/AcademicYears/Index', [
            'academicYears' => $academicYears,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:academic_years,name',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'required|boolean',
        ]);

        $academicYear = AcademicYear::create($validated);

        // If set as active, deactivate others
        if ($validated['is_active']) {
            $academicYear->activate();
        }

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function update(Request $request, AcademicYear $academicYear): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:academic_years,name,' . $academicYear->id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'required|boolean',
        ]);

        $academicYear->update($validated);

        // If set as active, deactivate others
        if ($validated['is_active']) {
            $academicYear->activate();
        }

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        // Check if there are associated classrooms
        if ($academicYear->classrooms()->count() > 0) {
            return redirect()->route('admin.academic-years.index')
                ->with('error', 'Tidak dapat menghapus tahun ajaran yang memiliki kelas.');
        }

        // Delete associated academic terms first
        $academicYear->academicTerms()->delete();
        $academicYear->delete();

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'Tahun ajaran berhasil dihapus.');
    }

    public function activate(AcademicYear $academicYear): RedirectResponse
    {
        $academicYear->activate();

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'Tahun ajaran berhasil diaktifkan.');
    }
}
