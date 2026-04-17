<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('division_id');
            $table->unsignedBigInteger('cluster_id')->nullable();
            $table->timestamps();

            $table->foreign('division_id')->references('id')->on('divisions')->onDelete('cascade');
            $table->foreign('cluster_id')->references('id')->on('clusters')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};