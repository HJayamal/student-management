<?php

namespace App\Http\Controllers;

use App\Models\QuizAttempt;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StudentResultsController extends Controller
{
    /**
     * Get the authenticated student's quiz results.
     */
    public function index(Request $request): JsonResponse
    {
        $student = $request->user();

        // Students can access only their own results.
        if (
            ! $student instanceof Student ||
            $student->role !== 'student'
        ) {
            return response()->json([
                'message' => 'Only students can access their results.',
            ], 403);
        }

        $attempts = QuizAttempt::query()
            ->with(['quiz:id,title,subject'])
            ->where('student_id', $student->id)
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->get([
                'id',
                'quiz_id',
                'student_id',
                'marks_obtained',
                'total_marks',
                'percentage',
                'submitted_at',
                'graded_at',
            ]);

        $results = $attempts->map(function (QuizAttempt $attempt) {
            return [
                'attempt_id' => $attempt->id,
                'quiz_id' => $attempt->quiz_id,
                'quiz_title' => $attempt->quiz?->title
                    ?? 'Quiz no longer available',
                'subject' => $attempt->quiz?->subject,
                'marks_obtained' => (int) $attempt->marks_obtained,
                'total_marks' => (int) $attempt->total_marks,
                'percentage' => (float) $attempt->percentage,
                'submitted_at' => $attempt->submitted_at?->toISOString(),
                'graded_at' => $attempt->graded_at?->toISOString(),
            ];
        });

        return response()->json([
            'message' => 'Student results retrieved successfully.',
            'total_attempts' => $results->count(),
            'results' => $results,
        ]);
    }
}
