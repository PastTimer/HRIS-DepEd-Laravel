<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = []; // This is a shortcut that lets us fill any column safely

    // 1. An Employee belongs to a School
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    // 2. An Employee belongs to a Designation (Position)
    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    // 3. Optional: Get the equipment this employee is accountable for
    public function accountableEquipment()
    {
        return $this->hasMany(Equipment::class, 'accountable_officer_id');
    }
}