<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use Illuminate\Http\Request;

class AdmissionController extends Controller
{
    public function index()
    {
        $admissions = Admission::all();

        return view('admissions.index', compact('admissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'admission_no' => 'required|unique:admissions,admission_no',
            'reg_no' => 'required',
            'course' => 'required',
            'admission_date' => 'required|date',
            'status' => 'required',
        ]);

        Admission::create([
            'admission_no' => $request->admission_no,
            'reg_no' => $request->reg_no,
            'course' => $request->course,
            'admission_date' => $request->admission_date,
            'status' => $request->status,
        ]);

        return redirect()
            ->route('admissions.index')
            ->with('success', 'Admission added successfully!');
    }

    public function edit(Admission $admission)
    {
        return view('admissions.edit', compact('admission'));
    }

    public function update(Request $request, Admission $admission)
    {
        $request->validate([
            'admission_no' => 'required|unique:admissions,admission_no,' . $admission->id,
            'reg_no' => 'required',
            'course' => 'required',
            'admission_date' => 'required|date',
            'status' => 'required',
        ]);

        $admission->update([
            'admission_no' => $request->admission_no,
            'reg_no' => $request->reg_no,
            'course' => $request->course,
            'admission_date' => $request->admission_date,
            'status' => $request->status,
        ]);

        return redirect()
            ->route('admissions.index')
            ->with('success', 'Admission updated successfully!');
    }

    public function destroy(Admission $admission)
    {
        $admission->delete();

        return redirect()
            ->route('admissions.index')
            ->with('success', 'Admission deleted successfully!');
    }
}
