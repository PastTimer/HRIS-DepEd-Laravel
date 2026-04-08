<?php

namespace App\Http\Controllers;

use App\Models\Personnel;
use App\Models\School;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Gather the counts using pure Eloquent
        $user = auth()->user();
        if ($user && ($user->hasRole('school') || $user->hasRole('encoding_officer'))) {
            $schoolId = $user->school_id;
            $activePersonnelCount = Personnel::where('is_active', true)
                ->where('assigned_school_id', $schoolId)
                ->count();
            $activeSchoolsCount = School::where('is_active', true)
                ->where('id', $schoolId)
                ->count();
            $diffStationCount = Personnel::where('is_active', true)
                ->where('assigned_school_id', $schoolId)
                ->whereColumn('assigned_school_id', '!=', 'deployed_school_id')
                ->count();
        } else {
            $activePersonnelCount = Personnel::where('is_active', true)->count();
            $activeSchoolsCount = School::where('is_active', true)->count();
            $diffStationCount = Personnel::whereColumn('assigned_school_id', '!=', 'deployed_school_id')
                ->where('is_active', true)
                ->count();
        }

        // 2. Pass the data to the View
        return view('dashboard.index', compact(
            'activePersonnelCount', 
            'activeSchoolsCount', 
            'diffStationCount'
        ));
    }
}