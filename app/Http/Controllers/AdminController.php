<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Admission;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Exam;

class AdminController extends Controller
{
    public function dashboard()
    {
        $students = Student::count();
        $admissions = Admission::count();
        $teachers = Teacher::count();
        $subjects = Subject::count();
        $exams = Exam::count();

        return view('admin.dashboard', compact(
            'students',
            'admissions',
            'teachers',
            'subjects',
            'exams'
        ));
    }
}
