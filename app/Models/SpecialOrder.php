<?php

namespace App\Models;

use App\Models\SoType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SpecialOrder extends Model
{
    protected $table = 'special_orders';

    protected $fillable = [
        'so_number',
        'series_year',
        'title',
        'description',
        'type_id',
        'status',
        'approved_by',
        'approved_at',
        'created_by',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(SoType::class, 'type_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function personnel(): BelongsToMany
    {
        return $this->belongsToMany(
            Personnel::class,
            'so_personnel',
            'special_order_id',
            'personnel_id'
        )->withPivot('units')->withTimestamps();
    }

    public function employees()
    {
        return $this->personnel();
    }
}