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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('student_id')->constrained('student_profiles')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained('instructor_profiles')->nullOnDelete();
            $table->enum('status', ['pending', 'active', 'completed', 'dropped'])->default('pending');
            $table->decimal('progress_percent', 5, 2)->default(0);
            $table->decimal('final_grade', 5, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('target_completion_date')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0);
            $table->decimal('tdc_hours_completed', 5, 2)->default(0);
            $table->decimal('tdc_hours_required', 5, 2)->default(0);
            $table->decimal('pdc_hours_completed', 5, 2)->default(0);
            $table->decimal('pdc_hours_required', 5, 2)->default(0);
            $table->decimal('pdc_kms_driven', 10, 2)->default(0);
            $table->enum('tdc_status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->enum('pdc_status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
