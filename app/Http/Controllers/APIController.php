<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class APIController extends Controller
{

    public function student_list(): JsonResponse
    {
        $students = Student::query()
            ->select([
                'id',
                'reg_no',
                'name',
                'email',
                'phone',
                'dob',
                'address',
                'image',
                'created_at',
                'updated_at',
            ])
            ->orderBy('id')
            ->get();

        return response()->json($students);
    }


    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reg_no' => 'required|string|max:255|unique:students,reg_no',
            'name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:20',
            'nic' => 'nullable|string|max:50',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'dob' => 'required|date_format:Y-m-d',
            'password' => 'required|string|min:6',
            'address' => 'required|string',
            'img' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $imagePath = null;

        if ($request->hasFile('img')) {
            $imagePath = $request->file('img')->store('students', 'public');
        }

        $student = Student::create([
            'reg_no' => $validated['reg_no'],
            'name' => $validated['name'],
            'first_name' => $validated['first_name'] ?? null,
            'last_name' => $validated['last_name'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'nic' => $validated['nic'] ?? null,
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'dob' => $validated['dob'],
            'password' => Hash::make($validated['password']),
            'address' => $validated['address'],
            'image' => $imagePath,
        ]);

        return response()->json([
            'message' => 'Student created successfully',
            'student' => [
                'id' => $student->id,
                'reg_no' => $student->reg_no,
                'name' => $student->name,
                'email' => $student->email,
                'phone' => $student->phone,
                'dob' => $student->dob,
                'address' => $student->address,
                'image' => $student->image,
            ],
        ], 201);
    }
}
