<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolProfile extends Model
{
    protected $table = 'school_profile'; 
    
    // Explicitly set the primary key if it's not 'id'
    protected $primaryKey = 'profile_id';

    // Allow all fields to be mass-assigned
    protected $guarded = [];

    public function school()
    {
        return $this->belongsTo(School::class, 'schoolid');
    }
}