<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ImportController extends Controller
{
    public function students(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = fopen($request->file('file')->getRealPath(), 'r');

        // Skip header row
        fgetcsv($file);

        $imported = 0;
        $skipped = 0;

        while (($row = fgetcsv($file)) !== false) {

            if (count($row) < 7) {
                continue;
            }

            $regNo = trim($row[0]);

            // Skip if Reg No already exists
            if (Student::where('reg_no', $regNo)->exists()) {
                $skipped++;
                continue;
            }

            Student::create([
                'reg_no' => $regNo,
                'name' => trim($row[1]),
                'email' => trim($row[2]),
                'phone' => trim($row[3]),
                'dob' => trim($row[4]),
                'password' => Hash::make($row[5]),
                'address' => trim($row[6]),
            ]);

            $imported++;
        }

        fclose($file);

        return redirect()
            ->route('students.index')
            ->with(
                'success',
                "$imported students imported successfully. $skipped duplicate students skipped."
            );
    }

    public function teachers(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = fopen($request->file('file')->getRealPath(), 'r');


        fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {

            if (count($row) < 5) {
                continue;
            }

            Teacher::create([
                'name' => $row[0],
                'email' => $row[1],
                'phone' => $row[2],
                'subject' => $row[3],
                'address' => $row[4],
            ]);
        }

        fclose($file);

        return redirect()
            ->route('teachers.index')
            ->with('success', 'Teachers imported successfully!');
    }
}
