<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IspInventory extends Model
{
    use SoftDeletes; 

    protected $table = 'isp_inventory';

    protected $guarded = [];

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