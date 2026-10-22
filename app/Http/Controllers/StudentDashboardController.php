<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StudentDashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $student = $request->user();

        // Only authenticated student accounts can access this dashboard.
        if (
            ! $student instanceof Student ||
            $student->role !== 'student'
        ) {
            return response()->json([
                'message' => 'Only student accounts can access this dashboard.',
            ], 403);
        }

        // Load the student's linked admission record.
        $student->load('admission');

        $admission = $student->admission;

        return response()->json([
            'message' => 'Student dashboard loaded successfully.',

            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'reg_no' => $student->reg_no,
                'email' => $student->email,
                'image' => $student->image,
            ],

            'academic' => $admission ? [
                'course' => $admission->course,
                'degree' => $admission->degree,
                'batch' => $admission->batch,
                'admission_status' => $admission->status,
            ] : null,
        ]);
    }
}
