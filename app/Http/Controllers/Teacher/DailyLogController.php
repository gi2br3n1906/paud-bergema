<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\StudentDailyLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DailyLogController extends Controller
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
                $students = $selectedClassroom->students;
            }
        }

        // Get selected date or use today
        $selectedDate = $request->get('date', now()->toDateString());

        // Get existing logs for selected date and classroom
        $existingLogs = StudentDailyLog::where('classroom_id', $selectedClassroomId)
            ->whereDate('log_date', $selectedDate)
            ->get()
            ->keyBy('student_id');

        return Inertia::render('Teacher/DailyLogs/Index', [
            'classrooms' => $classrooms,
            'selectedClassroom' => $selectedClassroom,
            'students' => $students,
            'selectedDate' => $selectedDate,
            'existingLogs' => $existingLogs,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'log_date' => 'required|date',
            'logs' => 'required|array',
            'logs.*.student_id' => 'required|exists:students,id',
            'logs.*.attendance_status' => 'required|in:Hadir,Sakit,Izin,Alpa',
            'logs.*.prayer_quality' => 'nullable|in:Baik,Cukup,Perlu Bimbingan',
            'logs.*.quran_surah' => 'nullable|string|max:100',
            'logs.*.quran_verses' => 'nullable|string|max:50',
            'logs.*.notes' => 'nullable|string',
        ]);

        // Allow teacher to record for any classroom (cross-class documentation)
        $classroom = Classroom::findOrFail($validated['classroom_id']);

        foreach ($validated['logs'] as $logData) {
            StudentDailyLog::updateOrCreate(
                [
                    'student_id' => $logData['student_id'],
                    'classroom_id' => $validated['classroom_id'],
                    'log_date' => $validated['log_date'],
                ],
                [
                    'attendance_status' => $logData['attendance_status'],
                    'prayer_quality' => $logData['prayer_quality'] ?? null,
                    'quran_surah' => $logData['quran_surah'] ?? null,
                    'quran_verses' => $logData['quran_verses'] ?? null,
                    'notes' => $logData['notes'] ?? null,
                    'recorded_by' => Auth::id(),
                ]
            );
        }

        return redirect()->back()->with('success', 'Log harian berhasil disimpan.');
    }
}
