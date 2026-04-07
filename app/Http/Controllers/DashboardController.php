<?php

namespace App\Http\Controllers;

use App\Models\Personnel;
use App\Models\School;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Gather the counts using pure Eloquent
        $activePersonnelCount = Personnel::where('is_active', true)->count();
        $activeSchoolsCount = School::where('is_active', true)->count();
        
        // Count personnel deployed to a different school than they are assigned
        $diffStationCount = Personnel::whereColumn('assigned_school_id', '!=', 'deployed_school_id')
                                    ->where('is_active', true)
                                    ->count();

        // 2. Pass the data to the View
        return view('dashboard.index', compact(
            'activePersonnelCount', 
            'activeSchoolsCount', 
            'diffStationCount'
        ));
    }
}