<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolInternetProfile extends Model
{
    protected $table = 'school_internet_profile';

    protected $fillable = [
        'school_id',
        'is_provider_available',
        'available_providers',
        'mobile_signals',
        'has_mobile_data',
        'mobile_data_quality',
        'is_subscribed',
        'subscribed_providers',
        'total_isps',
        'total_cost',
        'subscription_purpose',
        'rooms_admin_use',
        'rooms_classroom_use',
        'rooms_other_use',
        'rooms_covered',
        'access_points',
        'insufficient_bandwidth_reason',
        'coverage_areas',
        'is_dict_recipient',
        'dict_rating',
        'has_sufficient_bandwidth',
        'no_subscription_reason',
        'has_electricity',
        'electricity_sources',
        'is_solar_powered',
        'frequent_brownouts',
    ];

    protected $casts = [
        'total_isps' => 'integer',
        'total_cost' => 'decimal:2',
        'rooms_admin_use' => 'integer',
        'rooms_classroom_use' => 'integer',
        'rooms_other_use' => 'integer',
        'rooms_covered' => 'integer',
        'access_points' => 'integer',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }
}
