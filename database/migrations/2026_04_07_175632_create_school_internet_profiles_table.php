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
            $table->string('is_provider_available')->nullable();    // Yes / No
            $table->text('available_providers')->nullable();        // Checkbox
            $table->text('mobile_signals')->nullable();             // Checkbox
            $table->string('has_mobile_data')->nullable();          // Yes / No
            $table->string('mobile_data_quality')->nullable();      // Text

            // Subscriptions & Cost (Q6-Q9)
            $table->string('is_subscribed')->nullable();            // Yes / No
            $table->text('subscribed_providers')->nullable();       // Checkbox
            $table->integer('total_isps')->default(0);              // Number
            $table->decimal('total_cost', 10, 2)->default(0.00);    // Number
            
            // Purpose & Coverage (Q10-Q17)
            $table->text('subscription_purpose')->nullable();       // Checkbox
            $table->integer('rooms_admin_use')->default(0);         // Number
            $table->integer('rooms_classroom_use')->default(0);     // Number
            $table->integer('rooms_other_use')->default(0);         // Number
            $table->integer('rooms_covered')->default(0);           // Number
            $table->integer('access_points')->default(0);           // Number
            $table->text('insufficient_bandwidth_reason')->nullable();      // Text
            $table->text('coverage_areas')->nullable();             // Checkbox

            // DICT & Usage (Q18-Q21)
            $table->string('is_dict_recipient')->nullable();        // Yes / No
            $table->string('dict_rating')->nullable();              // Text
            $table->string('has_sufficient_bandwidth')->nullable(); // Yes / No
            $table->text('no_subscription_reason')->nullable();     // Text
            
            // Power Source (Q22-Q25)
            $table->string('has_electricity')->nullable();          // Yes / No
            $table->text('electricity_sources')->nullable();        // Checkbox
            $table->string('is_solar_powered')->nullable();         // Yes / No
            $table->string('frequent_brownouts')->nullable();       // Yes / No

            $table->timestamps();
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_internet_profile');
    }
};
