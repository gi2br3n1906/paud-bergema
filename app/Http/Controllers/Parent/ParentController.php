<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\ReportCard;
use App\Models\Student;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class ParentController extends Controller
{
    /**
     * Display parent dashboard with their children
     */
    public function dashboard()
    {
        $parent = auth()->user();

        // Get all students associated with this parent
        $students = $parent->students()
            ->with(['classroom'])
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'nickname' => $student->nickname,
                    'nisn' => $student->nisn,
                    'date_of_birth' => $student->date_of_birth,
                    'gender' => $student->gender,
                    'photo_url' => $student->photo_url,
                    'classroom' => $student->classroom ? [
                        'id' => $student->classroom->id,
                        'name' => $student->classroom->name,
                    ] : null,
                    'relationship_type' => $student->pivot->relationship_type,
                ];
            });

        return Inertia::render('Parent/Dashboard', [
            'students' => $students,
        ]);
    }

    /**
     * Display student profile details
     */
    public function studentProfile($studentId)
    {
        $parent = auth()->user();

        // Verify parent has access to this student
        $student = $parent->students()
            ->with(['classroom', 'reportCards.academicTerm.academicYear'])
            ->findOrFail($studentId);

        return Inertia::render('Parent/StudentProfile', [
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'nickname' => $student->nickname,
                'nisn' => $student->nisn,
                'date_of_birth' => $student->date_of_birth,
                'place_of_birth' => $student->place_of_birth,
                'gender' => $student->gender,
                'address' => $student->address,
                'photo_url' => $student->photo_url,
                'enrollment_date' => $student->enrollment_date,
                'status' => $student->status,
                'classroom' => $student->classroom ? [
                    'id' => $student->classroom->id,
                    'name' => $student->classroom->name,
                ] : null,
                'report_cards' => $student->reportCards->map(function ($reportCard) {
                    return [
                        'id' => $reportCard->id,
                        'status' => $reportCard->status,
                        'academic_term' => [
                            'id' => $reportCard->academicTerm->id,
                            'semester' => $reportCard->academicTerm->semester,
                            'academic_year' => $reportCard->academicTerm->academicYear->year,
                        ],
                    ];
                }),
            ],
        ]);
    }

    /**
     * Display report card details
     */
    public function reportCard($studentId, $termId)
    {
        $parent = auth()->user();

        // Verify parent has access to this student
        $student = $parent->students()->findOrFail($studentId);

        $academicTerm = AcademicTerm::with('academicYear')->findOrFail($termId);

        // Get report card - only show published reports
        $reportCard = ReportCard::with(['creator'])
            ->where('student_id', $studentId)
            ->where('academic_term_id', $termId)
            ->where('status', \App\Enums\ReportCardStatus::PUBLISHED)
            ->firstOrFail();

        // Get all report details with assessment aspects, ordered by aspect order
        $reportDetails = \App\Models\ReportDetail::where('report_card_id', $reportCard->id)
            ->with('assessmentAspect')
            ->join('assessment_aspects', 'report_details.assessment_aspect_id', '=', 'assessment_aspects.id')
            ->orderBy('assessment_aspects.order')
            ->select('report_details.*')
            ->get()
            ->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'score' => $detail->score,
                    'narrative' => $detail->narrative,
                    'assessment_aspect' => [
                        'id' => $detail->assessmentAspect->id,
                        'name' => $detail->assessmentAspect->name,
                        'category' => $detail->assessmentAspect->category,
                        'description' => $detail->assessmentAspect->description,
                    ],
                ];
            });

        return Inertia::render('Parent/ReportCard', [
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'nisn' => $student->nisn,
            ],
            'academicTerm' => [
                'id' => $academicTerm->id,
                'semester' => $academicTerm->semester,
                'start_date' => $academicTerm->start_date,
                'end_date' => $academicTerm->end_date,
                'academic_year' => [
                    'year' => $academicTerm->academicYear->year,
                ],
            ],
            'reportCard' => [
                'id' => $reportCard->id,
                'status' => $reportCard->status,
                'published_at' => $reportCard->published_at,
                'creator' => [
                    'name' => $reportCard->creator->name,
                ],
            ],
            'reportDetails' => $reportDetails,
        ]);
    }

    /**
     * Download report card as PDF
     */
    public function downloadPdf($studentId, $termId)
    {
        $parent = auth()->user();

        // Verify parent has access to this student
        $student = $parent->students()
            ->with(['classroom'])
            ->findOrFail($studentId);

        $academicTerm = AcademicTerm::with('academicYear')->findOrFail($termId);

        // Get report card - only published reports
        $reportCard = ReportCard::with(['creator'])
            ->where('student_id', $studentId)
            ->where('academic_term_id', $termId)
            ->where('status', \App\Enums\ReportCardStatus::PUBLISHED)
            ->firstOrFail();

        // Get all report details with assessment aspects, ordered by aspect order
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
        $filename = 'Raport_' . str_replace(' ', '_', $student->name) . '_'
            . $academicTerm->semester . '_' . $academicTerm->academicYear->year . '.pdf';

        // Download
        return $pdf->download($filename);
    }
}
