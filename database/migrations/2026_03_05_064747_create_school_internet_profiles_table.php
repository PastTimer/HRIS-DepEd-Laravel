<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('school_internet_profile', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->unique();
            
            // Availability & Signals (Q1-Q5)
            $table->string('is_provider_available')->nullable();
            $table->text('available_providers')->nullable(); // Stores imploded checkbox values
            $table->text('mobile_signals')->nullable();
            $table->string('has_mobile_data')->nullable();
            $table->string('mobile_data_quality')->nullable();

            // Subscriptions & Cost (Q6-Q9)
            $table->text('subscribed_providers')->nullable();
            $table->integer('total_isps')->default(0);
            $table->decimal('total_cost', 10, 2)->default(0.00);
            
            // Purpose & Coverage (Q10-Q17)
            $table->text('subscription_purpose')->nullable();
            $table->integer('rooms_admin_use')->default(0);
            $table->integer('rooms_classroom_use')->default(0);
            $table->integer('rooms_other_use')->default(0);
            $table->integer('rooms_covered')->default(0);
            $table->integer('access_points')->default(0);
            $table->text('insufficient_bandwidth_reason')->nullable();
            $table->text('coverage_areas')->nullable();

            // DICT & Usage (Q18-Q21)
            $table->string('is_dict_recipient')->nullable();
            $table->string('dict_rating')->nullable();
            $table->string('has_sufficient_bandwidth')->nullable();
            $table->text('no_subscription_reason')->nullable();
            
            // Power Source (Q22-Q25)
            $table->string('has_electricity')->nullable();
            $table->text('electricity_sources')->nullable();
            $table->string('is_solar_powered')->nullable();
            $table->string('frequent_brownouts')->nullable();

            $table->timestamps();
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('isp_inventories');
    }
};
