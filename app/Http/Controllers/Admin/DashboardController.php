<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        // Get statistics for dashboard
        $stats = [
            'total_students' => Student::where('status', 'Aktif')->count(),
            'total_teachers' => User::where('role', 'teacher')->where('is_active', true)->count(),
            'total_classrooms' => Classroom::count(),
            'active_academic_year' => AcademicYear::active()->first()?->name,
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
        ]);
    }
}
