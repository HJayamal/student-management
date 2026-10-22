<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $admissions = DB::table('admissions')
            ->select('course', 'degree', 'batch')
            ->get();

        foreach ($admissions as $admission) {
            $courseName = trim((string) ($admission->course ?? ''));
            $degreeName = trim((string) ($admission->degree ?? ''));
            $batchName = trim((string) ($admission->batch ?? ''));

            if ($courseName === '') {
                continue;
            }

            // Find or create course
            $course = DB::table('courses')
                ->where('course_name', $courseName)
                ->first();

            if (!$course) {
                $courseId = DB::table('courses')->insertGetId([
                    'course_name' => $courseName,
                    'description' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $courseId = $course->id;
            }

            if ($degreeName === '') {
                continue;
            }

            // Find or create degree under this course
            $degree = DB::table('degrees')
                ->where('course_id', $courseId)
                ->where('degree_name', $degreeName)
                ->first();

            if (!$degree) {
                $degreeId = DB::table('degrees')->insertGetId([
                    'course_id' => $courseId,
                    'degree_name' => $degreeName,
                    'description' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $degreeId = $degree->id;
            }

            if ($batchName === '') {
                continue;
            }

            // Find or create batch under this degree
            $batch = DB::table('batches')
                ->where('degree_id', $degreeId)
                ->where('batch_name', $batchName)
                ->first();

            if (!$batch) {
                DB::table('batches')->insert([
                    'degree_id' => $degreeId,
                    'batch_name' => $batchName,
                    'start_date' => null,
                    'end_date' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Keep imported academic records when rolling back migrations.
    }
};
