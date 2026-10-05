<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login()
    {
        if (session()->has('student_id')) {
            return redirect()->route('home');
        }

        return view('login');
    }

    public function loginCheck(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $student = Student::where('email', $request->email)->first();

        if ($student && Hash::check($request->password, $student->password)) {

            session([
                'student_id' => $student->id,
                'student_name' => $student->name,
            ]);

            return redirect()->route('home');
        }

        return back()->with('error', 'Invalid email or password.');
    }

    public function logout()
    {
        session()->flush();

        return redirect()->route('login');
    }
}
