<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\GrowthRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class GrowthRecordController extends Controller
{
    public function index(Request $request): Response
    {
        $teacher = Auth::user();

        // Get all classrooms (teacher can access all classes for cross-class documentation)
        $classrooms = Classroom::with('academicYear')->get();

        // Get selected classroom ID or use first classroom
        $selectedClassroomId = $request->get('classroom_id', $classrooms->first()?->id);

        // Get students in the selected classroom
        $students = [];
        $selectedClassroom = null;

        if ($selectedClassroomId) {
            $selectedClassroom = Classroom::with(['students' => function ($query) {
                $query->orderBy('name');
            }])->find($selectedClassroomId);

            if ($selectedClassroom) {
                $students = $selectedClassroom->students->map(function ($student) {
                    // Get latest growth record
                    $latestRecord = GrowthRecord::where('student_id', $student->id)
                        ->orderBy('measurement_date', 'desc')
                        ->first();

                    return [
                        'id' => $student->id,
                        'nisn' => $student->nisn,
                        'name' => $student->name,
                        'gender' => $student->gender,
                        'date_of_birth' => $student->date_of_birth,
                        'latest_record' => $latestRecord ? [
                            'id' => $latestRecord->id,
                            'measurement_date' => $latestRecord->measurement_date,
                            'height' => $latestRecord->height,
                            'weight' => $latestRecord->weight,
                            'head_circumference' => $latestRecord->head_circumference,
                        ] : null,
                    ];
                });
            }
        }

        return Inertia::render('Teacher/GrowthRecords/Index', [
            'classrooms' => $classrooms,
            'selectedClassroom' => $selectedClassroom,
            'students' => $students,
        ]);
    }

    public function show(Request $request, $studentId): Response
    {
        $student = \App\Models\Student::with('classroom')->findOrFail($studentId);

        // Allow teacher to view any student (cross-class documentation)

        // Get all growth records for this student
        $records = GrowthRecord::where('student_id', $studentId)
            ->orderBy('measurement_date', 'desc')
            ->get();

        return Inertia::render('Teacher/GrowthRecords/Show', [
            'student' => [
                'id' => $student->id,
                'nisn' => $student->nisn,
                'name' => $student->name,
                'gender' => $student->gender,
                'date_of_birth' => $student->date_of_birth,
                'place_of_birth' => $student->place_of_birth,
            ],
            'records' => $records,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'measurement_date' => 'required|date',
            'height' => 'required|numeric|min:0|max:300',
            'weight' => 'required|numeric|min:0|max:200',
            'head_circumference' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        // Allow teacher to record for any student (cross-class documentation)
        $validated['recorded_by'] = Auth::id();

        GrowthRecord::create($validated);

        return redirect()->back()->with('success', 'Data tumbuh kembang berhasil ditambahkan.');
    }

    public function update(Request $request, GrowthRecord $growthRecord): RedirectResponse
    {
        $validated = $request->validate([
            'measurement_date' => 'required|date',
            'height' => 'required|numeric|min:0|max:300',
            'weight' => 'required|numeric|min:0|max:200',
            'head_circumference' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        // Allow teacher to update any student (cross-class documentation)
        $growthRecord->update($validated);

        return redirect()->back()->with('success', 'Data tumbuh kembang berhasil diperbarui.');
    }

    public function destroy(GrowthRecord $growthRecord): RedirectResponse
    {
        // Allow teacher to delete any record (cross-class documentation)
        $growthRecord->delete();

        return redirect()->back()->with('success', 'Data tumbuh kembang berhasil dihapus.');
    }
}
