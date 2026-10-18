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

            $request->session()->regenerate();

            session([
                'student_id' => $student->id,
                'student_name' => $student->name,
                'student_role' => $student->role ?? 'student',
            ]);

            return redirect()->route('home');
        }

        return back()
            ->withInput($request->only('email'))
            ->with('error', 'Invalid email or password.');
    }

    public function logout()
    {
        session()->flush();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}
