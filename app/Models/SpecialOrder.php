<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialOrder extends Model
{
    protected $table = 'specialorder';

    protected $fillable = ['title', 'so_no', 'series_year', 'type', 'file_path', 'created_by'];

    public function employees()
    {
        return $this->belongsToMany(
            Employee::class, 
            'employee_specialorder', 
            'specialorder_id', 
            'employee_id'
        );
    }
}