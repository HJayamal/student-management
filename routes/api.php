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

// Protected Teacher Quiz APIs
Route::middleware('auth:sanctum')->group(function () {
    Route::get(
        '/teacher/quizzes',
        [TeacherQuizController::class, 'index']
    );


    Route::post(
        '/teacher/quizzes',
        [TeacherQuizController::class, 'store']
    );

    Route::post(
        '/teacher/quizzes/{id}/publish',
        [TeacherQuizController::class, 'publish']
    );

});
