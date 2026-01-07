<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentDailyLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class StudentDailyLogController extends Controller
{
    /**
     * Display a listing of student daily logs
     */
    public function index(Request $request): Response
    {
        // Get all classrooms for filter
        $classrooms = Classroom::with('academicYear')->get();

        $query = StudentDailyLog::with(['student.classroom', 'recordedBy'])
            ->orderBy('date', 'desc');

        // Filter by classroom if specified
        if ($request->filled('classroom_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('classroom_id', $request->classroom_id);
            });
        }

        // Filter by date range if specified
        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        // Filter by student if specified
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        $logs = $query->paginate(15)->withQueryString();

        return Inertia::render('Teacher/StudentDailyLogs/Index', [
            'logs' => $logs,
            'classrooms' => $classrooms,
            'filters' => $request->only(['classroom_id', 'start_date', 'end_date', 'student_id']),
        ]);
    }

    /**
     * Show the form for creating a new log (returns students for selected date)
     */
    public function create(Request $request): Response
    {
        $classrooms = Classroom::with(['students' => function ($query) {
            $query->where('status', 'Aktif')->orderBy('name');
        }])->get();

        $date = $request->input('date', now()->format('Y-m-d'));

        // Get students who already have logs for this date
        $existingLogs = StudentDailyLog::where('date', $date)->pluck('student_id')->toArray();

        return Inertia::render('Teacher/StudentDailyLogs/Create', [
            'classrooms' => $classrooms,
            'date' => $date,
            'existingLogs' => $existingLogs,
        ]);
    }

    /**
     * Store a newly created log
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'date' => 'required|date',
            'attendance_status' => 'required|in:Hadir,Sakit,Izin,Alpa',
            'arrival_time' => 'nullable|date_format:H:i',
            'pickup_time' => 'nullable|date_format:H:i',
            'mood' => 'nullable|in:Senang,Biasa,Sedih,Rewel',
            'activities' => 'nullable|string',
            'meals' => 'nullable|string',
            'nap_notes' => 'nullable|string',
            'health_notes' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['recorded_by'] = Auth::id();

        StudentDailyLog::create($validated);

        return redirect()->route('teacher.daily-logs.index')
            ->with('success', 'Log harian siswa berhasil disimpan!');
    }

    /**
     * Show the form for editing the specified log
     */
    public function edit(StudentDailyLog $dailyLog): Response
    {
        $dailyLog->load(['student.classroom', 'recordedBy']);

        return Inertia::render('Teacher/StudentDailyLogs/Edit', [
            'log' => $dailyLog,
        ]);
    }

    /**
     * Update the specified log
     */
    public function update(Request $request, StudentDailyLog $dailyLog): RedirectResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'attendance_status' => 'required|in:Hadir,Sakit,Izin,Alpa',
            'arrival_time' => 'nullable|date_format:H:i',
            'pickup_time' => 'nullable|date_format:H:i',
            'mood' => 'nullable|in:Senang,Biasa,Sedih,Rewel',
            'activities' => 'nullable|string',
            'meals' => 'nullable|string',
            'nap_notes' => 'nullable|string',
            'health_notes' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $dailyLog->update($validated);

        return redirect()->route('teacher.daily-logs.index')
            ->with('success', 'Log harian siswa berhasil diperbarui!');
    }

    /**
     * Remove the specified log
     */
    public function destroy(StudentDailyLog $dailyLog): RedirectResponse
    {
        $dailyLog->delete();

        return redirect()->route('teacher.daily-logs.index')
            ->with('success', 'Log harian siswa berhasil dihapus!');
    }

    /**
     * Get students for a specific classroom (API endpoint for dynamic loading)
     */
    public function getStudentsByClassroom(Request $request)
    {
        $classroomId = $request->input('classroom_id');
        $date = $request->input('date', now()->format('Y-m-d'));

        $students = Student::where('classroom_id', $classroomId)
            ->where('status', 'Aktif')
            ->orderBy('name')
            ->get();

        // Check which students already have logs for this date
        $existingLogs = StudentDailyLog::where('date', $date)
            ->whereIn('student_id', $students->pluck('id'))
            ->pluck('student_id')
            ->toArray();

        return response()->json([
            'students' => $students,
            'existingLogs' => $existingLogs,
        ]);
    }
}
