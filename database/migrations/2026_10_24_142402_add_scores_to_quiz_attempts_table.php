
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->unsignedInteger('marks_obtained')->default(0);
            $table->unsignedInteger('total_marks')->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->dateTime('graded_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn([
                'marks_obtained',
                'total_marks',
                'percentage',
                'graded_at',
            ]);
        });
    }
};
