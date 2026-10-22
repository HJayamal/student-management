<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class StudentQuizController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $student = $request->user();

        // Only authenticated student accounts can access this endpoint.
        if (
            ! $student instanceof Student ||
            $student->role !== 'student'
        ) {
            return response()->json([
                'message' => 'Only student accounts can access their quizzes.',
            ], 403);
        }

        // Get the student's admission record.
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
}
