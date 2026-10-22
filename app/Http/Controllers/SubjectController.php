<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Course;
use App\Models\Degree;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    // Display subjects and available degrees
    public function index()
    {
        $subjects = Subject::with('degree.course')
            ->orderBy('subject_name')
            ->get();

        $degrees = Degree::with('course')
            ->orderBy('degree_name')
            ->get();

        return view('subjects.index', compact(
            'subjects',
            'degrees'
        ));
    }

    // Save a new subject
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_code' => 'required|string|max:255|unique:subjects,subject_code',
            'degree_id' => 'required|integer|exists:degrees,id',
            'subject_name' => 'required|string|max:255',
            'teacher' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $degree = Degree::with('course')
            ->findOrFail($validated['degree_id']);

        Subject::create([
            'subject_code' => $validated['subject_code'],
            'course_name' => $degree->course->course_name,
            'degree_id' => $degree->id,
            'subject_name' => $validated['subject_name'],
            'teacher' => $validated['teacher'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Subject added successfully!');
    }

    // Show edit form
    public function edit(Subject $subject)
    {
        $degrees = Degree::with('course')
            ->orderBy('degree_name')
            ->get();

        return view('subjects.edit', compact(
            'subject',
            'degrees'
        ));
    }

    // Update a subject
    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'subject_code' => [
                'required',
                'string',
                'max:255',
                'unique:subjects,subject_code,' . $subject->id,
            ],
            'degree_id' => 'required|integer|exists:degrees,id',
            'subject_name' => 'required|string|max:255',
            'teacher' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $degree = Degree::with('course')
            ->findOrFail($validated['degree_id']);

        $subject->update([
            'subject_code' => $validated['subject_code'],
            'course_name' => $degree->course->course_name,
            'degree_id' => $degree->id,
            'subject_name' => $validated['subject_name'],
            'teacher' => $validated['teacher'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Subject updated successfully!');
    }

    // Delete a subject
    public function destroy(Subject $subject)
    {
        $subject->delete();

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Subject deleted successfully!');
    }
}
