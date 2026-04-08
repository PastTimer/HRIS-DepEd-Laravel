<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PdsSkill extends Model
{
    use HasFactory;

    protected $fillable = [
        'personnel_id',
        'submission_id',
        'skill',
    ];

    public function personnel()
    {
        return $this->belongsTo(Personnel::class);
    }

    public function submission()
    {
        return $this->belongsTo(PdsSubmission::class, 'submission_id');
    }
}
