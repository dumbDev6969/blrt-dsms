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
        Schema::create('instructor_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('instructor_profiles')->cascadeOnDelete();
            $table->date('metric_month');
            $table->integer('total_sessions');
            $table->integer('completed_sessions');
            $table->decimal('total_hours', 10, 2);
            $table->decimal('avg_rating', 3, 2);
            $table->integer('students_taught');
            $table->integer('students_passed');
            $table->decimal('pass_rate', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructor_metrics');
    }
};
