<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnel')->onDelete('cascade');
            $table->foreignId('position_id')->constrained('positions')->onDelete('restrict');
            $table->foreignId('school_id')->constrained('schools')->onDelete('restrict');
            $table->date('date_from');
            $table->date('date_to')->nullable();
            $table->string('status');
            $table->decimal('salary', 15, 2)->nullable();
            $table->string('branch')->nullable();
            $table->string('lv_abs_wo_pay')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_records');
    }
};
