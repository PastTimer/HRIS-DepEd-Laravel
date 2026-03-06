<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use App\Models\District;
use App\Models\ActivityLog;

class SchoolController extends Controller
{
    public function index()
    {
        $schools = School::orderBy('name', 'asc')->paginate(15);
        return view('schools.index', compact('schools'));
    }

    public function create()
    {
        $districts = District::orderBy('name')->get(); 
        return view('schools.create', compact('districts'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'school_id'       => 'required|string|max:255|unique:schools,school_id',
            'name'            => 'required|string|max:255',
            'district'        => 'required|string|max:255',
            'custom_district' => 'nullable|required_if:district,Other|string|max:255', 
            'address'         => 'nullable|string'
        ]);

        $finalDistrict = $request->district; 
        
        if ($request->district === 'Other') {
            $newDistrict = District::create([
                'name' => $request->custom_district
            ]);
            $finalDistrict = $newDistrict->name;
        } 

        School::create([
            'school_id' => $validatedData['school_id'],
            'name'      => $validatedData['name'],
            'district'  => $finalDistrict,
            'address'   => $validatedData['address'],
        ]);

        ActivityLog::log(
            'CREATE', 
            'School', 
            "Created new school: {$validatedData['name']}"
        );

        return redirect('/schools')->with('success', 'School added successfully.');
    }

    public function edit(School $school)
    {
        $districts = District::orderBy('name')->get(); 
        return view('schools.edit', compact('school', 'districts'));
    }

    public function update(Request $request, School $school)
    {
        $validatedData = $request->validate([
            'school_id'       => 'required|string|max:255|unique:schools,school_id,' . $school->id,
            'name'            => 'required|string|max:255',
            'district'        => 'required|string|max:255',
            'custom_district' => 'nullable|required_if:district,Other|string|max:255', 
            'address'         => 'nullable|string'
        ]);

        $finalDistrict = $request->district; 
        
        if ($request->district === 'Other') {
            $newDistrict = District::create([
                'name' => $request->custom_district
            ]);
            $finalDistrict = $newDistrict->name;
        } 

        $original = $school->getOriginal();

        $school->update([
            'school_id' => $validatedData['school_id'],
            'name'      => $validatedData['name'],
            'district'  => $finalDistrict,
            'address'   => $validatedData['address'],
        ]);

        $changes = [];
        foreach ($school->getChanges() as $key => $newValue) {
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
                'School Profile', 
                "Updated school profile: {$school->name}",
                $changes 
            );
        }

        return redirect('/schools')->with('success', 'School updated successfully.');
    }

    public function destroy(School $school)
    {

        ActivityLog::log(
            'DELETE', 
            'School', 
            "Permanently deleted school: {$school->name}"
        );

        $school->delete();

        return back()->with('success', 'School removed.');
    }
}