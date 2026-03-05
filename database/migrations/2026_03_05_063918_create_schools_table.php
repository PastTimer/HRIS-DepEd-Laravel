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
        Schema::create('schools', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key 
            $table->string('school_id')->unique(); // The DepEd School ID
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('district')->nullable();
            $table->boolean('is_active')->default(true); // Replaces string 'sactive'
            
            $table->timestamps();
            $table->softDeletes(); 
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
