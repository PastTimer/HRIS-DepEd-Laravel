<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\School;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Gather the counts using pure Eloquent
        $activePersonnelCount = Employee::where('is_active', true)->count();
        $activeSchoolsCount = School::where('is_active', true)->count();
        
        // Count employees deployed to a different school than they are assigned
        $diffStationCount = Employee::whereColumn('school_id', '!=', 'deployed_school_id')
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