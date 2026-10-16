<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class TeacherQuizController extends Controller
{
    /**
     * Get quizzes created by the logged-in teacher.
     */
    public function index(Request $request): JsonResponse
    {
        $teacher = $request->user();

        $quizzes = Quiz::query()
            ->where('teacher_id', $teacher->id)
            ->select([
                'id',
                'title',
                'grade',
                'subject',
                'description',
                'duration_minutes',
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

    /**
     * Create a quiz and save its questions.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'grade' => 'required|integer|min:1|max:13',
            'subject' => 'required|string|max:100',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1|max:300',

            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.option_a' => 'required|string',
            'questions.*.option_b' => 'required|string',
            'questions.*.option_c' => 'required|string',
            'questions.*.option_d' => 'required|string',
            'questions.*.correct_option' => 'required|in:A,B,C,D',
            'questions.*.marks' => 'required|integer|min:1|max:100',
        ]);

        $quiz = DB::transaction(function () use ($validated, $request) {
            $quiz = Quiz::create([
                'teacher_id' => $request->user()->id,
                'title' => $validated['title'],
                'grade' => $validated['grade'],
                'subject' => $validated['subject'],
                'description' => $validated['description'] ?? null,
                'duration_minutes' => $validated['duration_minutes'],
                'status' => 'draft',
                'published_at' => null,
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
            'message' => 'Quiz saved as draft successfully.',
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'grade' => $quiz->grade,
                'subject' => $quiz->subject,
                'duration_minutes' => $quiz->duration_minutes,
                'status' => $quiz->status,
                'questions_count' => count($validated['questions']),
            ],
        ], 201);
    }

    /**
     * Publish a draft quiz owned by the logged-in teacher.
     */
    public function publish(Request $request, int $id): JsonResponse
    {
        $teacher = $request->user();

        $quiz = Quiz::where('teacher_id', $teacher->id)
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
        $quiz->published_at = now();
        $quiz->save();

        return response()->json([
            'message' => 'Quiz published successfully.',
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'grade' => $quiz->grade,
                'subject' => $quiz->subject,
                'status' => $quiz->status,
                'published_at' => $quiz->published_at,
            ],
        ]);
    }
}
