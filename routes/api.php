<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APIController;
use App\Http\Controllers\TeacherAuthController;
use App\Http\Controllers\TeacherDashboardController;
use App\Http\Controllers\TeacherQuizController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Existing user API
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Student APIs
Route::get('/student_list', [APIController::class, 'student_list']);
Route::post('/student/save', [APIController::class, 'store']);

// Teacher Login API
Route::post('/teacher/login', [TeacherAuthController::class, 'login']);

// Protected Teacher Dashboard API
Route::middleware('auth:sanctum')->get(
    '/teacher/dashboard',
    [TeacherDashboardController::class, 'index']
);

// Protected Teacher APIs
Route::middleware('auth:sanctum')->group(function () {

    // Teacher Logout
    Route::post(
        '/teacher/logout',
        [TeacherAuthController::class, 'logout']
    );

    // List teacher's quizzes
    Route::get(
        '/teacher/quizzes',
        [TeacherQuizController::class, 'index']
    );

    // Create a quiz
    Route::post(
        '/teacher/quizzes',
        [TeacherQuizController::class, 'store']
    );

    // Preview one quiz with questions
    Route::get(
        '/teacher/quizzes/{id}',
        [TeacherQuizController::class, 'show']
    );

    // Edit a draft quiz
    Route::put(
        '/teacher/quizzes/{id}',
        [TeacherQuizController::class, 'update']
    );

    // Delete a draft quiz
    Route::delete(
        '/teacher/quizzes/{id}',
        [TeacherQuizController::class, 'destroy']
    );

    // Publish a draft quiz
    Route::post(
        '/teacher/quizzes/{id}/publish',
        [TeacherQuizController::class, 'publish']
    );
});
