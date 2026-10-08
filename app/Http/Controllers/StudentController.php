<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

    public function store(Request $request)
    {
        $request->validate([
            'reg_no' => 'required|unique:students,reg_no',
            'first_name' => 'required',
            'last_name' => 'required',
            'gender' => 'required',
            'nic' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'dob' => 'required|date',
            'password' => 'required|min:6',
            'address' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')
                ->store('students', 'public');
        }

        // Create full name for existing system functions
        $fullName = trim(
            $request->first_name . ' ' . $request->last_name
        );

        Student::create([
            'reg_no' => $request->reg_no,
            'name' => $fullName,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'gender' => $request->gender,
            'nic' => $request->nic,
            'email' => $request->email,
            'phone' => $request->phone,
            'dob' => $request->dob,
            'password' => Hash::make($request->password),
            'address' => $request->address,
            'image' => $imagePath,
        ]);

        return redirect()
            ->route('students.index')
            ->with('success', 'Student registered successfully!');
    }

    public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
    }

    public function update(Request $request, Student $student)
    {
        $request->validate([
            'reg_no' => 'required|unique:students,reg_no,' . $student->id,
            'first_name' => 'required',
            'last_name' => 'required',
            'gender' => 'required',
            'nic' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'dob' => 'required|date',
            'password' => 'nullable|min:6',
            'address' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = [
            'reg_no' => $request->reg_no,
            'name' => trim(
                $request->first_name . ' ' . $request->last_name
            ),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'gender' => $request->gender,
            'nic' => $request->nic,
            'email' => $request->email,
            'phone' => $request->phone,
            'dob' => $request->dob,
            'address' => $request->address,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')
                ->store('students', 'public');
        }

        $student->update($data);

        return redirect()
            ->route('students.index')
            ->with('success', 'Student updated successfully!');
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()
            ->route('students.index')
            ->with('success', 'Student deleted successfully!');
    }

    public function imageList()
    {
        $students = Student::all();

        return view('students.image-list', compact('students'));
    }
}
