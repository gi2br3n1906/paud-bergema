<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\AssessmentAspect;
use App\Models\Classroom;
use App\Models\StudentAssessment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AssessmentController extends Controller
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

        return Inertia::render('Teacher/Assessments/Index', [
            'classrooms' => $classrooms,
            'academicTerms' => $academicTerms,
        ]);
    }

    public function form(Request $request, $classroomId, $termId): Response
    {
        $teacher = Auth::user();

        // Allow teacher to assess any classroom (cross-class documentation)
        $classroom = Classroom::where('id', $classroomId)
            ->with(['students' => function ($query) {
                $query->where('status', 'Aktif')->orderBy('name');
            }])
            ->firstOrFail();

        $academicTerm = AcademicTerm::with('academicYear')->findOrFail($termId);

        // Get all assessment aspects
        $assessmentAspects = AssessmentAspect::orderBy('category')->orderBy('name')->get();

        // Get existing assessments for this classroom and term
        $existingAssessments = StudentAssessment::where('classroom_id', $classroomId)
            ->where('academic_term_id', $termId)
            ->get()
            ->groupBy(function ($item) {
                return $item->student_id . '_' . $item->assessment_aspect_id;
            })
            ->map(function ($group) {
                return $group->first();
            });

        return Inertia::render('Teacher/Assessments/Form', [
            'classroom' => $classroom,
            'academicTerm' => $academicTerm,
            'students' => $classroom->students,
            'assessmentAspects' => $assessmentAspects,
            'existingAssessments' => $existingAssessments,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'assessments' => 'required|array',
            'assessments.*.student_id' => 'required|exists:students,id',
            'assessments.*.assessment_aspect_id' => 'required|exists:assessment_aspects,id',
            'assessments.*.score' => 'required|in:BB,MB,BSH,BSB',
            'assessments.*.notes' => 'nullable|string',
        ]);

        // Allow teacher to assess any classroom (cross-class documentation)
        $classroom = Classroom::findOrFail($validated['classroom_id']);

        foreach ($validated['assessments'] as $assessment) {
            StudentAssessment::updateOrCreate(
                [
                    'student_id' => $assessment['student_id'],
                    'academic_term_id' => $validated['academic_term_id'],
                    'assessment_aspect_id' => $assessment['assessment_aspect_id'],
                ],
                [
                    'classroom_id' => $validated['classroom_id'],
                    'score' => $assessment['score'],
                    'notes' => $assessment['notes'] ?? null,
                    'assessed_by' => Auth::id(),
                ]
            );
        }

        return redirect()->back()->with('success', 'Penilaian berhasil disimpan.');
    }
}
