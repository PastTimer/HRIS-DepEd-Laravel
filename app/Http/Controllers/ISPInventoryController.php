<?php

namespace App\Http\Controllers;

use App\Models\IspInventory;
use App\Models\School;
use App\Models\ActivityLog; 
use Illuminate\Http\Request;

class IspInventoryController extends Controller
{
    public function index()
    {
        $schools = \App\Models\School::where('is_active', true)
            ->leftJoin('school_internet_profile as sip', 'schools.id', '=', 'sip.school_id')
            ->select('schools.*', 'sip.updated_at as profile_updated')
            ->orderBy('schools.name', 'asc')
            ->paginate(15);

        return view('isp.index', compact('schools'));
    }

    public function store(Request $request)
    {
        $isp = IspInventory::create($request->all());

        ActivityLog::log(
            'CREATE', 
            'IspInventory', 
            "Added new {$isp->provider} connection for School ID: {$isp->school_id}"
        );

        return redirect()->back()->with('success', 'Connection added.');
    }

    public function edit($id)
    {
        $isp = IspInventory::findOrFail($id); 
        $schools = School::orderBy('name')->get();

        return view('isp.edit', compact('isp', 'schools'));
    }

    public function update(Request $request, $id)
    {
        $isp = IspInventory::findOrFail($id);
        $original = $isp->getOriginal();
        
        $isp->update($request->all());

        $changes = [];
        foreach ($isp->getChanges() as $key => $newValue) {
            if ($key !== 'updated_at') {
                $changes[$key] = ['old' => $original[$key] ?? null, 'new' => $newValue];
            }
        }

        if (!empty($changes)) {
            ActivityLog::log(
                'UPDATE', 
                'IspInventory', 
                "Updated {$isp->provider} connection for School ID: {$isp->school_id}",
                $changes
            );
        }

        return redirect("/internet/{$isp->school_id}")->with('success', 'Connection updated.');
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
        return redirect("/internet/{$schoolId}")->with('success', 'Connection removed.');
    }
}