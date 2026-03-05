<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id', 'name', 'address', 'district', 'is_active'
    ];

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
}