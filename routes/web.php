<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'login'])
    ->name('login');

Route::post('/login-check', [LoginController::class, 'loginCheck'])
    ->name('login.check');

Route::get('/logout', [LoginController::class, 'logout'])
    ->name('logout');

Route::view('/about', 'about')
    ->name('about');

Route::view('/contact', 'contact')
    ->name('contact');

Route::middleware('student.auth')->group(function () {

    Route::get('/', [AdminController::class, 'dashboard'])
        ->name('home');

    // Livewire Student Management Demo
    Route::view(
        '/students-livewire-demo',
        'students.livewire-demo'
    )->name('students.livewire-demo');

    // Student Management
    Route::resource('students', StudentController::class)
        ->except(['show']);

    Route::get('/students/export-pdf', [ExportController::class, 'studentsPdf'])
        ->name('students.export-pdf');

    Route::post('/students/import', [ImportController::class, 'students'])
        ->name('students.import');

    Route::get('/student-image-list', [StudentController::class, 'imageList'])
        ->name('students.image-list');

    // Admin-only Teacher Management routes
    Route::middleware('admin.auth')->group(function () {

        Route::get('/teachers/export-pdf', [ExportController::class, 'teachersPdf'])
            ->name('teachers.export-pdf');

        Route::post('/teachers/import', [ImportController::class, 'teachers'])
            ->name('teachers.import');

        Route::resource('teachers', TeacherController::class)
            ->except(['show']);
    });

    // Admission Management
    Route::resource('admissions', AdmissionController::class)
        ->except(['show']);

    // Subject Management
    Route::resource('subjects', SubjectController::class)
        ->except(['show']);

    // Exam Management
    Route::resource('exams', ExamController::class)
        ->except(['show']);
});
