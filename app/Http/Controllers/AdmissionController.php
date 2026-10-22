<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Course;
use App\Models\Degree;
use App\Models\Batch;
use Illuminate\Http\Request;

class AdmissionController extends Controller
{
    /**
     * Display admissions and academic dropdown options.
     */
    public function index()
    {
        $admissions = Admission::all();

        $courses = Course::orderBy('course_name')->get();

        $degrees = Degree::with('course')
            ->orderBy('degree_name')
            ->get();

        $batches = Batch::with('degree.course')
            ->orderBy('batch_name')
            ->get();

        return view('admissions.index', compact(
            'admissions',
            'courses',
            'degrees',
            'batches'
        ));
    }

    /**
     * Store a new admission.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'admission_no' => 'required|string|max:255|unique:admissions,admission_no',
            'reg_no' => 'required|string|max:255',
            'course_id' => 'required|integer|exists:courses,id',
            'degree_id' => 'required|integer|exists:degrees,id',
            'batch_id' => 'required|integer|exists:batches,id',
            'admission_date' => 'required|date',
            'status' => 'required|string|max:255',
        ]);

        $course = Course::findOrFail($validated['course_id']);

        $degree = Degree::where('id', $validated['degree_id'])
            ->where('course_id', $course->id)
            ->first();

        if (!$degree) {
            return back()
                ->withErrors([
                    'degree_id' => 'Please select a degree belonging to the selected course.',
                ])
                ->withInput();
        }

        $batch = Batch::where('id', $validated['batch_id'])
            ->where('degree_id', $degree->id)
            ->first();

        if (!$batch) {
            return back()
                ->withErrors([
                    'batch_id' => 'Please select a batch belonging to the selected degree.',
                ])
                ->withInput();
        }

        Admission::create([
            'admission_no' => $validated['admission_no'],
            'reg_no' => $validated['reg_no'],
            'course' => $course->course_name,
            'degree' => $degree->degree_name,
            'batch' => $batch->batch_name,
            'admission_date' => $validated['admission_date'],
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admissions.index')
            ->with('success', 'Admission added successfully!');
    }

    /**
     * Show the edit admission form.
     */
    public function edit(Admission $admission)
    {
        $courses = Course::orderBy('course_name')->get();

        $degrees = Degree::with('course')
            ->orderBy('degree_name')
            ->get();

        $batches = Batch::with('degree.course')
            ->orderBy('batch_name')
            ->get();

        return view('admissions.edit', compact(
            'admission',
            'courses',
            'degrees',
            'batches'
        ));
    }

    /**
     * Update an existing admission.
     */
    public function update(Request $request, Admission $admission)
    {
        $validated = $request->validate([
            'admission_no' => [
                'required',
                'string',
                'max:255',
                'unique:admissions,admission_no,' . $admission->id,
            ],
            'reg_no' => 'required|string|max:255',
            'course_id' => 'required|integer|exists:courses,id',
            'degree_id' => 'required|integer|exists:degrees,id',
            'batch_id' => 'required|integer|exists:batches,id',
            'admission_date' => 'required|date',
            'status' => 'required|string|max:255',
        ]);

        $course = Course::findOrFail($validated['course_id']);

        $degree = Degree::where('id', $validated['degree_id'])
            ->where('course_id', $course->id)
            ->first();

        if (!$degree) {
            return back()
                ->withErrors([
                    'degree_id' => 'Please select a degree belonging to the selected course.',
                ])
                ->withInput();
        }

        $batch = Batch::where('id', $validated['batch_id'])
            ->where('degree_id', $degree->id)
            ->first();

        if (!$batch) {
            return back()
                ->withErrors([
                    'batch_id' => 'Please select a batch belonging to the selected degree.',
                ])
                ->withInput();
        }

        $admission->update([
            'admission_no' => $validated['admission_no'],
            'reg_no' => $validated['reg_no'],
            'course' => $course->course_name,
            'degree' => $degree->degree_name,
            'batch' => $batch->batch_name,
            'admission_date' => $validated['admission_date'],
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admissions.index')
            ->with('success', 'Admission updated successfully!');
    }

    /**
     * Delete an admission.
     */
    public function destroy(Admission $admission)
    {
        $admission->delete();

        return redirect()
            ->route('admissions.index')
            ->with('success', 'Admission deleted successfully!');
    }
}
