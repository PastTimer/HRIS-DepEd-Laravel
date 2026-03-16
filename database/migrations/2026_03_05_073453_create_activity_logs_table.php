<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('username')->nullable();
            $table->string('user_role')->nullable();
            $table->string('access_level')->nullable(); 
            $table->string('action_type');
            $table->string('module'); 
            $table->text('description');
            $table->json('changes')->nullable(); 
            $table->string('ip_address')->nullable();
            $table->timestamps(); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_logs');
    }
};