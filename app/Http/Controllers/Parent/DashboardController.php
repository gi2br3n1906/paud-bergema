<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\ClassJournal;
use App\Models\Student;
use App\Models\StudentDailyLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $parent = Auth::user();

        // Get all children of this parent
        $children = $parent->students()
            ->with('classroom.academicYear')
            ->where('status', 'Aktif')
            ->get();

        // Default to first child or selected child
        $selectedChildId = $request->input('child_id', $children->first()?->id);
        $selectedChild = $children->firstWhere('id', $selectedChildId);

        $timeline = [];
        $stats = [
            'total_children' => $children->count(),
            'class_journals_count' => 0,
            'daily_logs_count' => 0,
        ];

        if ($selectedChild && $selectedChild->classroom_id) {
            // Get Class Journals from child's classroom (last 30 days)
            $classJournals = ClassJournal::where('classroom_id', $selectedChild->classroom_id)
                ->with(['classroom', 'teacher'])
                ->where('date', '>=', now()->subDays(30))
                ->orderBy('date', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($journal) {
                    return [
                        'type' => 'class_journal',
                        'id' => $journal->id,
                        'date' => $journal->date,
                        'title' => $journal->theme ?? 'Kegiatan Kelas',
                        'description' => $journal->activity_summary,
                        'photos' => $journal->photos,
                        'attendance_stats' => $journal->attendance_stats,
                        'classroom' => $journal->classroom->name,
                        'teacher' => $journal->teacher->name,
                        'notes' => $journal->notes,
                    ];
                });

            // Get Student Daily Logs for selected child (last 30 days)
            // Note: Wrapped in try-catch in case the table doesn't exist yet
            $dailyLogs = collect([]);
            try {
                $dailyLogs = StudentDailyLog::where('student_id', $selectedChild->id)
                    ->with('recordedBy')
                    ->where('date', '>=', now()->subDays(30))
                    ->orderBy('date', 'desc')
                    ->limit(10)
                    ->get()
                    ->map(function ($log) {
                        return [
                            'type' => 'daily_log',
                            'id' => $log->id,
                            'date' => $log->date,
                            'title' => 'Log Harian',
                            'attendance_status' => $log->attendance_status,
                            'arrival_time' => $log->arrival_time,
                            'activities' => $log->activities,
                            'mood' => $log->mood,
                            'notes' => $log->notes,
                            'teacher' => $log->recordedBy->name,
                        ];
                    });
            } catch (\Exception $e) {
                // Table not yet created, skip daily logs
            }

            // Combine and sort timeline
            $timeline = $classJournals->concat($dailyLogs)
                ->sortByDesc('date')
                ->values()
                ->take(20);

            $stats['class_journals_count'] = $classJournals->count();
            $stats['daily_logs_count'] = $dailyLogs->count();
        }

        return Inertia::render('Parent/Dashboard', [
            'children' => $children,
            'selectedChild' => $selectedChild,
            'timeline' => $timeline,
            'stats' => $stats,
        ]);
    }
}
