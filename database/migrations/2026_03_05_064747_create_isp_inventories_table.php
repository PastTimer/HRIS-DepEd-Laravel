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
        Schema::create('isp_inventories', function (Blueprint $table) {
            $table->id();
            
            // Link to the school
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            
            // ISP Details
            $table->string('provider'); 
            $table->string('account_no')->nullable();
            $table->string('internet_type')->nullable(); 
            $table->string('plan_speed')->nullable();
            $table->decimal('monthly_mrc', 10, 2)->nullable();
            
            // Status & Config
            $table->string('status')->default('Active'); 
            $table->string('ip_type')->default('Dynamic');
            $table->string('public_ip')->nullable();
            $table->date('installation_date')->nullable();
            $table->date('contract_end_date')->nullable();
            
            // Source & Funding
            $table->string('fund_source')->nullable();
            $table->string('acquisition_mode')->nullable();
            $table->string('purpose')->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();
            $table->softDeletes();
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
