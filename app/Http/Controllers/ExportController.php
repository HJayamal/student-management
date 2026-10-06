<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    public function studentsPdf()
    {
        $students = Student::all();

        $pdf = Pdf::loadView('pdf.students', compact('students'));

        return $pdf->download('students-list.pdf');
    }

    public function teachersPdf()
    {
        $teachers = Teacher::all();

        $pdf = Pdf::loadView('pdf.teachers', compact('teachers'));

        return $pdf->download('teachers-list.pdf');
    }
}
