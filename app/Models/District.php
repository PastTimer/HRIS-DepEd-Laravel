<?php

namespace App\Models;

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $fillable = [
        'name', 'is_active', 'created_at', 'updated_at'
    ];

    public function schools()
    {
        return $this->hasMany(School::class, 'district_id');
    }
}