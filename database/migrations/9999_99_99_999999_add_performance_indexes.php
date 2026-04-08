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
        // Activity Logs
        if (!\Illuminate\Support\Facades\DB::select("PRAGMA index_list('activity_logs')") ||
            !collect(\Illuminate\Support\Facades\DB::select("PRAGMA index_list('activity_logs')"))->pluck('name')->contains('activity_logs_user_id_index')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->index('user_id', 'activity_logs_user_id_index');
            });
        }
        if (!\Illuminate\Support\Facades\DB::select("PRAGMA index_list('activity_logs')") ||
            !collect(\Illuminate\Support\Facades\DB::select("PRAGMA index_list('activity_logs')"))->pluck('name')->contains('activity_logs_created_at_index')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->index('created_at', 'activity_logs_created_at_index');
            });
        }

        // Personnel
        $personnelIndexes = [
            'personnel_assigned_school_id_index' => 'assigned_school_id',
            'personnel_deployed_school_id_index' => 'deployed_school_id',
            'personnel_position_id_index' => 'position_id',
            'personnel_last_name_index' => 'last_name',
        ];
        foreach ($personnelIndexes as $idxName => $col) {
            if (!collect(\Illuminate\Support\Facades\DB::select("PRAGMA index_list('personnel')"))->pluck('name')->contains($idxName)) {
                Schema::table('personnel', function (Blueprint $table) use ($col, $idxName) {
                    $table->index($col, $idxName);
                });
            }
        }

        // Schools
        $schoolsIndexes = [
            'schools_district_id_index' => 'district_id',
            'schools_school_id_index' => 'school_id',
            'schools_name_index' => 'name',
        ];
        foreach ($schoolsIndexes as $idxName => $col) {
            if (!collect(\Illuminate\Support\Facades\DB::select("PRAGMA index_list('schools')"))->pluck('name')->contains($idxName)) {
                Schema::table('schools', function (Blueprint $table) use ($col, $idxName) {
                    $table->index($col, $idxName);
                });
            }
        }

        // Service Records
        $serviceRecordIndexes = [
            'service_records_personnel_id_index' => 'personnel_id',
            'service_records_position_id_index' => 'position_id',
            'service_records_school_id_index' => 'school_id',
            'service_records_date_from_index' => 'date_from',
            'service_records_date_to_index' => 'date_to',
        ];
        foreach ($serviceRecordIndexes as $idxName => $col) {
            if (!collect(\Illuminate\Support\Facades\DB::select("PRAGMA index_list('service_records')"))->pluck('name')->contains($idxName)) {
                Schema::table('service_records', function (Blueprint $table) use ($col, $idxName) {
                    $table->index($col, $idxName);
                });
            }
        }

        // Equipment
        $equipmentIndexes = [
            'equipment_school_id_index' => 'school_id',
            'equipment_accountable_officer_id_index' => 'accountable_officer_id',
            'equipment_custodian_id_index' => 'custodian_id',
        ];
        foreach ($equipmentIndexes as $idxName => $col) {
            if (!collect(\Illuminate\Support\Facades\DB::select("PRAGMA index_list('equipment')"))->pluck('name')->contains($idxName)) {
                Schema::table('equipment', function (Blueprint $table) use ($col, $idxName) {
                    $table->index($col, $idxName);
                });
            }
        }

        // ISP Inventory
        $ispInventoryIndexes = [
            'isp_inventory_school_id_index' => 'school_id',
            'isp_inventory_provider_index' => 'provider',
        ];
        foreach ($ispInventoryIndexes as $idxName => $col) {
            if (!collect(\Illuminate\Support\Facades\DB::select("PRAGMA index_list('isp_inventory')"))->pluck('name')->contains($idxName)) {
                Schema::table('isp_inventory', function (Blueprint $table) use ($col, $idxName) {
                    $table->index($col, $idxName);
                });
            }
        }

        // ISP Speedtests
        $ispSpeedtestsIndexes = [
            'isp_speedtests_isp_id_index' => 'isp_id',
            'isp_speedtests_test_date_index' => 'test_date',
        ];
        foreach ($ispSpeedtestsIndexes as $idxName => $col) {
            if (!collect(\Illuminate\Support\Facades\DB::select("PRAGMA index_list('isp_speedtests')"))->pluck('name')->contains($idxName)) {
                Schema::table('isp_speedtests', function (Blueprint $table) use ($col, $idxName) {
                    $table->index($col, $idxName);
                });
            }
        }

        // PDS Main (if exists)
        if (Schema::hasTable('pds_main')) {
            if (!collect(\Illuminate\Support\Facades\DB::select("PRAGMA index_list('pds_main')"))->pluck('name')->contains('pds_main_personnel_id_index')) {
                Schema::table('pds_main', function (Blueprint $table) {
                    $table->index('personnel_id', 'pds_main_personnel_id_index');
                });
            }
        }

        // Pivot tables
        $pivotTables = [
            'personnel_training' => ['personnel_id', 'training_id'],
            'personnel_specialorder' => ['personnel_id', 'specialorder_id'],
        ];
        foreach ($pivotTables as $tableName => $columns) {
            if (Schema::hasTable($tableName)) {
                foreach ($columns as $col) {
                    $idxName = $tableName . '_' . $col . '_index';
                    if (!collect(\Illuminate\Support\Facades\DB::select("PRAGMA index_list('".$tableName."')"))->pluck('name')->contains($idxName)) {
                        Schema::table($tableName, function (Blueprint $table) use ($col, $idxName) {
                            $table->index($col, $idxName);
                        });
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes
        Schema::table('service_records', function (Blueprint $table) {
            $table->dropIndex('service_records_personnel_id_index');
            $table->dropIndex('service_records_position_id_index');
            $table->dropIndex('service_records_school_id_index');
            $table->dropIndex('service_records_date_from_index');
            $table->dropIndex('service_records_date_to_index');
        });
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('activity_logs_user_id_index');
            $table->dropIndex('activity_logs_created_at_index');
        });
        Schema::table('personnel', function (Blueprint $table) {
            $table->dropIndex('personnel_assigned_school_id_index');
            $table->dropIndex('personnel_deployed_school_id_index');
            $table->dropIndex('personnel_position_id_index');
            $table->dropIndex('personnel_last_name_index');
        });
        Schema::table('schools', function (Blueprint $table) {
            $table->dropIndex('schools_district_id_index');
            $table->dropIndex('schools_school_id_index');
            $table->dropIndex('schools_name_index');
        });
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropIndex('equipment_school_id_index');
            $table->dropIndex('equipment_accountable_officer_id_index');
            $table->dropIndex('equipment_custodian_id_index');
        });
        Schema::table('isp_inventory', function (Blueprint $table) {
            $table->dropIndex('isp_inventory_school_id_index');
            $table->dropIndex('isp_inventory_provider_index');
        });
        Schema::table('isp_speedtests', function (Blueprint $table) {
            $table->dropIndex('isp_speedtests_isp_id_index');
            $table->dropIndex('isp_speedtests_test_date_index');
        });
        if (Schema::hasTable('pds_main')) {
            Schema::table('pds_main', function (Blueprint $table) {
                $table->dropIndex('pds_main_personnel_id_index');
            });
        }
        $pivotTables = [
            'personnel_training' => ['personnel_id', 'training_id'],
            'personnel_specialorder' => ['personnel_id', 'specialorder_id'],
        ];
        foreach ($pivotTables as $tableName => $columns) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($columns, $tableName) {
                    foreach ($columns as $col) {
                        $table->dropIndex($tableName . '_' . $col . '_index');
                    }
                });
            }
        }
    }
};
