<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class StudentAuthController extends Controller
{
    /**
     * Student login using email and password.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $student = Student::where('email', $validated['email'])->first();

        if (
            ! $student ||
            ! $student->password ||
            ! Hash::check($validated['password'], $student->password)
        ) {
            return response()->json([
                'message' => 'Invalid email or password.',
            ], 401);
        }

        $token = $student->createToken('student-react-app')->plainTextToken;

        return response()->json([
            'message' => 'Student login successful.',
            'token' => $token,
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'reg_no' => $student->reg_no,
            ],
        ]);
    }

    /**
     * Revoke the currently authenticated student's token.
     */
    public function logout(Request $request): JsonResponse
    {
        $student = $request->user();

        if (! $student instanceof Student) {
            return response()->json([
                'message' => 'Only students can use this endpoint.',
            ], 403);
        }

        $token = $student->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Student logged out successfully.',
        ]);
    }
}
