<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class QuizAttemptController extends Controller
{
    /**
     * Submit answers and automatically calculate the student's score.
     */
    public function store(Request $request, int $quizId): JsonResponse
    {
        $student = $request->user();

        if (
            ! $student instanceof Student ||
            $student->role !== 'student'
        ) {
            return response()->json([
                'message' => 'Only authenticated students can submit quizzes.',
            ], 403);
        }

        // Verify that the student has an active admission.
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

        $quiz = Quiz::findOrFail($quizId);

        if ($quiz->status !== 'published') {
            return response()->json([
                'message' => 'This quiz is not available for submission.',
            ], 422);
        }

        // Check quiz availability time.
        if (
            $quiz->available_at &&
            Carbon::parse($quiz->available_at)->isFuture()
        ) {
            return response()->json([
                'message' => 'This quiz is not available yet.',
            ], 403);
        }

        // Ensure the quiz belongs to this student's academic group.
        $matchesAcademicGroup =
            strtolower(trim((string) $admission->course))
            === strtolower(trim((string) $quiz->course))
            && strtolower(trim((string) $admission->degree))
            === strtolower(trim((string) $quiz->degree))
            && strtolower(trim((string) $admission->batch))
            === strtolower(trim((string) $quiz->batch));

        if (! $matchesAcademicGroup) {
            return response()->json([
                'message' => 'This quiz is not assigned to your academic group.',
            ], 403);
        }

        $validated = $request->validate([
            'answers' => 'required|array|min:1',
            'answers.*' => 'required|string|in:A,B,C,D',
        ]);

        $answers = $validated['answers'];

        // Load the quiz questions and correct answers on the server.
        $questions = $quiz->questions()
            ->get([
                'id',
                'correct_option',
                'marks',
            ])
            ->keyBy('id');

        if ($questions->isEmpty()) {
            return response()->json([
                'message' => 'This quiz does not contain any questions.',
            ], 422);
        }

        // Reject question IDs that do not belong to this quiz.
        foreach ($answers as $questionId => $selectedOption) {
            if (
                ! ctype_digit((string) $questionId) ||
                ! $questions->has($questionId)
            ) {
                return response()->json([
                    'message' => 'One or more submitted questions are invalid.',
                ], 422);
            }
        }

        // Calculate the maximum possible marks.
        $totalMarks = (int) $questions->sum(
            fn ($question) => (int) $question->marks
        );

        // Calculate marks using the server-side correct answers.
        $marksObtained = 0;

        foreach ($questions as $question) {
            $selectedOption = strtoupper(
                (string) ($answers[$question->id] ?? '')
            );

            $correctOption = strtoupper(
                trim((string) $question->correct_option)
            );

            if (
                $selectedOption !== '' &&
                $selectedOption === $correctOption
            ) {
                $marksObtained += (int) $question->marks;
            }
        }

        $percentage = $totalMarks > 0
            ? round(($marksObtained / $totalMarks) * 100, 2)
            : 0;

        // Save the answers and calculated score together.
        $attempt = DB::transaction(function () use (
            $quiz,
            $student,
            $answers,
            $marksObtained,
            $totalMarks,
            $percentage
        ) {
            return QuizAttempt::create([
                'quiz_id' => $quiz->id,
                'student_id' => $student->id,
                'answers' => $answers,
                'submitted_at' => now(),
                'marks_obtained' => $marksObtained,
                'total_marks' => $totalMarks,
                'percentage' => $percentage,
                'graded_at' => now(),
            ]);
        });

        return response()->json([
            'message' => 'Quiz submitted and graded successfully.',
            'attempt' => [
                'id' => $attempt->id,
                'quiz_id' => $attempt->quiz_id,
                'submitted_at' => $attempt->submitted_at,
            ],
            'result' => [
                'marks_obtained' => $marksObtained,
                'total_marks' => $totalMarks,
                'percentage' => $percentage,
            ],
        ], 201);
    }
}
