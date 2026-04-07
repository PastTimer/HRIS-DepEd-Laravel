<?php

namespace App\Http\Controllers;

use App\Models\IspInventory;
use App\Models\IspSpeedtest;
use App\Models\School;
use App\Models\ActivityLog; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IspInventoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = School::where('schools.is_active', true)
            ->with(['isps' => function($query) {
                $query->latest(); 
            }])
            ->leftJoin('school_internet_profile as sip', 'schools.id', '=', 'sip.school_id')
            ->select('schools.*', 'sip.updated_at as profile_updated')
            ->when($search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('schools.name', 'like', "%{$search}%")
                    ->orWhere('schools.school_id', 'like', "%{$search}%")
                    ->orWhereHas('isps', function($ispQuery) use ($search) {
                        $ispQuery->where('provider', 'like', "%{$search}%")
                                ->orWhere('account_no', 'like', "%{$search}%");
                    });
                });
            })
            ->orderBy('schools.name', 'asc');

        if (Auth::check() && Auth::user()->hasRole('school') && !empty(Auth::user()->school_id)) {
            $query->where('schools.id', Auth::user()->school_id);
        }

        $isps = $query->paginate(15)->appends(['search' => $search]);

        return view('isp.index', compact('isps'));
    }

    public function create()
    {
        $query = School::where('is_active', true)->orderBy('name');

        if (Auth::check() && Auth::user()->hasRole('school') && !empty(Auth::user()->school_id)) {
            $query->where('id', Auth::user()->school_id);
        }

        $schools = $query->get();
            
        return view('isp.create', compact('schools'));
    }

    public function edit($id)
    {
        $isp = IspInventory::with(['school', 'speedTests'])->findOrFail($id); 

        if (Auth::check() && Auth::user()->hasRole('school') && !empty(Auth::user()->school_id) && (int) Auth::user()->school_id !== (int) $isp->school_id) {
            abort(403);
        }
        
        return view('isp.edit', compact('isp'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'provider' => 'required|string|max:255',
            'account_no' => 'nullable|string|max:255',
            'internet_type' => 'nullable|string|max:255',
            'subscription_type' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:100',
            'purpose' => 'nullable|string|max:255',
            'acquisition_mode' => 'nullable|string|max:255',
            'donor' => 'nullable|string|max:255',
            'fund_source' => 'nullable|string|max:255',
            'monthly_mrc' => 'nullable|numeric|min:0',
            'plan_speed' => 'nullable|string|max:50',
            'min_speed' => 'nullable|string|max:50',
            'area_coverage' => 'nullable|string|max:255',
            'package_inclusion' => 'nullable|string',
            'installation_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date',
            'ip_type' => 'nullable|string|max:50',
            'public_ip' => 'nullable|string|max:100',
            'remarks' => 'nullable|string',

            'access_points_count' => 'nullable|integer|min:0',
            'access_points_loc' => 'nullable|string|max:255',
            'admin_rooms_covered' => 'nullable|integer|min:0',
            'classrooms_covered' => 'nullable|integer|min:0',
            'admin_connectivity_rating' => 'nullable|integer|min:1|max:5',
            'classroom_connectivity_rating' => 'nullable|integer|min:1|max:5',
            'signal_quality' => 'nullable|string|max:100',
            'isp_service_rating' => 'nullable|integer|min:1|max:5',
            'active_isp_counter' => 'nullable|integer|min:0',
            'active_custom_counter_2' => 'nullable|integer|min:0',
            'active_custom_counter_3' => 'nullable|integer|min:0',

            'init_test_date' => 'nullable|date',
            'init_test_time' => 'nullable|date_format:H:i',
            'init_download' => 'nullable|numeric|min:0',
            'init_upload' => 'nullable|numeric|min:0',
            'init_ping' => 'nullable|integer|min:0',
            'init_remarks_speed' => 'nullable|string',
        ]);

        if (Auth::check() && Auth::user()->hasRole('school') && !empty(Auth::user()->school_id)) {
            $validated['school_id'] = Auth::user()->school_id;
        }

        $ispData = $validated;
        unset(
            $ispData['init_test_date'],
            $ispData['init_test_time'],
            $ispData['init_download'],
            $ispData['init_upload'],
            $ispData['init_ping'],
            $ispData['init_remarks_speed']
        );

        $ispData['monthly_mrc'] = $request->filled('monthly_mrc') ? $request->monthly_mrc : 0.00;
        $ispData['plan_speed'] = $request->filled('plan_speed') ? $request->plan_speed : null;
        $ispData['min_speed'] = $request->filled('min_speed') ? $request->min_speed : null;
        $ispData['access_points_count'] = $request->filled('access_points_count') ? (int) $request->access_points_count : 0;
        $ispData['admin_rooms_covered'] = $request->filled('admin_rooms_covered') ? (int) $request->admin_rooms_covered : 0;
        $ispData['classrooms_covered'] = $request->filled('classrooms_covered') ? (int) $request->classrooms_covered : 0;
        $ispData['active_isp_counter'] = $request->filled('active_isp_counter') ? (int) $request->active_isp_counter : 0;
        $ispData['active_custom_counter_2'] = $request->filled('active_custom_counter_2') ? (int) $request->active_custom_counter_2 : 0;
        $ispData['active_custom_counter_3'] = $request->filled('active_custom_counter_3') ? (int) $request->active_custom_counter_3 : 0;
        $ispData['created_by'] = Auth::id();
        $ispData['updated_by'] = Auth::id();

        $isp = IspInventory::create($ispData);

        if ($request->filled('init_download') && $request->filled('init_upload') && $request->filled('init_test_date') && $request->filled('init_test_time')) {
            $testDate = ($request->init_test_date ?? now()->toDateString()) . ' ' . 
                        ($request->init_test_time ?? now()->toTimeString());

            IspSpeedtest::create([
                'isp_id' => $isp->id, 
                'test_date' => $testDate,
                'download_mbps' => $request->init_download,
                'upload_mbps' => $request->init_upload,
                'ping_ms' => $request->init_ping,
                'tested_by' => Auth::check() ? Auth::user()->username : null,
                'remarks_speed' => $request->init_remarks_speed,
            ]);
        }

        ActivityLog::log(
            'CREATE', 
            'IspInventory', 
            "Added new {$isp->provider} connection for School ID: {$isp->school_id}"
        );

        return redirect()->route('isp.index')->with('success', 'New ISP connection recorded successfully.');
    }

    public function update(Request $request, $id)
    {
        $isp = IspInventory::findOrFail($id);

        if (Auth::check() && Auth::user()->hasRole('school') && !empty(Auth::user()->school_id) && (int) Auth::user()->school_id !== (int) $isp->school_id) {
            abort(403);
        }

        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'provider' => 'required|string|max:255',
            'account_no' => 'nullable|string|max:255',
            'internet_type' => 'nullable|string|max:255',
            'subscription_type' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:100',
            'purpose' => 'nullable|string|max:255',
            'acquisition_mode' => 'nullable|string|max:255',
            'donor' => 'nullable|string|max:255',
            'fund_source' => 'nullable|string|max:255',
            'monthly_mrc' => 'nullable|numeric|min:0',
            'plan_speed' => 'nullable|string|max:50',
            'min_speed' => 'nullable|string|max:50',
            'area_coverage' => 'nullable|string|max:255',
            'package_inclusion' => 'nullable|string',
            'installation_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date',
            'ip_type' => 'nullable|string|max:50',
            'public_ip' => 'nullable|string|max:100',
            'remarks' => 'nullable|string',

            'access_points_count' => 'nullable|integer|min:0',
            'access_points_loc' => 'nullable|string|max:255',
            'admin_rooms_covered' => 'nullable|integer|min:0',
            'classrooms_covered' => 'nullable|integer|min:0',
            'admin_connectivity_rating' => 'nullable|integer|min:1|max:5',
            'classroom_connectivity_rating' => 'nullable|integer|min:1|max:5',
            'signal_quality' => 'nullable|string|max:100',
            'isp_service_rating' => 'nullable|integer|min:1|max:5',
            'active_isp_counter' => 'nullable|integer|min:0',
            'active_custom_counter_2' => 'nullable|integer|min:0',
            'active_custom_counter_3' => 'nullable|integer|min:0',
        ]);

        if (Auth::check() && Auth::user()->hasRole('school') && !empty(Auth::user()->school_id)) {
            $validated['school_id'] = Auth::user()->school_id;
        }
        
        $ispData = $validated;
        $ispData['monthly_mrc'] = $request->filled('monthly_mrc') ? $request->monthly_mrc : 0.00;
        $ispData['plan_speed']  = $request->filled('plan_speed') ? $request->plan_speed : null;
        $ispData['min_speed']   = $request->filled('min_speed') ? $request->min_speed : null;
        $ispData['access_points_count'] = $request->filled('access_points_count') ? (int) $request->access_points_count : 0;
        $ispData['admin_rooms_covered'] = $request->filled('admin_rooms_covered') ? (int) $request->admin_rooms_covered : 0;
        $ispData['classrooms_covered'] = $request->filled('classrooms_covered') ? (int) $request->classrooms_covered : 0;
        $ispData['active_isp_counter'] = $request->filled('active_isp_counter') ? (int) $request->active_isp_counter : 0;
        $ispData['active_custom_counter_2'] = $request->filled('active_custom_counter_2') ? (int) $request->active_custom_counter_2 : 0;
        $ispData['active_custom_counter_3'] = $request->filled('active_custom_counter_3') ? (int) $request->active_custom_counter_3 : 0;
        $ispData['updated_by'] = Auth::id();

        $original = $isp->getOriginal();
        
        $isp->update($ispData);

        $changes = [];
        foreach ($isp->getChanges() as $key => $newValue) {
            // Ignore the timestamp update to prevent cluttering the log
            if ($key !== 'updated_at') {
                $changes[$key] = [
                    'old' => $original[$key] ?? null, 
                    'new' => $newValue
                ];
            }
        }

        if (!empty($changes)) {
            \App\Models\ActivityLog::log(
                'UPDATE', 
                'IspInventory', 
                "Updated {$isp->provider} connection for School ID: {$isp->school_id}",
                $changes
            );
        }

        return redirect()->route('isp.index')->with('success', 'Connection updated successfully.');
    }

    public function storeSpeedTest(Request $request, $id)
    {
        $isp = IspInventory::findOrFail($id);

        if (Auth::check() && Auth::user()->hasRole('school') && !empty(Auth::user()->school_id) && (int) Auth::user()->school_id !== (int) $isp->school_id) {
            abort(403);
        }

        $validated = $request->validate([
            'test_date' => 'required|date',
            'test_time' => 'required|date_format:H:i',
            'download_mbps' => 'required|numeric|min:0',
            'upload_mbps' => 'required|numeric|min:0',
            'ping_ms' => 'nullable|integer|min:0',
            'remarks_speed' => 'nullable|string',
        ]);

        IspSpeedtest::create([
            'isp_id' => $isp->id,
            'test_date' => $validated['test_date'] . ' ' . $validated['test_time'] . ':00',
            'download_mbps' => $validated['download_mbps'],
            'upload_mbps' => $validated['upload_mbps'],
            'ping_ms' => $validated['ping_ms'] ?? null,
            'tested_by' => Auth::check() ? Auth::user()->username : null,
            'remarks_speed' => $validated['remarks_speed'] ?? null,
        ]);

        ActivityLog::log(
            'CREATE',
            'IspSpeedtest',
            "Logged speed test for {$isp->provider} (ISP ID: {$isp->id})"
        );

        return redirect()->route('isp.edit', $isp->id)->with('success', 'Speed test logged successfully.');
    }

    public function destroy($id)
    {
        $isp = IspInventory::findOrFail($id);

        if (Auth::check() && Auth::user()->hasRole('school') && !empty(Auth::user()->school_id) && (int) Auth::user()->school_id !== (int) $isp->school_id) {
            abort(403);
        }

        $schoolId = $isp->school_id;

        ActivityLog::log(
            'DELETE', 
            'IspInventory', 
            "Removed {$isp->provider} connection (Acct: {$isp->account_no}) from School ID: {$schoolId}"
        );

        $isp->delete();
        return redirect()->route('isp.index')->with('success', 'Connection removed.');
    }

    public function show($id)
    {
        return redirect()->route('isp.edit', $id);
    }
}