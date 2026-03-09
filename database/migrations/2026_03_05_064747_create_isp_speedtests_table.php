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
        Schema::create('isp_speedtests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('isp_id'); 
            
            $table->dateTime('test_date');
            $table->double('download_mbps', 8, 2);
            $table->double('upload_mbps', 8, 2);
            $table->integer('ping_ms')->nullable();
            $table->string('tested_by')->nullable();
            $table->text('remarks_speed')->nullable();
            $table->timestamps();
            $table->foreign('isp_id')
                ->references('id')
                ->on('isp_inventory')
                ->onDelete('cascade');
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
