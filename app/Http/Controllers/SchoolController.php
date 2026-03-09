<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Employee;
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

    public function show(School $school)
    {
        $personnel = \App\Models\Employee::where('school_id', $school->id)
            ->where('is_active', true)
            ->orderBy('last_name')
            ->paginate(15);

        $profile = \DB::table('school_profile')->where('schoolid', $school->id)->first();

        return view('schools.show', compact('school', 'personnel', 'profile'));
    }

    public function editProfile(School $school)
    {
        $profile = \DB::table('school_profile')->where('schoolid', $school->id)->first();
        return view('schools.edit_profile', compact('school', 'profile'));
    }

    public function updateProfile(Request $request, School $school)
    {
        $nearby = $request->has('nearby_institutions') ? implode(', ', $request->nearby_institutions) : '';
        $paths = $request->has('access_paths') ? implode(', ', $request->access_paths) : '';

        $data = $request->except(['_token', 'nearby_institutions', 'access_paths']);
        
        $data['nearby_institutions'] = $nearby;
        $data['access_paths'] = $paths;
        $data['schoolid'] = $school->id;
        $data['updated_by'] = \Auth::id();

        \DB::table('school_profile')->updateOrInsert(
            ['schoolid' => $school->id],
            $data
        );

        return redirect("/schools/{$school->id}")->with('success', 'Station Profile updated successfully.');
    }
}