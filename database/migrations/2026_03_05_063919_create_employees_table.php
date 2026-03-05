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
        Schema::create('employees', function (Blueprint $table) {
            $table->id(); // Replaces erefid
            $table->string('employee_id')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('name_ext')->nullable(); 
            
            // --- FOREIGN KEYS ---
            // nullOnDelete() means if you delete a school, the employee isn't deleted, 
            // their school_id just becomes NULL.
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('deployed_school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained('designations')->nullOnDelete();
            
            $table->string('division')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('active_note')->nullable();
            $table->text('emp_notes')->nullable();
            $table->string('employee_type')->nullable();    
            
            // Personal Info
            $table->string('place_of_birth')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('civil_status')->nullable();
            $table->string('blood_type')->nullable();
            $table->string('gender')->nullable();
            
            // Government IDs
            $table->string('gsis_no')->nullable();
            $table->string('pagibig_no')->nullable();
            $table->string('philhealth_no')->nullable();
            $table->string('sss_no')->nullable();
            $table->string('tin_no')->nullable();
            
            // Contact Details
            $table->string('contact_no')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            
            // Employment Details
            $table->string('step')->nullable();
            $table->string('item_no')->nullable();
            $table->string('salary_grade')->nullable();
            $table->date('last_step_date')->nullable();
            
            // Separation Details
            $table->string('separation_type')->nullable();
            $table->date('separated_date')->nullable();
            $table->text('separated_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
