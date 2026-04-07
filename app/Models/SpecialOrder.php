<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialOrder extends Model
{
    protected $table = 'specialorder';

    protected $fillable = ['title', 'so_no', 'series_year', 'type', 'file_path', 'created_by'];

    public function personnel()
    {
        return $this->belongsToMany(
            Personnel::class,
            'personnel_specialorder',
            'specialorder_id',
            'personnel_id'
        );
    }

    public function employees()
    {
        return $this->personnel();
    }
}