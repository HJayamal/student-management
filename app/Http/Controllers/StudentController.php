<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $students = Student::when($search, function ($query) use ($search) {
            $query->where('reg_no', 'like', "%$search%")
                ->orWhere('name', 'like', "%$search%");
        })->get();

        return view('students.index', compact('students'));
    }

    // Store new student
    public function store(Request $request)
    {
        $request->validate([
            'reg_no' => 'required|unique:students,reg_no',
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'dob' => 'required|date',
            'password' => 'required|min:6',
            'address' => 'required',
        ]);

        Student::create([
            'reg_no' => $request->reg_no,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'dob' => $request->dob,
            'password' => $request->password,
            'address' => $request->address,
        ]);

        return redirect()
            ->route('students.index')
            ->with('success', 'Student registered successfully!');
    }

    // Show edit form
    public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
    }

    // Update student
    public function update(Request $request, Student $student)
    {
        $request->validate([
            'reg_no' => 'required|unique:students,reg_no,' . $student->id,
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'dob' => 'required|date',
            'address' => 'required',
        ]);

        $student->update([
            'reg_no' => $request->reg_no,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'dob' => $request->dob,
            'address' => $request->address,
        ]);

        // Update password only if a new password is entered
        if ($request->filled('password')) {
            $student->update([
                'password' => $request->password,
            ]);
        }

        return redirect()
            ->route('students.index')
            ->with('success', 'Student updated successfully!');
    }

    // Delete student
    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()
            ->route('students.index')
            ->with('success', 'Student deleted successfully!');
    }
}
