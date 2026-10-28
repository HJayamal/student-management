<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptSession;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class QuizAttemptController extends Controller
{
    /**
     * Create or resume the student's server-timed attempt for this quiz.
     * An expired in-progress session is deliberately returned with its original
     * deadline so the client can auto-submit it; the deadline is never reset.
     */
    public function start(Request $request, int $quizId): JsonResponse
    {
        $student = $request->user();

        if (! $student instanceof Student || $student->role !== 'student') {
            return response()->json([
                'message' => 'Only authenticated students can start quizzes.',
            ], 403);
        }

        $quizOrResponse = $this->findAccessibleQuiz($student, $quizId);
        if ($quizOrResponse instanceof JsonResponse) {
            return $quizOrResponse;
        }
        $quiz = $quizOrResponse;

        $durationMinutes = (int) $quiz->duration_minutes;
        if ($durationMinutes < 1) {
            return response()->json([
                'message' => 'This quiz does not have a valid time limit. Please ask your teacher to set the quiz duration.',
            ], 422);
        }

        if (! $quiz->questions()->exists()) {
            return response()->json([
                'message' => 'This quiz does not contain any questions.',
            ], 422);
        }

        $session = DB::transaction(function () use ($student, $quiz, $durationMinutes) {
            // Serialize starts for the same student, preventing two tabs from
            // creating separate active sessions at the same time.
            Student::query()->whereKey($student->id)->lockForUpdate()->first();

            $existingSession = QuizAttemptSession::query()
                ->where('student_id', $student->id)
                ->where('quiz_id', $quiz->id)
                ->where('status', 'in_progress')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if ($existingSession) {
                return $existingSession;
            }

            $startedAt = now();

            return QuizAttemptSession::query()->create([
                'quiz_id' => $quiz->id,
                'student_id' => $student->id,
                'answers' => [],
                'started_at' => $startedAt,
                'deadline_at' => $startedAt->copy()->addMinutes($durationMinutes),
                'status' => 'in_progress',
            ]);
        });

        return response()->json([
            'message' => 'Quiz attempt started.',
            'attempt_session' => [
                'id' => $session->id,
                'quiz_id' => $session->quiz_id,
                'started_at' => $session->started_at?->toISOString(),
                'deadline_at' => $session->deadline_at?->toISOString(),
                'answers' => $session->answers ?? (object) [],
                'status' => $session->status,
            ],
            'server_time' => now()->toISOString(),
        ]);
    }

    /** Persist each selected option on the server while the deadline is active. */
    public function saveAnswer(Request $request, int $quizId): JsonResponse
    {
        $student = $request->user();

        if (! $student instanceof Student || $student->role !== 'student') {
            return response()->json([
                'message' => 'Only authenticated students can save quiz answers.',
            ], 403);
        }

        $validated = $request->validate([
            'attempt_session_id' => ['required', 'integer', 'min:1'],
            'question_id' => ['required', 'integer', 'min:1'],
            'selected_option' => ['required', 'string', Rule::in(['A', 'B', 'C', 'D'])],
        ]);

        $outcome = DB::transaction(function () use ($student, $quizId, $validated) {
            $session = QuizAttemptSession::query()
                ->whereKey((int) $validated['attempt_session_id'])
                ->where('quiz_id', $quizId)
                ->where('student_id', $student->id)
                ->lockForUpdate()
                ->first();

            if (! $session || $session->status !== 'in_progress') {
                return [
                    'error' => 'No active quiz attempt was found. Please reopen the quiz.',
                    'status' => 404,
                ];
            }

            if (now()->greaterThanOrEqualTo($session->deadline_at)) {
                return [
                    'error' => 'The quiz time has expired. Your saved answers will be submitted automatically.',
                    'status' => 409,
                ];
            }

            $questionId = (int) $validated['question_id'];
            $questionExists = Quiz::query()
                ->whereKey($quizId)
                ->whereHas('questions', fn ($query) => $query->whereKey($questionId))
                ->exists();

            if (! $questionExists) {
                return [
                    'error' => 'The selected question does not belong to this quiz.',
                    'status' => 422,
                ];
            }

            $answers = $session->answers ?? [];
            $answers[(string) $questionId] = strtoupper($validated['selected_option']);
            $session->answers = $answers;
            $session->save();

            return ['saved' => true];
        });

        if (isset($outcome['error'])) {
            return response()->json(['message' => $outcome['error']], $outcome['status']);
        }

        return response()->json(['message' => 'Answer saved.']);
    }

    /**
     * Submit and grade an attempt. The server's stored deadline decides which
     * answers may be used. Once time expires, client-sent answers are ignored;
     * only options saved before the deadline are graded.
     */
    public function store(Request $request, int $quizId): JsonResponse
    {
        $student = $request->user();

        if (! $student instanceof Student || $student->role !== 'student') {
            return response()->json([
                'message' => 'Only authenticated students can submit quizzes.',
            ], 403);
        }

        $validated = $request->validate([
            'attempt_session_id' => ['required', 'integer', 'min:1'],
            'timed_out' => ['sometimes', 'boolean'],
            'answers' => ['sometimes', 'array'],
            'answers.*' => ['nullable', 'string', Rule::in(['A', 'B', 'C', 'D', ''])],
        ]);

        $clientAnswers = $validated['answers'] ?? [];

        $outcome = DB::transaction(function () use (
            $student,
            $quizId,
            $validated,
            $clientAnswers
        ) {
            $session = QuizAttemptSession::query()
                ->whereKey((int) $validated['attempt_session_id'])
                ->where('quiz_id', $quizId)
                ->where('student_id', $student->id)
                ->lockForUpdate()
                ->first();

            if (! $session) {
                return [
                    'error' => 'No quiz attempt was found. Please start the quiz again.',
                    'status' => 404,
                ];
            }

            // If the submit response was lost, return the original attempt instead
            // of creating a duplicate result on retry.
            if ($session->status === 'completed' && $session->quiz_attempt_id) {
                $existingAttempt = QuizAttempt::query()->find($session->quiz_attempt_id);
                if ($existingAttempt) {
                    return [
                        'status' => 200,
                        'body' => $this->formatAttemptResponse(
                            $existingAttempt,
                            'This quiz attempt was already submitted.'
                        ),
                    ];
                }
            }

            if ($session->status !== 'in_progress') {
                return [
                    'error' => 'This quiz attempt is no longer active. Please refresh your results.',
                    'status' => 409,
                ];
            }

            $quiz = Quiz::query()->find($quizId);
            if (! $quiz) {
                return [
                    'error' => 'This quiz is no longer available.',
                    'status' => 404,
                ];
            }

            $questions = $quiz->questions()
                ->get(['id', 'correct_option', 'marks'])
                ->keyBy('id');

            if ($questions->isEmpty()) {
                return [
                    'error' => 'This quiz does not contain any questions.',
                    'status' => 422,
                ];
            }

            $timeExpired = now()->greaterThanOrEqualTo($session->deadline_at);
            // Never trust the client-supplied timed_out flag. Only the persisted
            // server deadline authorizes a timeout submission using saved answers.
            $useServerSavedAnswers = $timeExpired;
            $rawAnswers = $useServerSavedAnswers
                ? ($session->answers ?? [])
                : $clientAnswers;

            if (! $useServerSavedAnswers && count($rawAnswers) === 0) {
                return [
                    'error' => 'Please answer all questions before submitting.',
                    'status' => 422,
                ];
            }

            $answers = [];
            foreach ($rawAnswers as $questionId => $selectedOption) {
                if (! ctype_digit((string) $questionId)) {
                    return [
                        'error' => 'One or more submitted questions are invalid.',
                        'status' => 422,
                    ];
                }

                $numericQuestionId = (int) $questionId;
                if (! $questions->has($numericQuestionId)) {
                    return [
                        'error' => 'One or more submitted questions are invalid.',
                        'status' => 422,
                    ];
                }

                $option = strtoupper(trim((string) ($selectedOption ?? '')));
                if ($option === '') {
                    // Blank answers are allowed only for a timeout submission.
                    if (! $useServerSavedAnswers) {
                        return [
                            'error' => 'Please answer all questions before submitting.',
                            'status' => 422,
                        ];
                    }
                    continue;
                }

                if (! in_array($option, ['A', 'B', 'C', 'D'], true)) {
                    return [
                        'error' => 'One or more submitted answers are invalid.',
                        'status' => 422,
                    ];
                }

                $answers[$numericQuestionId] = $option;
            }

            if (! $useServerSavedAnswers) {
                foreach ($questions as $question) {
                    if (! array_key_exists((int) $question->id, $answers)) {
                        return [
                            'error' => 'Please answer all questions before submitting.',
                            'status' => 422,
                        ];
                    }
                }
            }

            $totalMarks = (int) $questions->sum(
                fn ($question) => max(0, (int) $question->marks)
            );
            $marksObtained = 0;

            foreach ($questions as $question) {
                $selectedOption = $answers[(int) $question->id] ?? '';
                $correctOption = strtoupper(trim((string) $question->correct_option));

                if ($correctOption !== '' && $selectedOption === $correctOption) {
                    $marksObtained += max(0, (int) $question->marks);
                }
            }

            $percentage = $totalMarks > 0
                ? round(($marksObtained / $totalMarks) * 100, 2)
                : 0.0;
            $submittedAt = now();

            $attempt = QuizAttempt::query()->create([
                'quiz_id' => $quiz->id,
                'student_id' => $student->id,
                'answers' => $answers,
                'submitted_at' => $submittedAt,
                'marks_obtained' => $marksObtained,
                'total_marks' => $totalMarks,
                'percentage' => $percentage,
                'graded_at' => $submittedAt,
            ]);

            $session->answers = $answers;
            $session->status = 'completed';
            $session->submitted_at = $submittedAt;
            $session->quiz_attempt_id = $attempt->id;
            $session->save();

            return [
                'status' => 201,
                'body' => $this->formatAttemptResponse(
                    $attempt,
                    $useServerSavedAnswers
                        ? 'Time is up. Saved answers were submitted and graded.'
                        : 'Quiz submitted and graded successfully.'
                ),
            ];
        });

        if (isset($outcome['error'])) {
            return response()->json(['message' => $outcome['error']], $outcome['status']);
        }

        return response()->json($outcome['body'], $outcome['status']);
    }

    /** Return the published quiz only when the student is assigned to it. */
    private function findAccessibleQuiz(Student $student, int $quizId): Quiz|JsonResponse
    {
        $student->loadMissing('admission');
        $admission = $student->admission;

        if (! $admission || strtolower(trim((string) $admission->status)) !== 'active') {
            return response()->json([
                'message' => 'An active admission is required.',
            ], 403);
        }

        $quiz = Quiz::query()->find($quizId);
        if (! $quiz) {
            return response()->json(['message' => 'Quiz not found.'], 404);
        }

        if ($quiz->status !== 'published') {
            return response()->json([
                'message' => 'This quiz is not available for submission.',
            ], 422);
        }

        if ($quiz->available_at && Carbon::parse($quiz->available_at)->isFuture()) {
            return response()->json([
                'message' => 'This quiz is not available yet.',
            ], 403);
        }

        $matchesAcademicGroup =
            strtolower(trim((string) $admission->course)) === strtolower(trim((string) $quiz->course))
            && strtolower(trim((string) $admission->degree)) === strtolower(trim((string) $quiz->degree))
            && strtolower(trim((string) $admission->batch)) === strtolower(trim((string) $quiz->batch));

        if (! $matchesAcademicGroup) {
            return response()->json([
                'message' => 'This quiz is not assigned to your academic group.',
            ], 403);
        }

        return $quiz;
    }

    private function formatAttemptResponse(QuizAttempt $attempt, string $message): array
    {
        return [
            'message' => $message,
            'attempt' => [
                'id' => $attempt->id,
                'quiz_id' => $attempt->quiz_id,
                'submitted_at' => $attempt->submitted_at?->toISOString(),
            ],
            'result' => [
                'marks_obtained' => (int) $attempt->marks_obtained,
                'total_marks' => (int) $attempt->total_marks,
                'percentage' => (float) $attempt->percentage,
            ],
        ];
    }
}
