<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->string('course')->nullable()->after('subject');
            $table->string('degree')->nullable()->after('course');
            $table->string('batch', 50)->nullable()->after('degree');
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn([
                'course',
                'degree',
                'batch',
            ]);
        });
    }
};
