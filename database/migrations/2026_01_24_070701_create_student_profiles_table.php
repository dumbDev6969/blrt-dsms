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
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            
            // PERSONAL DETAILS
            $table->date('birth_date');
            $table->string('contact_number');
            $table->string('address'); // Use ->text('address') if you expect very long addresses
            $table->enum('nationality', ['filipino', 'foreigner']);
            $table->boolean('is_minor')->default(false); // Helpful to have a default
            $table->string('occupation')->nullable(); // Nullable in case they are unemployed/student
            
            // ENUMS
            $table->enum('educational_attainment', ['elementary', 'high_school', 'college', 'post_graduate']);
            $table->enum('civil_status', ['single', 'married', 'widowed', 'separated']);
            $table->enum('sex', ['male', 'female']);
            
            // LICENSING IDS (Make these nullable for new students)
            $table->string('ltms_client_id')->nullable(); 
            $table->string('student_permit_or_license_no')->nullable();
            
            // JSON META DATA
            // Stores guardian info, passport details, etc.
            $table->json('meta_details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};
