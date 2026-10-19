<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class QuizAttemptController extends Controller
{
    /**
     * Save a student's submitted quiz answers.
     */
    public function store(Request $request, int $quizId): JsonResponse
    {
        $student = $request->user();

        // Only authenticated student accounts may submit answers.
        if (! $student instanceof Student) {
            return response()->json([
                'message' => 'Only authenticated students can submit quizzes.',
            ], 403);
        }

        $quiz = Quiz::findOrFail($quizId);

        // Only published quizzes can be submitted.
        if ($quiz->status !== 'published') {
            return response()->json([
                'message' => 'This quiz is not available for submission.',
            ], 422);
        }

        // Do not allow submissions before the scheduled availability time.
        if (
            $quiz->available_at !== null
            && Carbon::parse($quiz->available_at)->isFuture()
        ) {
            return response()->json([
                'message' => 'This quiz is not available yet.',
            ], 403);
        }

        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'required|string|in:A,B,C,D',
        ]);

        $answers = $validated['answers'];
        $questionIds = array_keys($answers);

        // Confirm every submitted question belongs to this quiz.
        foreach ($questionIds as $questionId) {
            if (! ctype_digit((string) $questionId)) {
                return response()->json([
                    'message' => 'Invalid question ID submitted.',
                ], 422);
            }
        }

        $validQuestionCount = $quiz->questions()
            ->whereIn('id', $questionIds)
            ->count();

        if ($validQuestionCount !== count($questionIds)) {
            return response()->json([
                'message' => 'One or more questions do not belong to this quiz.',
            ], 422);
        }

        // Save the attempt without calculating marks.
        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
            'answers' => $answers,
            'submitted_at' => now(),
        ]);

        return response()->json([
            'message' => 'Quiz submitted successfully.',
            'attempt' => [
                'id' => $attempt->id,
                'quiz_id' => $attempt->quiz_id,
                'student_id' => $attempt->student_id,
                'answers' => $attempt->answers,
                'submitted_at' => $attempt->submitted_at,
            ],
        ], 201);
    }
}
