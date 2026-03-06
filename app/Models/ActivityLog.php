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

    /**
     * Helper function to quickly record an audit log
     */
    public static function log($action_type, $module, $description, $changes = null)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            self::create([
                'user_id'      => $user->id,
                'username'     => $user->username,
                'user_role'    => $user->role,
                'access_level' => $user->access_level,
                'action_type'  => strtoupper($action_type),
                'module'       => $module,
                'description'  => $description,
                'changes'      => $changes,
                'ip_address'   => request()->ip(), 
            ]);
        }
    }
}