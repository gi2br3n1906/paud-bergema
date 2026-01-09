<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\AssessmentAspectController;
use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\DapodikImportController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Parent\AuthController as ParentAuthController;
use App\Http\Controllers\Parent\ParentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Teacher\AssessmentController;
use App\Http\Controllers\Teacher\DailyLogController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\GrowthRecordController;
use App\Http\Controllers\Teacher\ReportCardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        $role = auth()->user()->role;
        return match($role->value) {
            'admin' => redirect()->route('admin.dashboard'),
            'teacher' => redirect()->route('teacher.dashboard'),
            'parent' => redirect()->route('parent.dashboard'),
            default => redirect()->route('login'),
        };
    }
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('welcome');

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Academic Years Management
    Route::resource('academic-years', AcademicYearController::class)->except(['show', 'create', 'edit']);
    Route::post('/academic-years/{academic_year}/activate', [AcademicYearController::class, 'activate'])->name('academic-years.activate');

    // Classrooms Management
    Route::resource('classrooms', ClassroomController::class)->except(['show', 'create', 'edit']);

    // Students Management
    Route::resource('students', StudentController::class)->except(['show', 'create', 'edit']);

    // Teachers Management
    Route::resource('teachers', TeacherController::class)->except(['show', 'create', 'edit']);

    // Assessment Aspects Management
    Route::resource('assessment-aspects', AssessmentAspectController::class)->except(['show', 'create', 'edit']);

    // Dapodik Import
    Route::get('/dapodik-import', [DapodikImportController::class, 'index'])->name('dapodik-import.index');
    Route::post('/dapodik-import', [DapodikImportController::class, 'import'])->name('dapodik-import.store');
});

// Teacher Routes
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');

    // Daily Logs Management
    Route::get('/daily-logs', [DailyLogController::class, 'index'])->name('daily-logs.index');
    Route::post('/daily-logs', [DailyLogController::class, 'store'])->name('daily-logs.store');

    // Growth Records Management
    Route::get('/growth-records', [GrowthRecordController::class, 'index'])->name('growth-records.index');
    Route::get('/growth-records/{student}', [GrowthRecordController::class, 'show'])->name('growth-records.show');
    Route::post('/growth-records', [GrowthRecordController::class, 'store'])->name('growth-records.store');
    Route::put('/growth-records/{growthRecord}', [GrowthRecordController::class, 'update'])->name('growth-records.update');
    Route::delete('/growth-records/{growthRecord}', [GrowthRecordController::class, 'destroy'])->name('growth-records.destroy');

    // Assessments Management
    Route::get('/assessments', [AssessmentController::class, 'index'])->name('assessments.index');
    Route::get('/assessments/{classroom}/{term}', [AssessmentController::class, 'form'])->name('assessments.form');
    Route::post('/assessments', [AssessmentController::class, 'store'])->name('assessments.store');

    // Report Cards Management
    Route::get('/reports', [ReportCardController::class, 'index'])->name('reports.index');
    Route::get('/reports/statistics', [ReportCardController::class, 'statistics'])->name('reports.statistics');
    Route::get('/reports/{classroom}/{term}/students', [ReportCardController::class, 'students'])->name('reports.students');
    Route::get('/reports/{student}/{term}', [ReportCardController::class, 'preview'])->name('reports.preview');
    Route::get('/reports/{student}/{term}/assess', [ReportCardController::class, 'assess'])->name('reports.assess');
    Route::post('/reports/{student}/{term}/assess', [ReportCardController::class, 'saveAssessment'])->name('reports.save-assessment');
    Route::get('/reports/{student}/{term}/pdf', [ReportCardController::class, 'previewPdf'])->name('reports.preview-pdf');
    Route::post('/reports/{student}/{term}/generate-narrative/{aspect}', [ReportCardController::class, 'generateNarrative'])
        ->middleware('throttle:20,1')
        ->name('reports.generate-narrative');
    Route::post('/reports/{student}/{term}/generate-narratives', [ReportCardController::class, 'generateBulkNarratives'])
        ->middleware('throttle:5,1')
        ->name('reports.generate-bulk-narratives');
    Route::post('/reports/{student}/{term}/publish', [ReportCardController::class, 'publishReportCard'])->name('reports.publish');
    Route::get('/reports/{student}/{term}/download', [ReportCardController::class, 'downloadPdf'])->name('reports.download-pdf');

    // Class Journals Management
    Route::get('/class-journals', [\App\Http\Controllers\Teacher\ClassJournalController::class, 'index'])->name('class-journals.index');
    Route::post('/class-journals', [\App\Http\Controllers\Teacher\ClassJournalController::class, 'store'])->name('class-journals.store');
    Route::put('/class-journals/{classJournal}', [\App\Http\Controllers\Teacher\ClassJournalController::class, 'update'])->name('class-journals.update');
    Route::delete('/class-journals/{classJournal}', [\App\Http\Controllers\Teacher\ClassJournalController::class, 'destroy'])->name('class-journals.destroy');

    // Student Daily Logs Management
    Route::get('/daily-logs', [\App\Http\Controllers\Teacher\StudentDailyLogController::class, 'index'])->name('daily-logs.index');
    Route::get('/daily-logs/create', [\App\Http\Controllers\Teacher\StudentDailyLogController::class, 'create'])->name('daily-logs.create');
    Route::post('/daily-logs', [\App\Http\Controllers\Teacher\StudentDailyLogController::class, 'store'])->name('daily-logs.store');
    Route::get('/daily-logs/{dailyLog}/edit', [\App\Http\Controllers\Teacher\StudentDailyLogController::class, 'edit'])->name('daily-logs.edit');
    Route::put('/daily-logs/{dailyLog}', [\App\Http\Controllers\Teacher\StudentDailyLogController::class, 'update'])->name('daily-logs.update');
    Route::delete('/daily-logs/{dailyLog}', [\App\Http\Controllers\Teacher\StudentDailyLogController::class, 'destroy'])->name('daily-logs.destroy');
    Route::get('/daily-logs/students-by-classroom', [\App\Http\Controllers\Teacher\StudentDailyLogController::class, 'getStudentsByClassroom'])->name('daily-logs.students-by-classroom');
});

// Parent Authentication Routes (Guest only)
Route::prefix('parent')->name('parent.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [ParentAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [ParentAuthController::class, 'login']);
    });
});

// Parent Routes (Authenticated parents only)
Route::middleware(['auth', 'parent'])->prefix('parent')->name('parent.')->group(function () {
    Route::post('/logout', [ParentAuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [ParentController::class, 'dashboard'])->name('dashboard');
    Route::get('/students/{student}', [ParentController::class, 'studentProfile'])->name('students.profile');
    Route::get('/students/{student}/reports/{term}', [ParentController::class, 'reportCard'])->name('students.reports.view');
    Route::get('/students/{student}/reports/{term}/download', [ParentController::class, 'downloadPdf'])->name('students.reports.download');
});

// Profile Routes (accessible by all authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
