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
        Schema::create('lto_clinics', function (Blueprint $table) {
            $table->id();
            $table->string('clinic_name');
            $table->string('accreditation_number')->unique();
            $table->string('address');
            $table->string('contact_number');
            $table->boolean('is_active')->default(true);
            $table->date('accreditation_expiry');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lto_clinics');
    }
};
