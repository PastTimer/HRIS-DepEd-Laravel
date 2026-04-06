<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add link columns only if they are not already present.
            if (!Schema::hasColumn('users', 'school_id')) {
                $table->foreignId('school_id')->nullable()->after('office')->constrained('schools')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'personnel_id')) {
                $table->foreignId('personnel_id')->nullable()->after('school_id')->constrained('personnel')->nullOnDelete();
            }

            // Clean legacy columns if they still exist from older schema.
            if (Schema::hasColumn('users', 'first_name')) {
                $table->dropColumn('first_name');
            }
            if (Schema::hasColumn('users', 'last_name')) {
                $table->dropColumn('last_name');
            }
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
            if (Schema::hasColumn('users', 'access_level')) {
                $table->dropColumn('access_level');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'personnel_id')) {
                $table->dropConstrainedForeignId('personnel_id');
            }
            if (Schema::hasColumn('users', 'school_id')) {
                $table->dropConstrainedForeignId('school_id');
            }
        });
    }
};
