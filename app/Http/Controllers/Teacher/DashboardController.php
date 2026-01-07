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

        // Get classrooms taught by this teacher with student count
        $classrooms = Classroom::where('teacher_id', $teacher->id)
            ->with('academicYear')
            ->withCount('students')
            ->get();

        // Get statistics
        $stats = [
            'total_classes' => $classrooms->count(),
            'total_students' => $classrooms->sum('students_count'),
            'active_academic_year' => $classrooms->first()?->academicYear->name ?? 'Tidak ada',
        ];

        return Inertia::render('Teacher/Dashboard', [
            'classrooms' => $classrooms,
            'stats' => $stats,
        ]);
    }
}
