<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\APIController;
use App\Http\Controllers\StudentAuthController;
use App\Http\Controllers\QuizAttemptController;
use App\Http\Controllers\TeacherAuthController;
use App\Http\Controllers\TeacherDashboardController;
use App\Http\Controllers\TeacherQuizController;
use App\Http\Controllers\TeacherResultsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Existing authenticated user API
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Existing student APIs
Route::get('/student_list', [APIController::class, 'student_list']);
Route::post('/student/save', [APIController::class, 'store']);

// Student Login API
Route::post('/student/login', [StudentAuthController::class, 'login']);

// Teacher Login API
Route::post('/teacher/login', [TeacherAuthController::class, 'login']);

// Protected Teacher Dashboard API
Route::middleware('auth:sanctum')->get(
    '/teacher/dashboard',
    [TeacherDashboardController::class, 'index']
);

// Protected APIs
Route::middleware('auth:sanctum')->group(function () {

    // ---------------- Student APIs ----------------

    // Student Logout
    Route::post(
        '/student/logout',
        [StudentAuthController::class, 'logout']
    );

    // Submit student quiz answers
    Route::post(
        '/student/quizzes/{quizId}/submit',
        [QuizAttemptController::class, 'store']
    );

    // ---------------- Teacher APIs ----------------

    // Teacher Logout
    Route::post(
        '/teacher/logout',
        [TeacherAuthController::class, 'logout']
    );

    // Get existing subjects
    Route::get('/teacher/subjects', function () {
        return DB::table('subjects')
            ->select('id', 'subject_name')
            ->orderBy('subject_name')
            ->get();
    });

    // Add a new subject
    Route::post('/teacher/subjects', function (Request $request) {
        $validated = $request->validate([
            'subject_name' => 'required|string|max:255|unique:subjects,subject_name',
        ]);

        do {
            $subjectCode = 'SUB-' . strtoupper(
                    \Illuminate\Support\Str::random(8)
                );
        } while (
            DB::table('subjects')
                ->where('subject_code', $subjectCode)
                ->exists()
        );

        $id = DB::table('subjects')->insertGetId([
            'subject_code' => $subjectCode,
            'course_name' => null,
            'subject_name' => $validated['subject_name'],
            'teacher' => $request->user()->name,
            'description' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $subject = DB::table('subjects')
            ->select('id', 'subject_name')
            ->where('id', $id)
            ->first();

        return response()->json([
            'message' => 'Subject added successfully.',
            'subject' => $subject,
        ], 201);
    });

    // Teacher Results: view student submissions
    Route::get(
        '/teacher/results',
        [TeacherResultsController::class, 'index']
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

    // Edit a quiz
    Route::put(
        '/teacher/quizzes/{id}',
        [TeacherQuizController::class, 'update']
    );

    // Delete a quiz
    Route::delete(
        '/teacher/quizzes/{id}',
        [TeacherQuizController::class, 'destroy']
    );

    // Publish a quiz
    Route::post(
        '/teacher/quizzes/{id}/publish',
        [TeacherQuizController::class, 'publish']
    );
});
