<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentMovement extends Model
{
    protected $fillable = [
        'equipment_id',
        'from_personnel_id',
        'to_personnel_id',
        'movement_date',
        'document_type',
        'document_number',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'movement_date' => 'date',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function fromPersonnel()
    {
        return $this->belongsTo(Personnel::class, 'from_personnel_id');
    }

    public function toPersonnel()
    {
        return $this->belongsTo(Personnel::class, 'to_personnel_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
