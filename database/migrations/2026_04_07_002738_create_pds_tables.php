<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pds_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnel')->onDelete('cascade');
            $table->unsignedInteger('version_number')->default(1);

            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('status')->nullable(); // SUBMITTED, APPROVED, REJECTED
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_remarks')->nullable();

            $table->timestamps();
        });

        Schema::create('pds_main', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnel')->onDelete('cascade');
            $table->foreignId('submission_id')->nullable()->constrained('pds_submissions')->onDelete('cascade');

            // Personal information
            $table->string('last_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('extension_name', 20)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('birth_place')->nullable();
            $table->string('birth_sex', 10)->nullable();
            $table->string('civil_status', 50)->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->string('blood_type', 5)->nullable();

            // Government numbers
            $table->string('umid_id_number')->nullable();
            $table->string('pagibig_number')->nullable();
            $table->string('philhealth_number')->nullable();
            $table->string('sss_number')->nullable();
            $table->string('philsys_number')->nullable()->unique();
            $table->string('tin_number')->nullable()->unique();
            $table->string('agency_employee_number')->nullable()->unique();

            // Citizenship
            $table->string('citizenship_type', 20)->default('FILIPINO');
            $table->string('citizenship_mode', 20)->nullable();
            $table->string('dual_citizenship_country')->nullable();
            $table->text('dual_citizenship_details')->nullable();

            // Residence split fields
            $table->string('res_house_lot')->nullable();
            $table->string('res_street')->nullable();
            $table->string('res_subdivision')->nullable();
            $table->string('res_barangay')->nullable();
            $table->string('res_city')->nullable();
            $table->string('res_province')->nullable();
            $table->string('res_zipcode', 10)->nullable();

            $table->string('perm_house_lot')->nullable();
            $table->string('perm_street')->nullable();
            $table->string('perm_subdivision')->nullable();
            $table->string('perm_barangay')->nullable();
            $table->string('perm_city')->nullable();
            $table->string('perm_province')->nullable();
            $table->string('perm_zipcode', 10)->nullable();

            // Practical single-field address for initial personnel flow
            $table->text('residential_address')->nullable();

            // Contact
            $table->string('telephone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email_address')->nullable();

            // Family
            $table->string('spouse_last_name')->nullable();
            $table->string('spouse_first_name')->nullable();
            $table->string('spouse_middle_name')->nullable();
            $table->string('spouse_extension_name', 20)->nullable();
            $table->string('spouse_occupation')->nullable();
            $table->string('spouse_employer')->nullable();
            $table->text('employer_address')->nullable();
            $table->string('spouse_telephone')->nullable();

            $table->string('father_last_name')->nullable();
            $table->string('father_first_name')->nullable();
            $table->string('father_middle_name')->nullable();
            $table->string('father_extension_name', 20)->nullable();

            $table->string('mother_last_name')->nullable();
            $table->string('mother_first_name')->nullable();
            $table->string('mother_middle_name')->nullable();

            // Questions
            $table->boolean('related_third_degree')->nullable();
            $table->boolean('related_fourth_degree')->nullable();
            $table->text('related_fourth_degree_details')->nullable();

            $table->boolean('admin_offense')->nullable();
            $table->text('admin_offense_details')->nullable();
            $table->boolean('criminal_case')->nullable();
            $table->text('criminal_case_details')->nullable();

            $table->boolean('convicted')->nullable();
            $table->text('convicted_details')->nullable();

            $table->boolean('separated_service')->nullable();
            $table->text('separated_service_details')->nullable();

            $table->boolean('election_candidate')->nullable();
            $table->text('election_candidate_details')->nullable();
            $table->boolean('election_resigned')->nullable();
            $table->text('election_resigned_details')->nullable();

            $table->boolean('immigrant')->nullable();
            $table->text('immigrant_details')->nullable();

            $table->boolean('indigenous')->nullable();
            $table->text('indigenous_details')->nullable();
            $table->boolean('pwd')->nullable();
            $table->text('pwd_details')->nullable();
            $table->boolean('solo_parent')->nullable();
            $table->text('solo_parent_details')->nullable();

            // Government ID issuance
            $table->string('issued_id')->nullable();
            $table->string('id_number')->nullable();
            $table->date('issue_date')->nullable();
            $table->string('issue_place')->nullable();

            $table->timestamps();

            $table->index('personnel_id');
            $table->index('submission_id');
        });

        Schema::create('pds_children', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnel')->onDelete('cascade');
            $table->foreignId('submission_id')->nullable()->constrained('pds_submissions')->onDelete('cascade');
            $table->string('child_name')->nullable();
            $table->date('birth_date')->nullable();
            $table->timestamps();
        });

        Schema::create('pds_education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnel')->onDelete('cascade');
            $table->foreignId('submission_id')->nullable()->constrained('pds_submissions')->onDelete('cascade');
            $table->string('level', 50)->nullable();
            $table->string('school_name')->nullable();
            $table->string('degree')->nullable();
            $table->unsignedInteger('from_year')->nullable();
            $table->unsignedInteger('to_year')->nullable();
            $table->string('honors')->nullable();
            $table->timestamps();
        });

        Schema::create('pds_eligibility', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnel')->onDelete('cascade');
            $table->foreignId('submission_id')->nullable()->constrained('pds_submissions')->onDelete('cascade');
            $table->string('eligibility')->nullable();
            $table->string('rating')->nullable();
            $table->date('exam_date')->nullable();
            $table->string('exam_place')->nullable();
            $table->string('license_number')->nullable();
            $table->dateTime('license_valid_until')->nullable();
            $table->timestamps();
        });

        Schema::create('pds_work_experience', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnel')->onDelete('cascade');
            $table->foreignId('submission_id')->nullable()->constrained('pds_submissions')->onDelete('cascade');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('position')->nullable();
            $table->string('company')->nullable();
            $table->string('appointment_status')->nullable();
            $table->timestamps();
        });

        Schema::create('pds_training', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnel')->onDelete('cascade');
            $table->foreignId('submission_id')->nullable()->constrained('pds_submissions')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedInteger('hours')->nullable();
            $table->string('type', 30)->nullable();
            $table->string('sponsor')->nullable();
            $table->timestamps();
        });

        Schema::create('pds_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnel')->onDelete('cascade');
            $table->foreignId('submission_id')->nullable()->constrained('pds_submissions')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->text('address')->nullable();
            $table->string('contact')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pds_references');
        Schema::dropIfExists('pds_training');
        Schema::dropIfExists('pds_work_experience');
        Schema::dropIfExists('pds_eligibility');
        Schema::dropIfExists('pds_education');
        Schema::dropIfExists('pds_children');
        Schema::dropIfExists('pds_main');
        Schema::dropIfExists('pds_submissions');
    }
};
