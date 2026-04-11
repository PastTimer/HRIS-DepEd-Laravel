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

            // C1 - 1 to 9 [Personal Information]
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

            // C1 - 10 to 15 [Government Numbers]
            $table->string('umid_id_number')->nullable();
            $table->string('pagibig_number')->nullable();
            $table->string('philhealth_number')->nullable();
            $table->string('sss_number')->nullable();
            $table->string('philsys_number')->nullable()->unique();
            $table->string('tin_number')->nullable()->unique();
            $table->string('agency_employee_number')->nullable()->unique();

            // C1 - 16 [Citizenship]
            $table->string('citizenship_type', 20)->default('FILIPINO');
            $table->string('citizenship_mode', 20)->nullable();
            $table->string('dual_citizenship_country')->nullable();
            $table->text('dual_citizenship_details')->nullable();

            // C1 - 17 [Residential Address]
            $table->string('res_house_lot')->nullable();
            $table->string('res_street')->nullable();
            $table->string('res_subdivision')->nullable();
            $table->string('res_barangay')->nullable();
            $table->string('res_city')->nullable();
            $table->string('res_province')->nullable();
            $table->string('res_zipcode', 10)->nullable();

            // C1 - 18 [Permanent Address]
            $table->string('perm_house_lot')->nullable();
            $table->string('perm_street')->nullable();
            $table->string('perm_subdivision')->nullable();
            $table->string('perm_barangay')->nullable();
            $table->string('perm_city')->nullable();
            $table->string('perm_province')->nullable();
            $table->string('perm_zipcode', 10)->nullable();

            // Practical Single-Field Address for Initial Personnel Flow
            $table->text('residential_address')->nullable();

            // C1 - 19 to 21 [Contact]
            $table->string('telephone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email_address')->nullable();

            // C1 - 22 to 25 [Family]
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

            // C4 - 34 to 40 [Questions]
            $table->boolean('related_third_degree')->nullable();
            $table->boolean('related_fourth_degree')->nullable();
            $table->text('related_fourth_degree_details')->nullable();

            $table->boolean('admin_offense')->nullable();
            $table->text('admin_offense_details')->nullable();
            $table->boolean('criminal_case')->nullable();
            $table->date('criminal_case_date')->nullable();
            $table->text('criminal_case_status')->nullable();

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

        // C1 - II [Family - Children]
        Schema::create('pds_children', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnel')->onDelete('cascade');
            $table->foreignId('submission_id')->nullable()->constrained('pds_submissions')->onDelete('cascade');
            $table->string('child_name')->nullable();
            $table->date('birth_date')->nullable();
            $table->timestamps();
        });

        // C1 - III [Educational Background]
        Schema::create('pds_education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnel')->onDelete('cascade');
            $table->foreignId('submission_id')->nullable()->constrained('pds_submissions')->onDelete('cascade');
            $table->string('level', 50)->nullable();
            $table->string('school_name')->nullable();
            $table->string('degree')->nullable();
            $table->unsignedInteger('from_year')->nullable();
            $table->unsignedInteger('to_year')->nullable();
            $table->string('highest_level_units')->nullable();
            $table->unsignedInteger('year_graduated')->nullable();
            $table->string('honors')->nullable();
            $table->timestamps();
        });

        // C2 - IV [Eligibility]
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

        // C2 - V [Work Experience]
        Schema::create('pds_work_experience', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnel')->onDelete('cascade');
            $table->foreignId('submission_id')->nullable()->constrained('pds_submissions')->onDelete('cascade');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('position')->nullable();
            $table->string('company')->nullable();
            $table->string('appointment_status')->nullable();
            $table->boolean('is_government')->nullable();
            $table->timestamps();
        });

        // C3 - VI [Voluntary Work]
        Schema::create('pds_voluntary_work', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnel')->onDelete('cascade');
            $table->foreignId('submission_id')->nullable()->constrained('pds_submissions')->onDelete('cascade');
            $table->string('organization_name');
            $table->string('organization_address')->nullable();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->unsignedInteger('number_of_hours')->nullable();
            $table->string('position')->nullable();
            $table->timestamps();
        });

        // C3 - VII [Training]
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

            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending')->after('sponsor');
            $table->unsignedBigInteger('verified_by')->nullable()->after('verification_status');
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->text('rejection_reason')->nullable()->after('verified_at');
            $table->timestamps();
        });

        // C3 - VIII [Other Information - Skills]
        Schema::create('pds_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnel')->onDelete('cascade');
            $table->foreignId('submission_id')->nullable()->constrained('pds_submissions')->onDelete('cascade');
            $table->string('skill')->nullable();
            $table->timestamps();
        });

        // C3 - VIII [Other Information - Distinctions]
        Schema::create('pds_distinctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnel')->onDelete('cascade');
            $table->foreignId('submission_id')->nullable()->constrained('pds_submissions')->onDelete('cascade');
            $table->string('distinction')->nullable();
            $table->timestamps();
        });

        // C3 - VIII [Other Information - Memberships]
        Schema::create('pds_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnel')->onDelete('cascade');
            $table->foreignId('submission_id')->nullable()->constrained('pds_submissions')->onDelete('cascade');
            $table->string('membership')->nullable();
            $table->timestamps();
        });

        // C4 - 41 [References]
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
        Schema::dropIfExists('pds_memberships');
        Schema::dropIfExists('pds_distinctions');
        Schema::dropIfExists('pds_skills');
        Schema::dropIfExists('pds_references');
        Schema::dropIfExists('pds_training');
        Schema::dropIfExists('pds_work_experience');
        Schema::dropIfExists('pds_voluntary_work');
        Schema::dropIfExists('pds_eligibility');
        Schema::dropIfExists('pds_education');
        Schema::dropIfExists('pds_children');
        Schema::dropIfExists('pds_main');
        Schema::dropIfExists('pds_submissions');
    }
};
