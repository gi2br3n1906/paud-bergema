<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\Classroom;
use App\Models\GrowthRecord;
use App\Models\Student;
use App\Models\StudentAssessment;
use App\Models\StudentDailyLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportCardController extends Controller
{
    public function index(Request $request): Response
    {
        $teacher = Auth::user();

        // Get all classrooms (teacher can access all classes for cross-class documentation)
        $classrooms = Classroom::with('academicYear')->get();

        // Get all academic terms
        $academicTerms = AcademicTerm::with('academicYear')
            ->whereHas('academicYear', function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('start_date', 'desc')
            ->get();

        return Inertia::render('Teacher/Reports/Index', [
            'classrooms' => $classrooms,
            'academicTerms' => $academicTerms,
        ]);
    }

    public function preview(Request $request, $studentId, $termId): Response
    {
        $student = Student::with(['classroom.teacher', 'classroom.academicYear'])
            ->findOrFail($studentId);

        // Allow teacher to view any student report (cross-class documentation)

        $academicTerm = AcademicTerm::with('academicYear')->findOrFail($termId);

        // Get attendance summary
        $attendanceSummary = StudentDailyLog::where('student_id', $studentId)
            ->where('classroom_id', $student->classroom_id)
            ->whereBetween('log_date', [$academicTerm->start_date, $academicTerm->end_date])
            ->select('attendance_status', DB::raw('count(*) as total'))
            ->groupBy('attendance_status')
            ->get()
            ->pluck('total', 'attendance_status')
            ->toArray();

        // Get latest growth record in this term
        $latestGrowth = GrowthRecord::where('student_id', $studentId)
            ->whereBetween('measurement_date', [$academicTerm->start_date, $academicTerm->end_date])
            ->orderBy('measurement_date', 'desc')
            ->first();

        // Get assessments grouped by category
        $assessments = StudentAssessment::where('student_id', $studentId)
            ->where('academic_term_id', $termId)
            ->with('assessmentAspect')
            ->get()
            ->groupBy(function ($item) {
                return $item->assessmentAspect->category;
            });

        return Inertia::render('Teacher/Reports/Preview', [
            'student' => [
                'id' => $student->id,
                'nisn' => $student->nisn,
                'name' => $student->name,
                'gender' => $student->gender,
                'date_of_birth' => $student->date_of_birth,
                'place_of_birth' => $student->place_of_birth,
                'address' => $student->address,
                'classroom' => [
                    'name' => $student->classroom->name,
                    'level' => $student->classroom->level,
                ],
            ],
            'academicTerm' => $academicTerm,
            'attendanceSummary' => $attendanceSummary,
            'latestGrowth' => $latestGrowth,
            'assessments' => $assessments,
        ]);
    }

    public function students(Request $request, $classroomId, $termId): Response
    {
        $teacher = Auth::user();

        // Allow teacher to view any classroom reports (cross-class documentation)
        $classroom = Classroom::where('id', $classroomId)
            ->with(['students' => function ($query) {
                $query->where('status', 'Aktif')->orderBy('name');
            }])
            ->firstOrFail();

        $academicTerm = AcademicTerm::with('academicYear')->findOrFail($termId);

        return Inertia::render('Teacher/Reports/Students', [
            'classroom' => $classroom,
            'academicTerm' => $academicTerm,
            'students' => $classroom->students,
        ]);
    }
}
