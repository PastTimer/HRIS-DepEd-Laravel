<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('so_personnel');
        Schema::dropIfExists('special_orders');
        Schema::dropIfExists('so_types');
        Schema::enableForeignKeyConstraints();

        Schema::create('so_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('value', 8, 2)->default(1);
            $table->timestamps();
        });

        DB::table('so_types')->insert([
            ['name' => 'Vacation Leave', 'value' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sick Leave', 'value' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Other / Custom', 'value' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::create('special_orders', function (Blueprint $table) {
            $table->id();
            $table->string('so_number');
            $table->string('series_year', 4);
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('type_id')->constrained('so_types')->cascadeOnUpdate()->restrictOnDelete();
            $table->enum('status', ['Approved', 'Rejected', 'Pending'])->default('Pending');
            $table->text('rejection_reason')->nullable()->after('status');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['so_number', 'series_year']);
            $table->index('status');
            $table->index('created_at');
        });

        Schema::create('so_personnel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('special_order_id')->constrained('special_orders')->cascadeOnDelete();
            $table->foreignId('personnel_id')->constrained('personnel')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['special_order_id', 'personnel_id']);
            $table->index('personnel_id');
            $table->index('special_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('so_personnel');
        Schema::dropIfExists('special_orders');
        Schema::dropIfExists('so_types');
    }
};
