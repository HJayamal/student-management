<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ImportController;



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

    Route::resource('students', StudentController::class)
        ->except(['show']);

    Route::get('/students/export-pdf', [ExportController::class, 'studentsPdf'])
        ->name('students.export-pdf');

    Route::get('/teachers/export-pdf', [ExportController::class, 'teachersPdf'])
        ->name('teachers.export-pdf');

    Route::post('/students/import', [ImportController::class, 'students'])
        ->name('students.import');

    Route::post('/teachers/import', [ImportController::class, 'teachers'])
        ->name('teachers.import');

    Route::get('/student-image-list', [StudentController::class, 'imageList'])
        ->name('students.image-list');

    Route::resource('admissions', AdmissionController::class)
        ->except(['show']);

    Route::resource('teachers', TeacherController::class)
        ->except(['show']);

    Route::resource('subjects', SubjectController::class)
        ->except(['show']);

    Route::resource('exams', ExamController::class)
        ->except(['show']);
});
