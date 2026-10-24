<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class StudentQuizController extends Controller
{
    /**
     * Get quizzes assigned to the student's academic group.
     */
    public function index(Request $request): JsonResponse
    {
        $student = $request->user();

        if (! $student instanceof Student) {
            return response()->json([
                'message' => 'Only students can access their quizzes.',
            ], 403);
        }

        $student->load('admission');
        $admission = $student->admission;

        if (
            ! $admission ||
            strtolower(trim((string) $admission->status)) !== 'active'
        ) {
            return response()->json([
                'message' => 'No active admission was found.',
                'academic' => null,
                'quizzes' => [],
            ]);
        }

        $course = trim((string) $admission->course);
        $degree = trim((string) $admission->degree);
        $batch = trim((string) $admission->batch);

        if ($course === '' || $degree === '' || $batch === '') {
            return response()->json([
                'message' => 'Academic details are incomplete.',
                'academic' => [
                    'course' => $course,
                    'degree' => $degree,
                    'batch' => $batch,
                ],
                'quizzes' => [],
            ]);
        }

        $now = now();

        $quizzes = Quiz::query()
            ->where('status', 'published')
            ->whereRaw(
                'LOWER(TRIM(course)) = ?',
                [strtolower($course)]
            )
            ->whereRaw(
                'LOWER(TRIM(degree)) = ?',
                [strtolower($degree)]
            )
            ->whereRaw(
                'LOWER(TRIM(batch)) = ?',
                [strtolower($batch)]
            )
            ->select([
                'id',
                'title',
                'course',
                'degree',
                'batch',
                'subject',
                'description',
                'duration_minutes',
                'available_at',
                'status',
            ])
            ->withCount('questions')
            ->orderBy('available_at')
            ->orderBy('id')
            ->get()
            ->map(function (Quiz $quiz) use ($now) {
                $availableAt = $quiz->available_at
                    ? Carbon::parse($quiz->available_at)
                    : null;

                $isAvailable = $availableAt === null ||
                    $availableAt->lessThanOrEqualTo($now);

                return [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'course' => $quiz->course,
                    'degree' => $quiz->degree,
                    'batch' => $quiz->batch,
                    'subject' => $quiz->subject,
                    'description' => $quiz->description,
                    'duration_minutes' => $quiz->duration_minutes,
                    'available_at' => $quiz->available_at,
                    'questions_count' => $quiz->questions_count,
                    'is_available' => $isAvailable,
                    'availability_status' => $isAvailable
                        ? 'available'
                        : 'upcoming',
                ];
            });

        return response()->json([
            'message' => 'Student quizzes retrieved successfully.',
            'academic' => [
                'course' => $course,
                'degree' => $degree,
                'batch' => $batch,
            ],
            'quizzes' => $quizzes,
        ]);
    }

    /**
     * Load a quiz and its questions for a student.
     */
    public function show(
        Request $request,
        int $quizId
    ): JsonResponse {
        $student = $request->user();

        if (! $student instanceof Student) {
            return response()->json([
                'message' => 'Only students can open quizzes.',
            ], 403);
        }

        $student->load('admission');
        $admission = $student->admission;

        if (
            ! $admission ||
            strtolower(trim((string) $admission->status)) !== 'active'
        ) {
            return response()->json([
                'message' => 'An active admission is required.',
            ], 403);
        }

        $course = trim((string) $admission->course);
        $degree = trim((string) $admission->degree);
        $batch = trim((string) $admission->batch);

        $quiz = Quiz::query()
            ->whereKey($quizId)
            ->where('status', 'published')
            ->first();

        if (! $quiz) {
            return response()->json([
                'message' => 'Quiz not found or not published.',
            ], 404);
        }

        $matchesAcademicGroup =
            strtolower(trim((string) $quiz->course)) === strtolower($course)
            && strtolower(trim((string) $quiz->degree)) === strtolower($degree)
            && strtolower(trim((string) $quiz->batch)) === strtolower($batch);

        if (! $matchesAcademicGroup) {
            return response()->json([
                'message' => 'This quiz is not assigned to your academic group.',
            ], 403);
        }

        if (
            $quiz->available_at &&
            Carbon::parse($quiz->available_at)->isFuture()
        ) {
            return response()->json([
                'message' => 'This quiz is not available yet.',
                'available_at' => $quiz->available_at,
            ], 403);
        }

        $questions = $quiz->questions()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get([
                'id',
                'quiz_id',
                'question_text',
                'option_a',
                'option_b',
                'option_c',
                'option_d',
                'marks',
                'sort_order',
            ])
            ->map(function ($question) {
                return [
                    'id' => $question->id,
                    'question_text' => $question->question_text,
                    'options' => [
                        'A' => $question->option_a,
                        'B' => $question->option_b,
                        'C' => $question->option_c,
                        'D' => $question->option_d,
                    ],
                    'marks' => $question->marks,
                ];
            });

        return response()->json([
            'message' => 'Quiz loaded successfully.',
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'subject' => $quiz->subject,
                'duration_minutes' => $quiz->duration_minutes,
                'available_at' => $quiz->available_at,
            ],
            'questions' => $questions,
        ]);
    }
}
