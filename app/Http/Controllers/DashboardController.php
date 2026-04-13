<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Personnel;
use App\Models\School;
use App\Models\SpecialOrder;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user?->hasRole('admin');
        $isSchoolScoped = $user && (
            $user->hasRole('school')
            || ($user->hasRole('encoding_officer') && !$user->isGlobalEncodingOfficer())
        );
        $isPersonnel = $user?->hasRole('personnel');

        $scopeSchoolId = null;
        if ($isSchoolScoped) {
            $scopeSchoolId = $user->school_id;
        } elseif ($isPersonnel) {
            $scopeSchoolId = $user->personnel?->assigned_school_id ?? $user->school_id;
        }

        $personnelScope = Personnel::query()->with(['pdsMain:id,personnel_id,last_name,first_name', 'school:id,name']);
        $schoolScope = School::query()->where('is_active', true);
        $specialOrderScope = SpecialOrder::query();

        if ($isSchoolScoped) {
            if ($scopeSchoolId) {
                $personnelScope->where('assigned_school_id', $scopeSchoolId);
                $schoolScope->where('id', $scopeSchoolId);
                $specialOrderScope
                    ->whereHas('personnel', function ($q) use ($scopeSchoolId) {
                        $q->where('assigned_school_id', $scopeSchoolId);
                    })
                    ->whereDoesntHave('personnel', function ($q) use ($scopeSchoolId) {
                        $q->where('assigned_school_id', '!=', $scopeSchoolId);
                    });
            } else {
                $personnelScope->whereRaw('1 = 0');
                $schoolScope->whereRaw('1 = 0');
                $specialOrderScope->whereRaw('1 = 0');
            }
        } elseif ($isPersonnel) {
            if ($user->personnel_id) {
                $personnelScope->whereKey($user->personnel_id);
                if ($scopeSchoolId) {
                    $schoolScope->where('id', $scopeSchoolId);
                } else {
                    $schoolScope->whereRaw('1 = 0');
                }
                $specialOrderScope
                    ->whereHas('personnel', function ($q) use ($user) {
                        $q->where('personnel.id', $user->personnel_id);
                    })
                    ->where('status', 'Approved');
            } else {
                $personnelScope->whereRaw('1 = 0');
                $schoolScope->whereRaw('1 = 0');
                $specialOrderScope->whereRaw('1 = 0');
            }
        }


        $activePersonnelCount = (clone $personnelScope)->where('is_active', true)->count();
        $inactivePersonnelCount = (clone $personnelScope)->where('is_active', false)->count();
        $activeSchoolsCount = (clone $schoolScope)->count();
        $totalSpecialOrdersVisible = (clone $specialOrderScope)->count();

        // Pending SO for School and Encoding Officer
        $pendingSpecialOrdersCount = null;
        if ($isSchoolScoped) {
            $pendingSpecialOrdersCount = (clone $specialOrderScope)->where('status', 'Pending')->count();
        }

        $employeeTypeBreakdown = (clone $personnelScope)
            ->where('is_active', true)
            ->selectRaw("COALESCE(employee_type, 'Unspecified') as employee_type, COUNT(*) as total")
            ->groupBy('employee_type')
            ->orderByDesc('total')
            ->get();

        $recentSpecialOrders = (clone $specialOrderScope)
            ->with(['type'])
            ->latest()
            ->take(6)
            ->get();

        $schoolSnapshot = null;
        if ($scopeSchoolId) {
            $schoolSnapshot = School::query()
                ->select(['id', 'name', 'governance_level', 'head_name'])
                ->find($scopeSchoolId);
        }

        $recentPersonnel = (clone $personnelScope)
            ->select(['id', 'assigned_school_id', 'employee_type', 'is_active', 'created_at'])
            ->latest()
            ->take(8)
            ->get();

        if ($isAdmin) {
            $recentActivities = ActivityLog::latest()->take(8)->get();
        } elseif ($isSchoolScoped && $scopeSchoolId) {
            $recentActivities = ActivityLog::whereHas('user', function ($q) use ($scopeSchoolId) {
                $q->where('school_id', $scopeSchoolId);
            })->latest()->take(8)->get();
        } elseif ($user) {
            $recentActivities = ActivityLog::where('user_id', $user->id)->latest()->take(8)->get();
        } else {
            $recentActivities = collect();
        }

        $roleName = $user?->getRoleNames()->first() ?? 'user';
        $quickLinks = [];

        if ($isAdmin || $user?->hasRole('school')) {
            $quickLinks[] = ['label' => 'Personnel', 'route' => 'personnel.index', 'icon' => 'ni ni-single-02'];
            $quickLinks[] = ['label' => 'Equipment', 'route' => 'equipment.index', 'icon' => 'ni ni-archive-2'];
            $quickLinks[] = ['label' => 'SO Requests', 'route' => 'specialorder.requests', 'icon' => 'ni ni-folder-17'];
            $quickLinks[] = ['label' => 'Reports', 'route' => 'report.index', 'icon' => 'ni ni-chart-bar-32'];
        }

        if ($user?->hasRole('encoding_officer')) {
            $quickLinks[] = ['label' => 'Special Orders', 'route' => 'specialorder.index', 'icon' => 'ni ni-paper-diploma'];
            $quickLinks[] = ['label' => 'SO Requests', 'route' => 'specialorder.requests', 'icon' => 'ni ni-folder-17'];
        }

        if ($isPersonnel) {
            $quickLinks[] = ['label' => 'My Profile', 'route' => 'personnel.me', 'icon' => 'ni ni-circle-08'];
            $quickLinks[] = ['label' => 'My Special Orders', 'route' => 'specialorder.index', 'icon' => 'ni ni-paper-diploma'];
        }

        return view('dashboard.index', compact(
            'activePersonnelCount',
            'inactivePersonnelCount',
            'activeSchoolsCount',
            'totalSpecialOrdersVisible',
            'employeeTypeBreakdown',
            'recentPersonnel',
            'recentSpecialOrders',
            'recentActivities',
            'schoolSnapshot',
            'roleName',
            'quickLinks',
            'pendingSpecialOrdersCount'
        ));
    }
}