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
        Schema::create('booking_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained('instructor_profiles')->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->enum('type', ['lecture', 'driving', 'assessment']);
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'no-show'])->default('scheduled');
            // ASSESSMENTS
            $table->decimal('score', 5, 2)->nullable();
            $table->boolean('is_passed')->nullable();

            // RATINGS
            $table->integer('rating')->nullable(); // 1-5
            $table->text('review_comment')->nullable();

            // PROGRESS
            $table->json('skill_progress')->nullable();

            // AUDIT
            $table->json('change_log')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_sessions');
    }
};
