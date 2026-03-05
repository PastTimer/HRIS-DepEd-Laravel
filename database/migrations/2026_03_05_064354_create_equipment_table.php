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
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            
            // Identifiers
            $table->string('property_no')->nullable()->unique();
            $table->string('serial_number')->nullable();
            $table->string('qr_code')->nullable()->unique();
            
            // --- FOREIGN KEYS (The Brains of the Operation) ---
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('item_type_id')->nullable()->constrained('item_types')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('dcp_package_id')->nullable()->constrained('dcp_packages')->nullOnDelete();
            
            // Hardware Details
            $table->string('model')->nullable();
            $table->text('specifications')->nullable();
            $table->boolean('is_dcp')->default(false);
            $table->year('dcp_year')->nullable();
            
            // Acquisition & Finance
            $table->decimal('acquisition_cost', 15, 2)->nullable();
            $table->date('received_date')->nullable();
            $table->string('source_funds')->nullable(); // e.g., MOOE, SEF, Donation
            $table->string('mode_acquisition')->nullable();
            $table->string('supplier')->nullable();
            
            // --- PERSONNEL TRACKING ---
            $table->foreignId('accountable_officer_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->date('accountable_date')->nullable();
            $table->foreignId('custodian_id')->nullable()->constrained('employees')->nullOnDelete();
            
            // Status & Lifecycle
            $table->boolean('under_warranty')->default(false);
            $table->date('warranty_end_date')->nullable();
            $table->string('equipment_location')->nullable();
            $table->boolean('is_functional')->default(true);
            $table->string('condition')->nullable(); 
            $table->string('disposition_status')->nullable(); 
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
        Schema::dropIfExists('equipment');
    }
};
