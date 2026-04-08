<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'personnel_id',
        'position_id',
        'school_id',
        'date_from',
        'date_to',
        'status',
        'salary',
        'branch',
        'lv_abs_wo_pay',
    ];

    public function personnel()
    {
        return $this->belongsTo(Personnel::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }
}
