<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth; 

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'username', 'user_role', 'access_level',
        'action_type', 'module', 'description', 'changes', 'ip_address'
    ];

    protected $casts = [
        'changes' => 'array', 
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Helper function to quickly record an audit log
     */
    public static function log($action_type, $module, $description, $changes = null)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $userRole = $user->getRoleNames()->first();
            $accessLevel = $user->school?->name
                ?? $user->personnel?->emp_id
                ?? null;
            
            self::create([
                'user_id'      => $user->id,
                'username'     => $user->username,
                'user_role'    => $userRole,
                'access_level' => $accessLevel,
                'action_type'  => strtoupper($action_type),
                'module'       => $module,
                'description'  => $description,
                'changes'      => $changes,
                'ip_address'   => request()->ip(), 
            ]);
        }
    }
}