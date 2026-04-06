<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id', 'name', 'district_id', 'governance_level', 'ro', 'sdo',
        'address_street', 'address_barangay', 'address_city', 'address_province', 'psgc',
        'coordinates_lat', 'coordinates_long', 'travel_time_min', 'access_paths',
        'contact_mobile1', 'contact_mobile2', 'contact_landline',
        'head_name', 'head_position', 'head_email',
        'admin_name', 'admin_mobile',
        'nearby_institutions', 'notes', 'is_active'
    ];
    // School belongs to a District
    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    // 1. A School has many Employees
    public function employees()
    {
        return $this->hasMany(Employee::class, 'school_id');
    }

    // 2. A School has many pieces of Equipment
    public function equipment()
    {
        return $this->hasMany(Equipment::class, 'school_id');
    }

    // 3. A School has ONE Internet Profile
    public function internetProfile()
    {
        return $this->hasOne(SchoolInternetProfile::class, 'school_id');
    }

    public function isps()
    {
        return $this->hasMany(IspInventory::class, 'school_id');
    }

}