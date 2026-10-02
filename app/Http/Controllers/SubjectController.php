<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::all();

        return view('subjects.index', compact('subjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject_code' => 'required|unique:subjects,subject_code',
            'subject_name' => 'required',
            'teacher' => 'required',
            'description' => 'nullable',
        ]);

        Subject::create([
            'subject_code' => $request->subject_code,
            'subject_name' => $request->subject_name,
            'teacher' => $request->teacher,
            'description' => $request->description,
        ]);

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Subject added successfully!');
    }

    public function edit(Subject $subject)
    {
        return view('subjects.edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'subject_code' => 'required|unique:subjects,subject_code,' . $subject->id,
            'subject_name' => 'required',
            'teacher' => 'required',
            'description' => 'nullable',
        ]);

        $subject->update([
            'subject_code' => $request->subject_code,
            'subject_name' => $request->subject_name,
            'teacher' => $request->teacher,
            'description' => $request->description,
        ]);

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Subject updated successfully!');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Subject deleted successfully!');
    }
}
