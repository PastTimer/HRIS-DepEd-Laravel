<?php

namespace App\Http\Controllers;

use App\Models\IspInventory;
use App\Models\School;
use App\Models\ActivityLog; 
use Illuminate\Http\Request;

class IspInventoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $isps = School::where('schools.is_active', true)
            ->with(['isps' => function($query) {
                $query->where('status', 'Active')->latest(); 
            }])
            ->leftJoin('school_internet_profile as sip', 'schools.id', '=', 'sip.school_id')
            ->select('schools.*', 'sip.updated_at as profile_updated')
            ->when($search, function ($query, $search) {
                // Group the search conditions so it doesn't break the 'is_active' check
                $query->where(function($q) use ($search) {
                    // Search by School Name or ID
                    $q->where('schools.name', 'like', "%{$search}%")
                    ->orWhere('schools.school_id', 'like', "%{$search}%")
                    // Search inside the linked ISP records (Provider or Account Number)
                    ->orWhereHas('isps', function($ispQuery) use ($search) {
                        $ispQuery->where('provider', 'like', "%{$search}%")
                                ->orWhere('account_no', 'like', "%{$search}%");
                    });
                });
            })
            ->orderBy('schools.name', 'asc')
            ->paginate(15)
            // CRITICAL: Keep the search term in the URL when clicking Page 2, Page 3, etc.
            ->appends(['search' => $search]);

        return view('isp.index', compact('isps'));
    }

    public function create()
    {
        // Only fetch active schools that DO NOT have an existing ISP connection
        $schools = School::where('is_active', true)
            ->doesntHave('isps') 
            ->orderBy('name')
            ->get();
            
        return view('isp.create', compact('schools'));
    }

    public function edit($id)
    {
        $isp = IspInventory::with('school')->findOrFail($id); 
        
        return view('isp.edit', compact('isp'));
    }

    public function store(Request $request)
    {
        $ispData = $request->except([
            'init_test_date', 'init_test_time', 
            'init_download', 'init_upload', 'init_ping', 'init_remarks_speed'
        ]);

        // If the field is empty, force it to 0 or 0.00
        $ispData['monthly_mrc'] = $request->filled('monthly_mrc') ? $request->monthly_mrc : 0.00;
        $ispData['plan_speed'] = $request->filled('plan_speed') ? $request->plan_speed : 0;
        $ispData['min_speed'] = $request->filled('min_speed') ? $request->min_speed : 0;

        $isp = IspInventory::create($ispData);

        if ($request->filled('init_download') && $request->filled('init_upload')) {
            $testDate = ($request->init_test_date ?? now()->toDateString()) . ' ' . 
                        ($request->init_test_time ?? now()->toTimeString());

            \Illuminate\Support\Facades\DB::table('isp_speedtests')->insert([
                'isp_id' => $isp->id, 
                'test_date' => $testDate,
                'download_mbps' => $request->init_download,
                'upload_mbps' => $request->init_upload,
                'ping_ms' => $request->init_ping ?? 0,
                'remarks_speed' => $request->init_remarks_speed,
                'created_at' => now(),
                'updated_at' => now()
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
        
        $ispData = $request->except(['_token', '_method']);
        $ispData['monthly_mrc'] = $request->filled('monthly_mrc') ? $request->monthly_mrc : 0.00;
        $ispData['plan_speed']  = $request->filled('plan_speed') ? $request->plan_speed : 0;
        $ispData['min_speed']   = $request->filled('min_speed') ? $request->min_speed : 0;

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

    public function destroy($id)
    {
        $isp = IspInventory::findOrFail($id);
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