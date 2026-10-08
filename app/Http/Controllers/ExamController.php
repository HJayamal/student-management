<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\Request;
use App\Models\Subject;

class ExamController extends Controller
{
    public function index()
    {
        $exams = Exam::all();

        $subjects = Subject::all();

        return view('exams.index', compact('exams', 'subjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'exam_name' => 'required',
            'course_name' => 'required',
            'subject' => 'required',
            'marks' => 'required|integer|min:0|max:100',
            'exam_date' => 'required|date',
            'duration' => 'required',
            'status' => 'required',
        ]);

        Exam::create([
            'exam_name' => $request->exam_name,
            'course_name' => $request->course_name,
            'subject' => $request->subject,
            'marks' => $request->marks,
            'exam_date' => $request->exam_date,
            'duration' => $request->duration,
            'status' => $request->status,
        ]);

        return redirect()
            ->route('exams.index')
            ->with('success', 'Exam added successfully!');
    }

    public function edit(Exam $exam)
    {
        return view('exams.edit', compact('exam'));
    }

    public function update(Request $request, Exam $exam)
    {
        $request->validate([
            'exam_name' => 'required',
            'course_name' => 'required',
            'subject' => 'required',
            'marks' => 'required|integer|min:0|max:100',
            'exam_date' => 'required|date',
            'duration' => 'required',
            'status' => 'required',
        ]);

        $exam->update([
            'exam_name' => $request->exam_name,
            'course_name' => $request->course_name,
            'subject' => $request->subject,
            'marks' => $request->marks,
            'exam_date' => $request->exam_date,
            'duration' => $request->duration,
            'status' => $request->status,
        ]);

        return redirect()
            ->route('exams.index')
            ->with('success', 'Exam updated successfully!');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();

        return redirect()
            ->route('exams.index')
            ->with('success', 'Exam deleted successfully!');
    }
}
