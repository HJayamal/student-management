<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Degree;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class BatchController extends Controller
{
    public function index()
    {
        $batches = Batch::with('degree.course')
            ->orderBy('batch_name')
            ->paginate(10);

        return view('academic.batches.index', compact('batches'));
    }

    public function create()
    {
        $degrees = Degree::with('course')
            ->orderBy('degree_name')
            ->get();

        return view('academic.batches.create', compact('degrees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'degree_id' => 'required|exists:degrees,id',
            'batch_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('batches', 'batch_name')
                    ->where(fn ($query) => $query->where(
                        'degree_id',
                        $request->input('degree_id')
                    )),
            ],
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        Batch::create($validated);

        return redirect()
            ->route('batches.index')
            ->with('success', 'Batch added successfully.');
    }

    public function edit(Batch $batch)
    {
        $degrees = Degree::with('course')
            ->orderBy('degree_name')
            ->get();

        return view(
            'academic.batches.edit',
            compact('batch', 'degrees')
        );
    }

    public function update(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'degree_id' => 'required|exists:degrees,id',
            'batch_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('batches', 'batch_name')
                    ->where(fn ($query) => $query->where(
                        'degree_id',
                        $request->input('degree_id')
                    ))
                    ->ignore($batch->id),
            ],
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $batch->update($validated);

        return redirect()
            ->route('batches.index')
            ->with('success', 'Batch updated successfully.');
    }

    public function destroy(Batch $batch)
    {
        $batch->load('degree.course');

        $courseName = $batch->degree->course->course_name;
        $degreeName = $batch->degree->degree_name;
        $batchName = $batch->batch_name;

        $hasAdmissions = DB::table('admissions')
            ->where('course', $courseName)
            ->where('degree', $degreeName)
            ->where('batch', $batchName)
            ->exists();

        $hasQuizzes = DB::table('quizzes')
            ->where('course', $courseName)
            ->where('degree', $degreeName)
            ->where('batch', $batchName)
            ->exists();

        if ($hasAdmissions || $hasQuizzes) {
            return redirect()
                ->route('batches.index')
                ->with(
                    'error',
                    'This batch is already used by admissions or quizzes and cannot be deleted.'
                );
        }

        $batch->delete();

        return redirect()
            ->route('batches.index')
            ->with('success', 'Batch deleted successfully.');
    }
}
