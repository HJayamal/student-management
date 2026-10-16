<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherDashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $teacher = $request->user();

        return response()->json([
            'message' => 'Teacher dashboard loaded successfully.',
            'teacher' => [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
                'phone' => $teacher->phone,
                'subject' => $teacher->subject,
                'image' => $teacher->image,
            ],
        ]);
    }
}
