<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            // 1. Core Identification
            $table->string('property_no')->unique()->nullable();
            $table->string('old_property_no')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('qr_code')->nullable();

            // 2. Equipment Details
            $table->string('item'); // Device Type
            $table->string('unit')->nullable(); // uom
            $table->string('brand_manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->text('item_description')->nullable();
            $table->text('specifications')->nullable();

            // 3. DCP Information
            $table->boolean('is_dcp')->default(false);
            $table->string('dcp_package')->nullable();
            $table->string('dcp_year')->nullable();

            // 4. Financial Information
            $table->decimal('acquisition_cost', 15, 2)->nullable();
            $table->string('category')->nullable(); // High-value / Low-value
            $table->string('classification')->nullable();
            $table->integer('estimated_useful_life')->nullable();
            $table->string('gl_sl_code')->nullable();
            $table->string('uacs_code')->nullable();

            // 5. Acquisition Details
            $table->string('mode_acquisition')->nullable();
            $table->string('source_acquisition')->nullable();
            $table->string('donor')->nullable();
            $table->string('source_funds')->nullable();
            $table->string('allotment_class')->nullable();
            $table->date('received_date')->nullable();
            $table->string('pmp_reference')->nullable();

            // 6. Transaction & Initial Accountability
            $table->string('transaction_type')->nullable();
            $table->string('supporting_doc_type')->nullable();
            $table->string('supporting_doc_no')->nullable();
            $table->foreignId('accountable_officer_id')->nullable()->constrained('personnel')->onDelete('set null');
            $table->date('accountable_date')->nullable();
            $table->foreignId('custodian_id')->nullable()->constrained('personnel')->onDelete('set null');
            $table->date('custodian_date')->nullable();

            // 7. Supplier & Warranty
            $table->string('supplier')->nullable();
            $table->string('supplier_contact')->nullable();
            $table->boolean('under_warranty')->default(false);
            $table->date('warranty_end_date')->nullable();

            // 8. Status & Condition
            $table->string('equipment_location')->nullable();
            $table->boolean('is_functional')->default(true);
            $table->string('equipment_condition')->nullable();
            $table->string('disposition_status')->nullable();
            $table->text('remarks')->nullable();

            // 9. System Associations
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
        });

        // Equipment Movements table
        Schema::create('equipment_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->onDelete('cascade');
            $table->foreignId('from_personnel_id')->nullable()->constrained('personnel')->nullOnDelete();
            $table->foreignId('to_personnel_id')->nullable()->constrained('personnel')->nullOnDelete();
            $table->date('movement_date')->nullable();
            $table->string('document_type')->nullable();
            $table->string('document_number')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['equipment_id', 'movement_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_movements');
        Schema::dropIfExists('equipment');
    }
};