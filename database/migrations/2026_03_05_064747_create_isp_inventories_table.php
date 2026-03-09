<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('isp_inventory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->unique();
            
            // Core Connection Details
            $table->string('provider'); // PLDT, Globe, Starlink, etc.
            $table->string('account_no')->nullable();
            $table->string('internet_type')->nullable(); // Fiber, Satellite, LTE
            $table->string('subscription_type')->default('Postpaid'); // Postpaid/Prepaid
            $table->string('status')->default('Active'); // Active, Inactive, Pending
            
            // Financial & Acquisition
            $table->string('purpose')->nullable();
            $table->string('acquisition_mode')->nullable();
            $table->string('donor')->nullable();
            $table->string('fund_source')->nullable();
            $table->decimal('monthly_mrc', 12, 2)->default(0.00);
            
            // Performance Specs
            $table->string('plan_speed')->nullable(); // Max speed in Mbps
            $table->string('min_speed')->nullable();  // Minimum guaranteed speed
            $table->string('area_coverage')->nullable(); // Whole School, Admin, etc.
            $table->text('package_inclusion')->nullable();
            
            // Technical Details
            $table->date('installation_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->string('ip_type')->default('Dynamic'); // Static or Dynamic
            $table->string('public_ip')->nullable();
            $table->text('remarks')->nullable();
            
            // Infrastructure & Ratings (v2 Fields)
            $table->integer('access_points_count')->default(0);
            $table->string('access_points_loc')->nullable();
            $table->integer('admin_rooms_covered')->default(0);
            $table->integer('classrooms_covered')->default(0);
            $table->integer('admin_connectivity_rating')->nullable(); // 1-5 Scale
            $table->integer('classroom_connectivity_rating')->nullable(); // 1-5 Scale
            $table->string('signal_quality')->nullable();
            $table->integer('isp_service_rating')->nullable(); // 1-5 Scale
            
            // Custom Counters (From Legacy v2)
            $table->integer('active_isp_counter')->default(0);
            $table->integer('active_custom_counter_2')->default(0); // Admin Counter
            $table->integer('active_custom_counter_3')->default(0); // Classroom Counter
            
            // Metadata
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes(); // For safe record management
            $table->timestamps(); 
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('isp_inventory');
    }
};