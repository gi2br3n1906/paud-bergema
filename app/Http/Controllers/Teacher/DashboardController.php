<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $teacher = Auth::user();

        // Get all classrooms (not just teacher's own classes) with student count
        $classrooms = Classroom::with('academicYear')
            ->withCount('students')
            ->get();

        // Separate own classes and other classes
        $ownClassrooms = $classrooms->where('teacher_id', $teacher->id);
        $otherClassrooms = $classrooms->where('teacher_id', '!=', $teacher->id);

        // Get statistics
        $stats = [
            'total_classes' => $ownClassrooms->count(),
            'total_students' => $ownClassrooms->sum('students_count'),
            'active_academic_year' => $classrooms->first()?->academicYear->name ?? 'Tidak ada',
        ];

        return Inertia::render('Teacher/Dashboard', [
            'classrooms' => $classrooms,
            'ownClassrooms' => $ownClassrooms->values(),
            'otherClassrooms' => $otherClassrooms->values(),
            'stats' => $stats,
        ]);
    }
}
