<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TeacherQuizController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $quizzes = Quiz::query()
            ->where('teacher_id', $request->user()->id)
            ->select([
                'id',
                'title',
                'grade',
                'course',
                'degree',
                'batch',
                'subject',
                'description',
                'duration_minutes',
                'available_at',
                'status',
                'published_at',
                'created_at',
            ])
            ->withCount('questions')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'message' => 'Teacher quizzes retrieved successfully.',
            'quizzes' => $quizzes,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',

            // Temporary legacy field; remove after the database migration.
            'grade' => 'required|integer|min:1|max:13',

            'course' => 'required|string|max:255',
            'degree' => 'required|string|max:255',
            'batch' => 'required|string|max:50',
            'subject' => 'required|string|max:100',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1|max:300',
            'available_at' => 'required|date|after:now',

            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.option_a' => 'required|string',
            'questions.*.option_b' => 'required|string',
            'questions.*.option_c' => 'required|string',
            'questions.*.option_d' => 'required|string',
            'questions.*.correct_option' => 'required|in:A,B,C,D',
            'questions.*.marks' => 'required|integer|min:1|max:100',
        ]);

        $validated['available_at'] = Carbon::parse(
            $validated['available_at']
        )->format('Y-m-d H:i:s');

        $quiz = DB::transaction(function () use ($validated, $request) {
            $quiz = Quiz::create([
                'teacher_id' => $request->user()->id,
                'title' => $validated['title'],
                'grade' => $validated['grade'],
                'course' => $validated['course'],
                'degree' => $validated['degree'],
                'batch' => $validated['batch'],
                'subject' => $validated['subject'],
                'description' => $validated['description'] ?? null,
                'duration_minutes' => $validated['duration_minutes'],
                'available_at' => $validated['available_at'],
                'status' => 'published',
                'published_at' => now(),
            ]);

            foreach ($validated['questions'] as $index => $question) {
                $quiz->questions()->create([
                    'question_text' => $question['question_text'],
                    'option_a' => $question['option_a'],
                    'option_b' => $question['option_b'],
                    'option_c' => $question['option_c'],
                    'option_d' => $question['option_d'],
                    'correct_option' => $question['correct_option'],
                    'marks' => $question['marks'],
                    'sort_order' => $index + 1,
                ]);
            }

            return $quiz;
        });

        return response()->json([
            'message' => 'Quiz scheduled successfully.',
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'course' => $quiz->course,
                'degree' => $quiz->degree,
                'batch' => $quiz->batch,
                'subject' => $quiz->subject,
                'duration_minutes' => $quiz->duration_minutes,
                'available_at' => $quiz->available_at,
                'status' => $quiz->status,
                'published_at' => $quiz->published_at,
                'questions_count' => count($validated['questions']),
            ],
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $quiz = Quiz::where('teacher_id', $request->user()->id)
            ->with(['questions' => function ($query) {
                $query->orderBy('sort_order');
            }])
            ->findOrFail($id);

        return response()->json([
            'message' => 'Quiz details retrieved successfully.',
            'quiz' => $quiz,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $quiz = Quiz::where('teacher_id', $request->user()->id)
            ->findOrFail($id);

        if (
            $quiz->status !== 'draft'
            && ! $this->isFutureScheduledQuiz($quiz)
        ) {
            return response()->json([
                'message' => 'Only draft or not-yet-available scheduled quizzes can be edited.',
            ], 422);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',

            // Temporary legacy field; remove after the database migration.
            'grade' => 'required|integer|min:1|max:13',

            'course' => 'required|string|max:255',
            'degree' => 'required|string|max:255',
            'batch' => 'required|string|max:50',
            'subject' => 'required|string|max:100',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1|max:300',
            'available_at' => 'required|date|after:now',

            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.option_a' => 'required|string',
            'questions.*.option_b' => 'required|string',
            'questions.*.option_c' => 'required|string',
            'questions.*.option_d' => 'required|string',
            'questions.*.correct_option' => 'required|in:A,B,C,D',
            'questions.*.marks' => 'required|integer|min:1|max:100',
        ]);

        $validated['available_at'] = Carbon::parse(
            $validated['available_at']
        )->format('Y-m-d H:i:s');

        DB::transaction(function () use ($quiz, $validated) {
            $quiz->update([
                'title' => $validated['title'],
                'grade' => $validated['grade'],
                'course' => $validated['course'],
                'degree' => $validated['degree'],
                'batch' => $validated['batch'],
                'subject' => $validated['subject'],
                'description' => $validated['description'] ?? null,
                'duration_minutes' => $validated['duration_minutes'],
                'available_at' => $validated['available_at'],
            ]);

            $quiz->questions()->delete();

            foreach ($validated['questions'] as $index => $question) {
                $quiz->questions()->create([
                    'question_text' => $question['question_text'],
                    'option_a' => $question['option_a'],
                    'option_b' => $question['option_b'],
                    'option_c' => $question['option_c'],
                    'option_d' => $question['option_d'],
                    'correct_option' => $question['correct_option'],
                    'marks' => $question['marks'],
                    'sort_order' => $index + 1,
                ]);
            }
        });

        return response()->json([
            'message' => 'Quiz schedule updated successfully.',
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $quiz = Quiz::where('teacher_id', $request->user()->id)
            ->findOrFail($id);

        if (
            $quiz->status !== 'draft'
            && ! $this->isFutureScheduledQuiz($quiz)
        ) {
            return response()->json([
                'message' => 'Only draft or not-yet-available scheduled quizzes can be deleted.',
            ], 422);
        }

        $quiz->delete();

        return response()->json([
            'message' => 'Quiz deleted successfully.',
        ]);
    }

    public function publish(Request $request, int $id): JsonResponse
    {
        $quiz = Quiz::where('teacher_id', $request->user()->id)
            ->findOrFail($id);

        if ($quiz->status !== 'draft') {
            return response()->json([
                'message' => 'Only draft quizzes can be published.',
            ], 422);
        }

        if (! $quiz->questions()->exists()) {
            return response()->json([
                'message' => 'Add at least one question before publishing.',
            ], 422);
        }

        $quiz->status = 'published';
        $quiz->available_at = $quiz->available_at ?? now();
        $quiz->published_at = now();
        $quiz->save();

        return response()->json([
            'message' => 'Quiz published successfully.',
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'course' => $quiz->course,
                'degree' => $quiz->degree,
                'batch' => $quiz->batch,
                'subject' => $quiz->subject,
                'available_at' => $quiz->available_at,
                'status' => $quiz->status,
                'published_at' => $quiz->published_at,
            ],
        ]);
    }

    private function isFutureScheduledQuiz(Quiz $quiz): bool
    {
        return $quiz->status === 'published'
            && $quiz->available_at !== null
            && Carbon::parse($quiz->available_at)->isFuture();
    }
}
