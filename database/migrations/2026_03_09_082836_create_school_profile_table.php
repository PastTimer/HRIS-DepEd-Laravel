<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('school_profile', function ($table) {
            $table->id('profile_id');
            $table->unsignedBigInteger('schoolid')->unique(); // Links to schools.id
            
            // General Information
            $table->string('governance_level')->nullable();
            $table->string('ro')->nullable();
            $table->string('sdo')->nullable();
            $table->string('school_district')->nullable();
            $table->string('school_name')->nullable();
            
            // Address Details
            $table->string('address_province')->nullable();
            $table->string('address_city')->nullable();
            $table->string('address_legislative')->nullable();
            $table->string('address_barangay')->nullable();
            $table->string('address_street')->nullable();
            $table->string('psgc')->nullable();
            
            // Contact Information
            $table->string('contact_mobile1')->nullable();
            $table->string('contact_mobile2')->nullable();
            $table->string('contact_landline')->nullable();
            
            // Key Personnel
            $table->string('head_name')->nullable();
            $table->string('head_position')->nullable();
            $table->string('head_email')->nullable();
            $table->string('head_mobile')->nullable();
            $table->string('admin_name')->nullable();
            $table->string('admin_position')->nullable();
            $table->string('admin_email')->nullable();
            $table->string('admin_mobile')->nullable();
            $table->string('network_admin_name')->nullable();
            
            // Geographic & Travel Data
            $table->string('coordinates_long')->nullable();
            $table->string('coordinates_lat')->nullable();
            $table->integer('travel_time_min')->nullable();
            $table->text('nearby_institutions')->nullable(); 
            $table->text('access_paths')->nullable();  
            
            // Notes
            $table->text('notes')->nullable();
            $table->text('recent_developments')->nullable();
            
            $table->string('updated_by')->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('schoolid')->references('id')->on('schools')->onDelete('cascade');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_profile');
    }
};
