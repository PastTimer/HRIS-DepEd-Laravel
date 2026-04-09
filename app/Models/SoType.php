<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SoType extends Model
{
    protected $table = 'so_types';

    protected $fillable = [
        'name',
        'value',
    ];

    public function specialOrders(): HasMany
    {
        return $this->hasMany(SpecialOrder::class, 'type_id');
    }
}
