<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IspInventory extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    public function school() { 
        return $this->belongsTo(School::class); 
    }
    
    public function speedtests() { 
        return $this->hasMany(IspSpeedtest::class, 'isp_inventory_id'); 
    }
}