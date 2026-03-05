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
        Schema::create('isp_speedtests', function (Blueprint $table) {
            $table->id();
            
            // Link to the specific ISP subscription
            $table->foreignId('isp_inventory_id')->constrained('isp_inventories')->cascadeOnDelete();
            
            // Test Results
            $table->decimal('download_mbps', 10, 2)->nullable();
            $table->decimal('upload_mbps', 10, 2)->nullable();
            $table->integer('ping_ms')->nullable();
            
            // Metadata
            $table->dateTime('test_date')->useCurrent();
            $table->string('tested_by')->nullable();
            $table->text('remarks_speed')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('isp_speedtests');
    }
};
