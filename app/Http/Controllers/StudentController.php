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
            'name' => 'required',
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

        Student::create([
            'reg_no' => $request->reg_no,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'dob' => $request->dob,

            // Hash password
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
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'dob' => 'required|date',
            'password' => 'nullable|min:6',
            'address' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = [
            'reg_no' => $request->reg_no,
            'name' => $request->name,
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
