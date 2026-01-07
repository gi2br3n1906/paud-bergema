<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Student::with(['classroom', 'parents'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('nickname', 'like', "%{$search}%");
            })
            ->when($request->classroom_id, function ($query, $classroomId) {
                $query->where('classroom_id', $classroomId);
            })
            ->when($request->filled('is_active'), function ($query) use ($request) {
                if (filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN)) {
                    $query->where('status', 'Aktif');
                } else {
                    $query->where('status', '!=', 'Aktif');
                }
            });

        $students = $query->latest()->paginate(10)->withQueryString();

        $classrooms = Classroom::with('academicYear')->get();
        $parents = User::where('role', 'parent')->where('is_active', true)->get();

        return Inertia::render('Admin/Students/Index', [
            'students' => $students,
            'classrooms' => $classrooms,
            'parents' => $parents,
            'filters' => $request->only(['search', 'classroom_id', 'is_active']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nisn' => 'nullable|string|max:20|unique:students,nisn',
            'name' => 'required|string|max:150',
            'nickname' => 'nullable|string|max:50',
            'gender' => 'required|in:Laki-laki,Perempuan',
            'date_of_birth' => 'required|date',
            'place_of_birth' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'enrollment_date' => 'required|date',
            'status' => 'required|in:Aktif,Lulus,Pindah,Keluar',
            'notes' => 'nullable|string',
            'parent_ids' => 'nullable|array',
            'parent_ids.*' => 'exists:users,id',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo_url'] = $request->file('photo')->store('students', 'public');
        }

        $student = Student::create($validated);

        // Attach parents if provided
        if (!empty($validated['parent_ids'])) {
            foreach ($validated['parent_ids'] as $index => $parentId) {
                $student->parents()->attach($parentId, [
                    'relationship_type' => 'guardian',
                    'is_primary_contact' => $index === 0,
                ]);
            }
        }

        return redirect()->route('admin.students.index')
            ->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'nisn' => 'nullable|string|max:20|unique:students,nisn,' . $student->id,
            'name' => 'required|string|max:150',
            'nickname' => 'nullable|string|max:50',
            'gender' => 'required|in:Laki-laki,Perempuan',
            'date_of_birth' => 'required|date',
            'place_of_birth' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'enrollment_date' => 'required|date',
            'status' => 'required|in:Aktif,Lulus,Pindah,Keluar',
            'notes' => 'nullable|string',
            'parent_ids' => 'nullable|array',
            'parent_ids.*' => 'exists:users,id',
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($student->photo_url) {
                Storage::disk('public')->delete($student->photo_url);
            }
            $validated['photo_url'] = $request->file('photo')->store('students', 'public');
        }

        $student->update($validated);

        // Sync parents
        if (isset($validated['parent_ids'])) {
            $student->parents()->detach();
            foreach ($validated['parent_ids'] as $index => $parentId) {
                $student->parents()->attach($parentId, [
                    'relationship_type' => 'guardian',
                    'is_primary_contact' => $index === 0,
                ]);
            }
        }

        return redirect()->route('admin.students.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        // Delete photo if exists
        if ($student->photo_url) {
            Storage::disk('public')->delete($student->photo_url);
        }

        $student->delete();

        return redirect()->route('admin.students.index')
            ->with('success', 'Siswa berhasil dihapus.');
    }
}
