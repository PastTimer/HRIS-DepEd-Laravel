<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            
            // Personal Information
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('name_ext')->nullable(); // Jr, III, etc.
            $table->string('gender');
            $table->date('date_of_birth');
            $table->string('place_of_birth')->nullable();
            $table->string('civil_status')->nullable();
            $table->string('blood_type')->nullable();
            
            // Employment Information
            $table->string('employee_id')->nullable()->unique();
            $table->foreignId('position_id')->constrained('positions')->onDelete('restrict');
            $table->string('item_no')->nullable();
            $table->integer('step')->default(1);
            $table->date('last_step');
            $table->string('sg')->nullable();
            $table->string('employee_type'); // Regular, Contractual, Substitute
            
            // Station Assignment
            $table->foreignId('school_id')->constrained('schools')->onDelete('restrict'); // Assigned Station
            $table->unsignedBigInteger('deployed_school_id')->nullable(); // Deployed Station
            $table->foreign('deployed_school_id')->references('id')->on('schools')->onDelete('restrict');
            
            // Identification Numbers
            $table->string('gsis_no')->nullable();
            $table->string('pagibig_no')->nullable();
            $table->string('philhealth_no')->nullable();
            $table->string('sss_no')->nullable();
            $table->string('tin_no')->nullable();
            
            // Contact Details
            $table->string('contact_no')->nullable();
            $table->string('email_address')->nullable();
            $table->text('address')->nullable();
            
            // Status & Photo
            $table->boolean('is_active')->default(true);
            $table->string('photo_path')->nullable(); // To store the uploaded image file path
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};