<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $admin = Student::where('email', $validated['email'])
            ->where('role', 'admin')
            ->first();

        if (
            !$admin ||
            !$admin->password ||
            !Hash::check($validated['password'], $admin->password)
        ) {
            return response()->json([
                'message' => 'Invalid admin email or password.',
            ], 401);
        }

        $token = $admin->createToken('admin-mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Admin login successful.',
            'token' => $token,
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $admin = $request->user();

        if (!$admin instanceof Student || $admin->role !== 'admin') {
            return response()->json([
                'message' => 'Admin access only.',
            ], 403);
        }

        $admin->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Admin logged out successfully.',
        ]);
    }
}
