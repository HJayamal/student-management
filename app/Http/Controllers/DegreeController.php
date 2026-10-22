<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Degree;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DegreeController extends Controller
{
    public function index()
    {
        $degrees = Degree::with('course')
            ->withCount('batches')
            ->orderBy('degree_name')
            ->paginate(10);

        $courses = Course::orderBy('course_name')->get();

        return view('academic.degrees.index', compact('degrees', 'courses'));
    }

    public function create()
    {
        $courses = Course::orderBy('course_name')->get();

        return view('academic.degrees.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'degree_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('degrees', 'degree_name')->where(
                    fn ($query) => $query->where('course_id', $request->input('course_id'))
                ),
            ],
            'description' => 'nullable|string',
        ]);

        Degree::create($validated);

        return redirect()->route('degrees.index')->with('success', 'Degree added successfully.');
    }

    public function edit(Degree $degree)
    {
        $courses = Course::orderBy('course_name')->get();

        return view('academic.degrees.edit', compact('degree', 'courses'));
    }

    public function update(Request $request, Degree $degree)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'degree_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('degrees', 'degree_name')
                    ->where(fn ($query) => $query->where('course_id', $request->input('course_id')))
                    ->ignore($degree->id),
            ],
            'description' => 'nullable|string',
        ]);

        $degree->update($validated);

        return redirect()->route('degrees.index')->with('success', 'Degree updated successfully.');
    }

    public function destroy(Degree $degree)
    {
        if ($degree->batches()->exists()) {
            return redirect()->route('degrees.index')->with(
                'error',
                'This degree cannot be deleted because batches are assigned to it.'
            );
        }

        $degree->delete();

        return redirect()->route('degrees.index')->with('success', 'Degree deleted successfully.');
    }
}
