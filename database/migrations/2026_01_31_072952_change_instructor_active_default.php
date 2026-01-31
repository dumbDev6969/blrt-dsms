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
        Schema::table('instructor_profiles', function (Blueprint $table) {
        // Change default to false
        $table->boolean('is_active')->default(false)->change();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instructor_profiles', function (Blueprint $table) {
            // Revert back to the original default (true)
            $table->boolean('is_active')->default(true)->change();
        });
    }
};
