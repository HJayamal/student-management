<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class TeacherAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $teacher = Teacher::where(
            'email',
            $validated['email']
        )->first();

        if (
            ! $teacher ||
            ! $teacher->password ||
            ! Hash::check(
                $validated['password'],
                $teacher->password
            )
        ) {
            return response()->json([
                'message' => 'Invalid email or password.',
            ], 401);
        }

        $token = $teacher
            ->createToken('teacher-react-app')
            ->plainTextToken;

        return response()->json([
            'message' => 'Teacher login successful.',
            'token' => $token,
            'teacher' => [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
                'subject' => $teacher->subject,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Teacher logged out successfully.',
        ]);
    }
}
