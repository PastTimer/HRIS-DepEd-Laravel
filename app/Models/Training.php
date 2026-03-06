<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    protected $fillable = ['trefid', 'title', 'hours', 'date_from', 'date_to', 'file_path', 'status', 'created_by'];

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_training');
    }
}
