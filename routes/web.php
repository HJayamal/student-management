<?php

use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/


Route::get('/', [AdminController::class, 'dashboard'])
    ->name('home');

Route::resource('students', StudentController::class)
    ->except(['show']);

Route::resource('admissions', AdmissionController::class)
    ->except(['show']);

Route::resource('teachers', TeacherController::class)
    ->except(['show']);

Route::resource('subjects', SubjectController::class)
    ->except(['show']);

Route::resource('exams', ExamController::class)
    ->except(['show']);


Route::view('/about', 'about')
    ->name('about');


Route::view('/contact', 'contact')
    ->name('contact');
