<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\APIController;
use App\Http\Controllers\StudentAuthController;
use App\Http\Controllers\StudentDashboardController;
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

// Student Login
Route::post('/student/login', [StudentAuthController::class, 'login']);

// Teacher Login
Route::post('/teacher/login', [TeacherAuthController::class, 'login']);

// Protected Teacher Dashboard
Route::middleware('auth:sanctum')->get(
    '/teacher/dashboard',
    [TeacherDashboardController::class, 'index']
);

// Protected APIs
Route::middleware('auth:sanctum')->group(function () {

    // ---------------- Student APIs ----------------

    Route::get(
        '/student/dashboard',
        [StudentDashboardController::class, 'index']
    );

    Route::post(
        '/student/logout',
        [StudentAuthController::class, 'logout']
    );

    Route::post(
        '/student/quizzes/{quizId}/submit',
        [QuizAttemptController::class, 'store']
    );

    // ---------------- Teacher APIs ----------------

    Route::post(
        '/teacher/logout',
        [TeacherAuthController::class, 'logout']
    );

    // Get subjects with Course and Degree information
    Route::get('/teacher/subjects', function () {
        return DB::table('subjects')
            ->leftJoin(
                'degrees',
                'subjects.degree_id',
                '=',
                'degrees.id'
            )
            ->leftJoin(
                'courses',
                'degrees.course_id',
                '=',
                'courses.id'
            )
            ->select(
                'subjects.id',
                'subjects.subject_name',
                'subjects.degree_id',
                'degrees.degree_name',
                'degrees.course_id',
                DB::raw(
                    'COALESCE(courses.course_name, subjects.course_name) AS course_name'
                )
            )
            ->orderBy('subjects.subject_name')
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
            'degree_id' => null,
            'subject_name' => $validated['subject_name'],
            'teacher' => $request->user()->name,
            'description' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $subject = DB::table('subjects')
            ->select('id', 'subject_name', 'degree_id', 'course_name')
            ->where('id', $id)
            ->first();

        return response()->json([
            'message' => 'Subject added successfully.',
            'subject' => $subject,
        ], 201);
    });

    // Get Course + Degree + Batch combinations from Academic Setup
    Route::get('/teacher/academic-options', function () {
        $academicOptions = DB::table('courses')
            ->join(
                'degrees',
                'degrees.course_id',
                '=',
                'courses.id'
            )
            ->join(
                'batches',
                'batches.degree_id',
                '=',
                'degrees.id'
            )
            ->select(
                'courses.id as course_id',
                'courses.course_name as course',
                'degrees.id as degree_id',
                'degrees.degree_name as degree',
                'batches.id as batch_id',
                'batches.batch_name as batch'
            )
            ->distinct()
            ->orderBy('courses.course_name')
            ->orderBy('degrees.degree_name')
            ->orderBy('batches.batch_name')
            ->get();

        return response()->json([
            'academic_options' => $academicOptions,
        ]);
    });

    // Teacher Results
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

    // Preview a quiz
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
