<?php

namespace App\Http\Controllers;

use App\Models\QuizAttempt;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TeacherResultsController extends Controller
{
    /**
     * Get student submissions for quizzes owned by this teacher.
     */
    public function index(Request $request): JsonResponse
    {
        $teacher = $request->user();

        // Only authenticated teachers can view these results.
        if (! $teacher instanceof Teacher) {
            return response()->json([
                'message' => 'Only teachers can access student results.',
            ], 403);
        }

        $attempts = QuizAttempt::query()
            ->whereHas('quiz', function ($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })
            ->with([
                'student:id,name,email,reg_no',
                'quiz:id,teacher_id,title,grade,subject',
                'quiz.questions' => function ($query) {
                    $query->select([
                        'id',
                        'quiz_id',
                        'question_text',
                        'option_a',
                        'option_b',
                        'option_c',
                        'option_d',
                        'sort_order',
                    ])->orderBy('sort_order');
                },
            ])
            ->orderByDesc('submitted_at')
            ->get([
                'id',
                'quiz_id',
                'student_id',
                'answers',
                'submitted_at',
            ]);

        return response()->json([
            'message' => 'Student submissions retrieved successfully.',
            'total_submissions' => $attempts->count(),
            'submissions' => $attempts,
        ]);
    }
}
