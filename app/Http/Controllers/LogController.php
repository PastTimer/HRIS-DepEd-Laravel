<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search');
        
        // Base query setup
        $query = ActivityLog::query();

        // Security Check: Restrict 'school' users to their own logs
        if ($user && $user->role === 'school') {
            $query->where('access_level', $user->access_level);
        }

        // Apply Deep Search Filter
        $query->when($search, function ($q) use ($search) {
            $q->where(function($subQuery) use ($search) {
                $subQuery->where('action_type', 'like', "%{$search}%")
                        ->orWhere('module', 'like', "%{$search}%") 
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhereHas('user', function($userQuery) use ($search) {
                            $userQuery->where('username', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                        });
            });
        });

        $totalLogs = (clone $query)->count();
        $logsToday = (clone $query)->whereDate('created_at', Carbon::today())->count();
        $uniqueUsers = (clone $query)->distinct('user_id')->count('user_id');

        $logs = $query->orderBy('created_at', 'desc')
                    ->paginate(25)
                    ->appends(['search' => $search]);

        return view('logs.index', compact('logs', 'totalLogs', 'logsToday', 'uniqueUsers'));
    }
}