<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IspInventory extends Model
{
    use SoftDeletes; 

    protected $table = 'isp_inventory';

    protected $fillable = [
        'school_id',
        'provider',
        'account_no',
        'internet_type',
        'subscription_type',
        'status',
        'purpose',
        'acquisition_mode',
        'donor',
        'fund_source',
        'monthly_mrc',
        'plan_speed',
        'min_speed',
        'area_coverage',
        'package_inclusion',
        'installation_date',
        'contract_end_date',
        'ip_type',
        'public_ip',
        'remarks',
        'access_points_count',
        'access_points_loc',
        'admin_rooms_covered',
        'classrooms_covered',
        'admin_connectivity_rating',
        'classroom_connectivity_rating',
        'signal_quality',
        'isp_service_rating',
        'active_isp_counter',
        'active_custom_counter_2',
        'active_custom_counter_3',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'monthly_mrc' => 'decimal:2',
        'installation_date' => 'date',
        'contract_end_date' => 'date',
        'access_points_count' => 'integer',
        'admin_rooms_covered' => 'integer',
        'classrooms_covered' => 'integer',
        'admin_connectivity_rating' => 'integer',
        'classroom_connectivity_rating' => 'integer',
        'isp_service_rating' => 'integer',
        'active_isp_counter' => 'integer',
        'active_custom_counter_2' => 'integer',
        'active_custom_counter_3' => 'integer',
    ];

    /**
     * Link to the speed tests table
     */
    public function speedTests()
    {
        return $this->hasMany(IspSpeedtest::class, 'isp_id')->orderBy('test_date', 'desc');
    }
    
    /**
     * Link back to the school
     */
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }
}