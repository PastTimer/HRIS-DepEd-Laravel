<?php

namespace App\Models;

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    protected $fillable = [
        'title',
        'type',
        'description',
    ];

    // Add this relationship: A Designation has many Employees
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}