<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('instructor_performances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('instructor_profiles')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('student_profiles')->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            $table->foreignId('booking_session_id')->constrained('booking_sessions')->cascadeOnDelete();
            $table->integer('rating');
            $table->json('performance_criteria');
            $table->text('feedback_comment')->nullable();
            $table->text('areas_of_strength')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->date('evaluation_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructor_performances');
    }
};
