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
        Schema::create('school_internet_profiles', function (Blueprint $table) {
            $table->id();
            
            // The Foreign Key (Unique because it's a 1-to-1 relationship)
            $table->foreignId('school_id')->unique()->constrained('schools')->cascadeOnDelete();
            
            // Connectivity Status
            $table->boolean('is_provider_available')->default(false);
            $table->text('available_providers')->nullable();
            $table->boolean('has_mobile_data')->default(false);
            $table->string('mobile_data_quality')->nullable();
            
            // Power & Infrastructure
            $table->boolean('has_electricity')->default(false);
            $table->text('electricity_sources')->nullable();
            $table->boolean('is_solar_powered')->default(false);
            $table->boolean('frequent_brownouts')->default(false);
            
            // Coverage
            $table->integer('rooms_admin_use')->default(0);
            $table->integer('rooms_classroom_use')->default(0);
            $table->integer('access_points')->default(0);
            
            // DICT Details
            $table->boolean('is_dict_recipient')->default(false);
            $table->string('dict_rating')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_internet_profiles');
    }
};
