<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $admin = $request->user();

        if (!$admin instanceof Student || $admin->role !== 'admin') {
            return response()->json([
                'message' => 'Admin access only.',
            ], 403);
        }

        return response()->json([
            'message' => 'Admin dashboard data retrieved successfully.',

            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role,
            ],

            'statistics' => [
                'students' => DB::table('students')
                    ->where('role', 'student')->count(),

                'teachers' => DB::table('teachers')->count(),

                'admissions' => DB::table('admissions')->count(),

                'courses' => DB::table('courses')->count(),

                'degrees' => DB::table('degrees')->count(),

                'batches' => DB::table('batches')->count(),

                'subjects' => DB::table('subjects')->count(),

                'exams' => DB::table('exams')->count(),
            ],
        ]);
    }
}
