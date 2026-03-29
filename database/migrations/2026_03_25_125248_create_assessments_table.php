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
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('student_profiles')->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained('instructor_profiles')->cascadeOnDelete();
            $table->foreignId('booking_session_id')->nullable()->constrained('booking_sessions')->nullOnDelete();
            $table->enum('assessment_type', ['formative', 'summative', 'practical', 'theoretical']);
            $table->date('assessment_date');

            // PRE-DRIVE CHECKLIST (Section 2)
            $table->json('pre_drive_checklist')->nullable();
            $table->integer('pre_drive_errors')->default(0);

            // IMMEDIATE FAILS (Section 1)
            $table->json('immediate_fails')->nullable();
            $table->integer('immediate_fail_count')->default(0);

            // DRIVING SKILLS (Section 3)
            $table->json('driving_skills')->nullable();
            $table->integer('driving_skills_rating')->nullable()->comment('1=Poor, 2=Fair, 3=Good');
            $table->integer('number_of_attempts')->default(1);

            // OBSERVANCE TO TRAFFIC RULES (Section 4)
            $table->json('traffic_rules')->nullable();
            $table->integer('traffic_rules_rating')->nullable()->comment('1=Poor, 2=Fair, 3=Good');

            // OVERALL ASSESSMENT (Section 4)
            $table->enum('learner_type', ['slow', 'fast', 'hesitant'])->nullable();
            $table->boolean('recommended_for_additional_driving')->default(false);

            // REMARKS AND OBSERVATIONS (Section 5)
            $table->text('instructor_remarks')->nullable();
            $table->text('observations')->nullable();

            // PASS/FAIL
            $table->boolean('is_passed')->nullable();
            $table->text('failure_reason')->nullable();

            // SIGNATURES
            $table->text('instructor_signature_path')->nullable();
            $table->date('instructor_signature_date')->nullable();
            $table->text('admin_signature_path')->nullable();
            $table->date('admin_noted_date')->nullable();
            $table->foreignId('noted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
