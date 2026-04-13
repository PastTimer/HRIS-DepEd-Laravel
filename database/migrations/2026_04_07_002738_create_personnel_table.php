<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnel', function (Blueprint $table) {
            $table->id();

            // Operational personnel attributes (HRIZZ-aligned)
            $table->foreignId('position_id')->constrained('positions')->onDelete('restrict');
            $table->foreignId('assigned_school_id')->constrained('schools')->onDelete('restrict');
            $table->unsignedBigInteger('deployed_school_id')->nullable();
            $table->foreign('deployed_school_id')->references('id')->on('schools')->onDelete('restrict');

            $table->string('profile_photo')->nullable();
            $table->boolean('is_active')->default(true);

            $table->string('emp_id')->nullable()->unique();
            $table->string('item_number')->nullable()->unique();
            $table->integer('current_step')->nullable();
            $table->date('last_step_increment_date')->nullable();
            $table->string('salary_grade')->nullable();
            $table->string('employee_type'); // Regular, Contractual, Substitute

            $table->decimal('salary_actual', 15, 2)->nullable();
            $table->string('branch')->nullable();

            $table->decimal('system_generated_credits', 12, 2)->default(0)->after('branch');
            $table->decimal('manually_added_credits', 12, 2)->default(0)->after('system_generated_credits');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnel');
    }
};