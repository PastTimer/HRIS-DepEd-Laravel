<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'username',
        'action_type',
        'module',
        'description',
        'ip_address',
        'user_agent'
    ];

    // Link back to the user who performed the action
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}