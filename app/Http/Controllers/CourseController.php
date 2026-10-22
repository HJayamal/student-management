<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    // Display all courses
    public function index()
    {
        $courses = Course::withCount('degrees')
            ->orderBy('course_name')
            ->paginate(10);

        return view('academic.courses.index', compact('courses'));
    }

    // Show create course form
    public function create()
    {
        return view('academic.courses.create');
    }

    // Save a new course
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_name' => [
                'required',
                'string',
                'max:255',
                'unique:courses,course_name',
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ]);

        Course::create($validated);

        return redirect()
            ->route('courses.index')
            ->with('success', 'Course added successfully.');
    }

    // Show edit course form
    public function edit(Course $course)
    {
        return view('academic.courses.edit', compact('course'));
    }

    // Update an existing course
    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'course_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('courses', 'course_name')
                    ->ignore($course->id),
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ]);

        $course->update($validated);

        return redirect()
            ->route('courses.index')
            ->with('success', 'Course updated successfully.');
    }

    // Delete a course only when it has no degrees
    public function destroy(Course $course)
    {
        if ($course->degrees()->exists()) {
            return redirect()
                ->route('courses.index')
                ->with(
                    'error',
                    'This course cannot be deleted because it has degrees assigned to it.'
                );
        }

        $course->delete();

        return redirect()
            ->route('courses.index')
            ->with('success', 'Course deleted successfully.');
    }
}
