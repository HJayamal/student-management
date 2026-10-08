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

            /*
             * NEW student CSV format:
             * Reg No, First Name, Last Name, Gender, NIC,
             * Email, Phone, DOB, Password, Address
             */
            if (count($row) >= 10) {

                $regNo = trim($row[0]);

                if (Student::where('reg_no', $regNo)->exists()) {
                    $skipped++;
                    continue;
                }

                $firstName = trim($row[1]);
                $lastName = trim($row[2]);

                Student::create([
                    'reg_no' => $regNo,
                    'name' => trim($firstName . ' ' . $lastName),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'gender' => trim($row[3]),
                    'nic' => trim($row[4]),
                    'email' => trim($row[5]),
                    'phone' => trim($row[6]),
                    'dob' => trim($row[7]),
                    'password' => Hash::make($row[8]),
                    'address' => trim($row[9]),
                ]);

                $imported++;
            }

            /*
             * OLD student CSV format:
             * Reg No, Name, Email, Phone, DOB, Password, Address
             */
            elseif (count($row) >= 7) {

                $regNo = trim($row[0]);

                if (Student::where('reg_no', $regNo)->exists()) {
                    $skipped++;
                    continue;
                }

                $fullName = trim($row[1]);

                Student::create([
                    'reg_no' => $regNo,
                    'name' => $fullName,
                    'first_name' => $fullName,
                    'last_name' => null,
                    'gender' => null,
                    'nic' => null,
                    'email' => trim($row[2]),
                    'phone' => trim($row[3]),
                    'dob' => trim($row[4]),
                    'password' => Hash::make($row[5]),
                    'address' => trim($row[6]),
                ]);

                $imported++;
            }
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

        // Skip header row
        fgetcsv($file);

        $imported = 0;

        while (($row = fgetcsv($file)) !== false) {

            /*
             * NEW teacher CSV format:
             * Name, Email, Phone, Gender, Subject, Address
             */
            if (count($row) >= 6) {

                Teacher::create([
                    'name' => trim($row[0]),
                    'email' => trim($row[1]),
                    'phone' => trim($row[2]),
                    'gender' => trim($row[3]),
                    'subject' => trim($row[4]),
                    'address' => trim($row[5]),
                ]);

                $imported++;
            }

            /*
             * OLD teacher CSV format:
             * Name, Email, Phone, Subject, Address
             */
            elseif (count($row) >= 5) {

                Teacher::create([
                    'name' => trim($row[0]),
                    'email' => trim($row[1]),
                    'phone' => trim($row[2]),
                    'gender' => null,
                    'subject' => trim($row[3]),
                    'address' => trim($row[4]),
                ]);

                $imported++;
            }
        }

        fclose($file);

        return redirect()
            ->route('teachers.index')
            ->with(
                'success',
                "$imported teachers imported successfully!"
            );
    }
}
