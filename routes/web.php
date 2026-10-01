<?php

use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Home page
Route::get('/', [StudentController::class, 'index'])
    ->name('home');

// Student CRUD routes
Route::resource('students', StudentController::class)
    ->except(['show']);

// About Us
Route::view('/about', 'about')
    ->name('about');

// Contact Us
Route::view('/contact', 'contact')
    ->name('contact');
