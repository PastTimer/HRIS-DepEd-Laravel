<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PdsVoluntaryWork extends Model
{
    use HasFactory;

    protected $table = 'pds_voluntary_work';

    protected $fillable = [
        'personnel_id',
        'submission_id',
        'organization_name',
        'organization_address',
        'from_date',
        'to_date',
        'number_of_hours',
        'position',
    ];
}
