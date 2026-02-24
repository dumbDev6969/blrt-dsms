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
        Schema::create('enrollment_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('student_profiles')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');

            $table->string('control_number')->unique()->comment('Top right of form');
            $table->enum('package_type', ['TDC', 'PDC', 'Refresher']);

            // PDC Specifics
            $table->enum('vehicle_category', ['4-Wheel', 'Motorcycle', 'Tricycle'])->nullable();
            $table->enum('transmission', ['Automatic', 'Manual'])->nullable();

            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->json('personal_info')->comment('{emergency_contact:{}}');
            $table->json('course_preferences')->comment('{schedule_pref:[], instructor_pref:null}');
            $table->text('rejection_reason')->nullable();

            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_forms');
    }
};
