<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\AssessmentAspectController;
use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Parent\DashboardController as ParentDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Teacher\DailyLogController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
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
    return redirect()->route('login');
});

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
});

// Teacher Routes
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');

    // Daily Logs Management
    Route::get('/daily-logs', [DailyLogController::class, 'index'])->name('daily-logs.index');
    Route::post('/daily-logs', [DailyLogController::class, 'store'])->name('daily-logs.store');
});

// Parent Routes
Route::middleware(['auth', 'role:parent'])->prefix('parent')->name('parent.')->group(function () {
    Route::get('/dashboard', [ParentDashboardController::class, 'index'])->name('dashboard');
});

// Profile Routes (accessible by all authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
