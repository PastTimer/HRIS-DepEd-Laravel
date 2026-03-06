<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialOrder extends Model
{
    protected $fillable = [
        'title',
        'so_no',
        'series_year',
        'type',
        'file_path',
        'created_by'
    ];

    // Many-to-Many Relationship with Employees
    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_special_order', 'special_order_id', 'employee_id')->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}