
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Http\Controllers\APIController;
use App\Http\Controllers\StudentAuthController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\StudentQuizController;
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

// Authenticated user
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

    // Student Dashboard
    Route::get(
        '/student/dashboard',
        [StudentDashboardController::class, 'index']
    );

    // Get quizzes matching the student's academic group
    Route::get(
        '/student/quizzes',
        [StudentQuizController::class, 'index']
    );

    // Submit a quiz attempt
    Route::post(
        '/student/quizzes/{quizId}/submit',
        [QuizAttemptController::class, 'store']
    );

    // Student Logout
    Route::post(
        '/student/logout',
        [StudentAuthController::class, 'logout']
    );

    // ---------------- Teacher APIs ----------------

    // Teacher Logout
    Route::post(
        '/teacher/logout',
        [TeacherAuthController::class, 'logout']
    );

    /*
    |--------------------------------------------------------------------------
    | Get Subjects with Course and Degree Information
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Add a Subject Assigned to a Degree
    |--------------------------------------------------------------------------
    */

    Route::post('/teacher/subjects', function (Request $request) {

        $validated = $request->validate([
            'subject_name' => [
                'required',
                'string',
                'max:255',
                'unique:subjects,subject_name',
            ],
            'degree_id' => [
                'required',
                'integer',
                'exists:degrees,id',
            ],
        ]);

        // Get the course linked to the selected degree
        $degree = DB::table('degrees')
            ->join(
                'courses',
                'degrees.course_id',
                '=',
                'courses.id'
            )
            ->where('degrees.id', $validated['degree_id'])
            ->select(
                'degrees.id as degree_id',
                'courses.course_name'
            )
            ->first();

        if (!$degree) {
            return response()->json([
                'message' => 'The selected degree is not linked to a valid course.',
            ], 422);
        }

        // Generate a unique subject code
        do {
            $subjectCode = 'SUB-' . strtoupper(Str::random(8));
        } while (
            DB::table('subjects')
                ->where('subject_code', $subjectCode)
                ->exists()
        );

        $id = DB::table('subjects')->insertGetId([
            'subject_code' => $subjectCode,
            'course_name' => $degree->course_name,
            'degree_id' => $degree->degree_id,
            'subject_name' => $validated['subject_name'],
            'teacher' => $request->user()->name,
            'description' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $subject = DB::table('subjects')
            ->select(
                'id',
                'subject_name',
                'degree_id',
                'course_name'
            )
            ->where('id', $id)
            ->first();

        return response()->json([
            'message' => 'Subject added successfully.',
            'subject' => $subject,
        ], 201);
    });

    /*
    |--------------------------------------------------------------------------
    | Course + Degree + Batch Options
    |--------------------------------------------------------------------------
    | Uses Academic Setup tables:
    | courses -> degrees -> batches
    */

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
            ->whereNotNull('courses.course_name')
            ->whereNotNull('degrees.degree_name')
            ->whereNotNull('batches.batch_name')
            ->whereRaw("TRIM(courses.course_name) <> ''")
            ->whereRaw("TRIM(degrees.degree_name) <> ''")
            ->whereRaw("TRIM(batches.batch_name) <> ''")
            ->selectRaw(
                'courses.id AS course_id,
                 TRIM(courses.course_name) AS course,
                 degrees.id AS degree_id,
                 TRIM(degrees.degree_name) AS degree,
                 batches.id AS batch_id,
                 TRIM(batches.batch_name) AS batch'
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

    // ---------------- Teacher Results ----------------

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
