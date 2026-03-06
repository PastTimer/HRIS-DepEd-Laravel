<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LogController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Base query setup
        $query = ActivityLog::query();

        // If the user is a 'school' user, strictly filter logs to their specific school 
        if ($user && $user->role === 'school') {
            $query->where('access_level', $user->access_level);
        }

        // 1. Calculate Stats
        $totalLogs = (clone $query)->count();
        $logsToday = (clone $query)->whereDate('created_at', Carbon::today())->count();
        $uniqueUsers = (clone $query)->distinct('user_id')->count('user_id');

        // 2. Fetch the paginated logs 
        $logs = $query->orderBy('created_at', 'desc')->paginate(25);

        return view('logs.index', compact('logs', 'totalLogs', 'logsToday', 'uniqueUsers'));
    }
}