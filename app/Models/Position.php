<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = [
        'title',
        'type',
        'description',
    ];

    // A Position has many Employees
    public function employees()
    {
        return $this->hasMany(Employee::class, 'position_id');
    }
}