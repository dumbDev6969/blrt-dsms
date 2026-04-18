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
        Schema::create('system_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('metric_date');
            $table->integer('new_students')->default(0);
            $table->integer('active_enrollments')->default(0);
            $table->integer('completed_courses')->default(0);
            $table->integer('total_bookings')->default(0);
            $table->decimal('revenue', 15, 2)->default(0);
            $table->json('additional_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_metrics');
    }
};
