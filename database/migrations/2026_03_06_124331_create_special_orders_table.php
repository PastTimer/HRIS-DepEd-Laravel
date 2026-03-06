<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. The Master Special Orders Table
        Schema::create('specialorder', function (Blueprint $table) {
            $table->id();
            $table->text('title'); 
            $table->string('so_no'); 
            $table->string('series_year'); 
            $table->string('type'); 
            $table->string('file_path')->nullable(); 
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 2. The Pivot Table 
        Schema::create('employee_specialorder', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specialorder_id')->constrained('specialorder')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_specialorder');
        Schema::dropIfExists('specialorder');
    }
};