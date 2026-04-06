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
        Schema::create('schools', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key 
            $table->string('school_id')->unique(); // The DepEd School ID
            $table->string('name');
            $table->unsignedBigInteger('district_id')->nullable();

            // Profile fields
            $table->string('governance_level')->nullable();
            $table->string('ro')->nullable();
            $table->string('sdo')->nullable();
            $table->string('address_street')->nullable();
            $table->string('address_barangay')->nullable();
            $table->string('address_city')->nullable();
            $table->string('address_province')->nullable();
            $table->string('psgc')->nullable();
            $table->string('coordinates_lat')->nullable();
            $table->string('coordinates_long')->nullable();
            $table->integer('travel_time_min')->nullable();
            $table->string('access_paths')->nullable();
            $table->string('contact_mobile1')->nullable();
            $table->string('contact_mobile2')->nullable();
            $table->string('contact_landline')->nullable();
            $table->string('head_name')->nullable();
            $table->string('head_position')->nullable();
            $table->string('head_email')->nullable();
            $table->string('admin_name')->nullable();
            $table->string('admin_mobile')->nullable();
            $table->string('nearby_institutions')->nullable();
            $table->text('notes')->nullable();

            $table->tinyInteger('is_active')->default(1); // TINYINT(1) for MySQL/SQLite compatibility
            $table->timestamps();
            $table->softDeletes(); 

            $table->foreign('district_id')->references('id')->on('districts')->nullOnDelete();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
