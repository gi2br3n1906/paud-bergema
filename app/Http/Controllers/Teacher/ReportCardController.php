<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\Classroom;
use App\Models\GrowthRecord;
use App\Models\Student;
use App\Models\StudentAssessment;
use App\Models\StudentDailyLog;
use App\Services\NarrativeGeneratorService;
use Barryvdh\DomPDF\Facade\Pdf;
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
            ->whereBetween('date', [$academicTerm->start_date, $academicTerm->end_date])
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

    /**
     * Show assessment input form for a specific student
     */
    public function assess(Request $request, $studentId, $termId): Response
    {
        $student = Student::with(['classroom', 'reportCards' => function ($query) use ($termId) {
            $query->where('academic_term_id', $termId);
        }])->findOrFail($studentId);

        $academicTerm = AcademicTerm::with('academicYear')->findOrFail($termId);

        // Get or create report card
        $reportCard = \App\Models\ReportCard::firstOrCreate(
            [
                'student_id' => $studentId,
                'academic_term_id' => $termId,
            ],
            [
                'classroom_id' => $student->classroom_id,
                'status' => \App\Enums\ReportCardStatus::DRAFT,
                'created_by' => Auth::id(),
            ]
        );

        // Get all assessment aspects
        $assessmentAspects = \App\Models\AssessmentAspect::orderBy('order')->get();

        // Get existing report details
        $existingDetails = \App\Models\ReportDetail::where('report_card_id', $reportCard->id)
            ->with('assessmentAspect')
            ->get()
            ->keyBy('assessment_aspect_id');

        return Inertia::render('Teacher/Reports/Assess', [
            'student' => $student,
            'academicTerm' => $academicTerm,
            'reportCard' => $reportCard,
            'assessmentAspects' => $assessmentAspects,
            'existingDetails' => $existingDetails,
        ]);
    }

    /**
     * Save assessment details with keywords
     */
    public function saveAssessment(Request $request, $studentId, $termId)
    {
        $validated = $request->validate([
            'assessments' => 'required|array',
            'assessments.*.assessment_aspect_id' => 'required|exists:assessment_aspects,id',
            'assessments.*.score' => 'required|in:BB,MB,BSH,BSB',
            'assessments.*.keywords' => 'nullable|string',
            'assessments.*.narrative' => 'nullable|string',
        ]);

        $student = Student::findOrFail($studentId);

        // Get or create report card
        $reportCard = \App\Models\ReportCard::firstOrCreate(
            [
                'student_id' => $studentId,
                'academic_term_id' => $termId,
            ],
            [
                'classroom_id' => $student->classroom_id,
                'status' => \App\Enums\ReportCardStatus::DRAFT,
                'created_by' => Auth::id(),
            ]
        );

        // Save each assessment detail
        foreach ($validated['assessments'] as $assessment) {
            \App\Models\ReportDetail::updateOrCreate(
                [
                    'report_card_id' => $reportCard->id,
                    'assessment_aspect_id' => $assessment['assessment_aspect_id'],
                ],
                [
                    'score' => $assessment['score'],
                    'keywords' => $assessment['keywords'] ?? null,
                    'narrative' => $assessment['narrative'] ?? null,
                ]
            );
        }

        return redirect()->back()->with('success', 'Penilaian berhasil disimpan sebagai draft!');
    }

    /**
     * Preview PDF report card in browser
     */
    public function previewPdf(Request $request, $studentId, $termId)
    {
        $student = Student::with(['classroom'])->findOrFail($studentId);
        $academicTerm = AcademicTerm::with('academicYear')->findOrFail($termId);

        // Get report card with details
        $reportCard = \App\Models\ReportCard::with(['creator'])
            ->where('student_id', $studentId)
            ->where('academic_term_id', $termId)
            ->firstOrFail();

        // Get all report details with assessment aspects, ordered by aspect order
        $reportDetails = \App\Models\ReportDetail::where('report_card_id', $reportCard->id)
            ->with('assessmentAspect')
            ->join('assessment_aspects', 'report_details.assessment_aspect_id', '=', 'assessment_aspects.id')
            ->orderBy('assessment_aspects.order')
            ->select('report_details.*')
            ->get();

        return view('pdf.report_card', [
            'student' => $student,
            'academicTerm' => $academicTerm,
            'reportCard' => $reportCard,
            'reportDetails' => $reportDetails,
        ]);
    }

    /**
     * Generate AI narrative for a specific assessment aspect
     */
    public function generateNarrative(Request $request, $studentId, $termId, $aspectId)
    {
        $validated = $request->validate([
            'score' => 'required|in:BB,MB,BSH,BSB',
            'keywords' => 'nullable|string',
        ]);

        $student = Student::findOrFail($studentId);
        $aspect = \App\Models\AssessmentAspect::findOrFail($aspectId);

        try {
            $generator = new NarrativeGeneratorService();
            $narrative = $generator->generateNarrative(
                $student->name,
                $aspect->name,
                $aspect->category,
                $validated['score'],
                $validated['keywords'] ?? null
            );

            if ($narrative) {
                return response()->json([
                    'success' => true,
                    'narrative' => $narrative,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal generate narasi. Pastikan API key Gemini sudah dikonfigurasi.',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate AI narratives for all assessments
     */
    public function generateBulkNarratives(Request $request, $studentId, $termId)
    {
        $validated = $request->validate([
            'assessments' => 'required|array',
            'assessments.*.assessment_aspect_id' => 'required|exists:assessment_aspects,id',
            'assessments.*.score' => 'required|in:BB,MB,BSH,BSB',
            'assessments.*.keywords' => 'nullable|string',
        ]);

        $student = Student::findOrFail($studentId);

        try {
            $generator = new NarrativeGeneratorService();
            $narratives = $generator->generateBulkNarratives(
                $validated['assessments'],
                $student->name
            );

            if (!empty($narratives)) {
                return response()->json([
                    'success' => true,
                    'narratives' => $narratives,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal generate narasi. Pastikan API key Gemini sudah dikonfigurasi.',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Publish report card (change status from draft to published)
     */
    public function publishReportCard(Request $request, $studentId, $termId)
    {
        $student = Student::findOrFail($studentId);
        $academicTerm = AcademicTerm::findOrFail($termId);

        // Get report card
        $reportCard = \App\Models\ReportCard::where('student_id', $studentId)
            ->where('academic_term_id', $termId)
            ->firstOrFail();

        // Validation: Check if all aspects have been assessed
        $assessmentAspects = \App\Models\AssessmentAspect::where('is_active', true)->count();
        $completedAssessments = \App\Models\ReportDetail::where('report_card_id', $reportCard->id)
            ->whereNotNull('score')
            ->count();

        if ($completedAssessments < $assessmentAspects) {
            return redirect()->back()->with('error', 'Tidak bisa publish! Semua aspek penilaian harus diisi terlebih dahulu.');
        }

        // Check if all assessments have narratives
        $aspectsMissingNarratives = \App\Models\ReportDetail::where('report_card_id', $reportCard->id)
            ->where(function($q) {
                $q->whereNull('narrative')->orWhere('narrative', '');
            })
            ->with('assessmentAspect')
            ->get()
            ->pluck('assessmentAspect.name')
            ->toArray();

        if (!empty($aspectsMissingNarratives)) {
            $aspectsList = implode(', ', $aspectsMissingNarratives);
            return redirect()->back()->with('error', "Tidak bisa publish! Aspek berikut masih perlu narasi: {$aspectsList}. Gunakan tombol 'Generate Narasi AI' atau tulis manual.");
        }

        // Publish the report
        $reportCard->update([
            'status' => \App\Enums\ReportCardStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Raport berhasil dipublish! Raport tidak bisa diedit lagi dan sudah bisa dilihat oleh orang tua.');
    }

    /**
     * Download report card as PDF
     */
    public function downloadPdf(Request $request, $studentId, $termId)
    {
        $student = Student::with(['classroom'])->findOrFail($studentId);
        $academicTerm = AcademicTerm::with('academicYear')->findOrFail($termId);

        // Get report card with details
        $reportCard = \App\Models\ReportCard::with(['creator'])
            ->where('student_id', $studentId)
            ->where('academic_term_id', $termId)
            ->firstOrFail();

        // Get all report details with assessment aspects
        $reportDetails = \App\Models\ReportDetail::where('report_card_id', $reportCard->id)
            ->with('assessmentAspect')
            ->join('assessment_aspects', 'report_details.assessment_aspect_id', '=', 'assessment_aspects.id')
            ->orderBy('assessment_aspects.order')
            ->select('report_details.*')
            ->get();

        // Generate PDF
        $pdf = Pdf::loadView('pdf.report_card', [
            'student' => $student,
            'academicTerm' => $academicTerm,
            'reportCard' => $reportCard,
            'reportDetails' => $reportDetails,
        ]);

        // Set paper size and orientation
        $pdf->setPaper('a4', 'portrait');

        // Generate filename
        $filename = 'Raport_' . str_replace(' ', '_', $student->name) . '_' . $academicTerm->semester . '_' . $academicTerm->academic_year->year . '.pdf';

        // Download
        return $pdf->download($filename);
    }

    /**
     * Display statistics for all report cards in a class/term
     */
    public function statistics(Request $request)
    {
        $classroomId = $request->get('classroom_id');
        $termId = $request->get('term_id');

        $classroom = null;
        $academicTerm = null;
        $statistics = null;

        if ($classroomId && $termId) {
            $classroom = Classroom::with(['students', 'teacher'])->findOrFail($classroomId);
            $academicTerm = AcademicTerm::with('academicYear')->findOrFail($termId);

            // Get all students in the classroom
            $students = $classroom->students;

            // Get all report cards for this class and term
            $reportCards = ReportCard::where('academic_term_id', $termId)
                ->whereIn('student_id', $students->pluck('id'))
                ->with(['student', 'reportDetails.assessmentAspect'])
                ->get();

            // Calculate statistics
            $totalStudents = $students->count();
            $completedReports = $reportCards->where('status', \App\Enums\ReportCardStatus::PUBLISHED)->count();
            $draftReports = $reportCards->where('status', \App\Enums\ReportCardStatus::DRAFT)->count();

            // Score distribution
            $scoreDistribution = [
                'BSB' => 0,
                'BSH' => 0,
                'MB' => 0,
                'BB' => 0,
            ];

            // Aspect performance
            $aspectPerformance = [];

            foreach ($reportCards as $reportCard) {
                foreach ($reportCard->reportDetails as $detail) {
                    // Count score distribution
                    if (isset($scoreDistribution[$detail->score])) {
                        $scoreDistribution[$detail->score]++;
                    }

                    // Track aspect performance
                    $aspectId = $detail->assessment_aspect_id;
                    $aspectName = $detail->assessmentAspect->name;

                    if (!isset($aspectPerformance[$aspectId])) {
                        $aspectPerformance[$aspectId] = [
                            'name' => $aspectName,
                            'category' => $detail->assessmentAspect->category,
                            'scores' => ['BSB' => 0, 'BSH' => 0, 'MB' => 0, 'BB' => 0],
                            'total' => 0,
                        ];
                    }

                    $aspectPerformance[$aspectId]['scores'][$detail->score]++;
                    $aspectPerformance[$aspectId]['total']++;
                }
            }

            // Calculate averages for each aspect
            foreach ($aspectPerformance as $aspectId => &$aspect) {
                $aspect['average_score'] = $this->calculateAverageScore($aspect['scores']);
            }

            $statistics = [
                'total_students' => $totalStudents,
                'completed_reports' => $completedReports,
                'draft_reports' => $draftReports,
                'not_started' => $totalStudents - $reportCards->count(),
                'score_distribution' => $scoreDistribution,
                'aspect_performance' => array_values($aspectPerformance),
            ];
        }

        // Get classrooms for filter
        $classrooms = Classroom::with(['academicYear', 'teacher'])
            ->whereHas('academicYear', function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('name')
            ->get();

        // Get academic terms for filter
        $academicTerms = AcademicTerm::with('academicYear')
            ->whereHas('academicYear', function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('start_date', 'desc')
            ->get();

        return Inertia::render('Teacher/Reports/Statistics', [
            'classrooms' => $classrooms,
            'academicTerms' => $academicTerms,
            'selectedClassroom' => $classroom,
            'selectedTerm' => $academicTerm,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Calculate average score from score distribution
     */
    private function calculateAverageScore(array $scores): float
    {
        $weights = ['BSB' => 4, 'BSH' => 3, 'MB' => 2, 'BB' => 1];
        $totalWeight = 0;
        $totalCount = 0;

        foreach ($scores as $score => $count) {
            if (isset($weights[$score])) {
                $totalWeight += $weights[$score] * $count;
                $totalCount += $count;
            }
        }

        return $totalCount > 0 ? round($totalWeight / $totalCount, 2) : 0;
    }
}
