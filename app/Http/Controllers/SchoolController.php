<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use App\Models\District;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class SchoolController extends Controller
{
    private function schoolScopeId(): ?int
    {
        $user = Auth::user();

        if ($user && $user->hasRole('school') && $user->school_id) {
            return (int) $user->school_id;
        }

        if ($user && $user->hasRole('encoding_officer') && !$user->isGlobalEncodingOfficer() && $user->school_id) {
            return (int) $user->school_id;
        }

        return null;
    }

    private function assertSchoolRecordAccess(School $school): void
    {
        $schoolId = $this->schoolScopeId();
        if ($schoolId) {
            abort_if((int) $school->id !== $schoolId, 403);
        }
    }

    private function assertCanWriteSchool(): void
    {
        $user = Auth::user();

        if ($user && $user->hasRole('encoding_officer')) {
            abort(403);
        }
    }

    private function assertAdminOnly(): void
    {
        $user = Auth::user();
        abort_if(!$user || !$user->hasRole('admin'), 403);
    }

    public function index(Request $request)
    {
        $schoolId = $this->schoolScopeId();
        if ($schoolId) {
            return redirect()->route('schools.show', $schoolId);
        }

        $search = $request->input('search');

        $schools = School::with('district')
            ->when($search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('school_id', 'like', "%{$search}%")
                      ->orWhereHas('district', function($dq) use ($search) {
                          $dq->where('name', 'like', "%{$search}%");
                      })
                      ->orWhere('address_street', 'like', "%{$search}%")
                      ->orWhere('address_barangay', 'like', "%{$search}%")
                      ->orWhere('address_city', 'like', "%{$search}%")
                      ->orWhere('address_province', 'like', "%{$search}%");
                });
            })
            ->get();

        // Assigned: has school_id, name, district, and address (any address field)
        $assigned = $schools->filter(function($school) {
            return $school->school_id && $school->name && $school->district && (
                $school->address_street || $school->address_barangay || $school->address_city || $school->address_province
            );
        });
        $unassigned = $schools->filter(function($school) {
            return !$school->school_id || !$school->name || !$school->district || !(
                $school->address_street || $school->address_barangay || $school->address_city || $school->address_province
            );
        });

        // Merge assigned first, then unassigned
        $sortedSchools = $assigned->concat($unassigned)->values();

        // Paginate manually
        $perPage = 15;
        $currentPage = $request->input('page', 1);
        $paginatedSchools = new \Illuminate\Pagination\LengthAwarePaginator(
            $sortedSchools->forPage($currentPage, $perPage),
            $sortedSchools->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $assignedCount = $assigned->count();
        $unassignedCount = $unassigned->count();

        return view('schools.index', [
            'schools' => $paginatedSchools,
            'assignedCount' => $assignedCount,
            'unassignedCount' => $unassignedCount,
        ]);
    }

    public function create()
    {
        $this->assertAdminOnly();

        $districts = District::orderBy('name')->get(); 
        return view('schools.create', compact('districts'));
    }

    public function store(Request $request)
    {
        $this->assertAdminOnly();

        $input = $request->all();
        // If blank school (no school_id and no name), generate only school_id and name, nothing else
        if (empty($input['school_id']) && empty($input['name'])) {
            $counter = 1;
            do {
                $id = str_pad((string) $counter, 4, '0', STR_PAD_LEFT);
                $code = 'SCHOOL-' . $id;
                $counter++;
            } while (School::where('school_id', $code)->exists());
            $school = School::create([
                'school_id' => $code,
                'name' => 'School ' . $id,
                'is_active' => true,
            ]);
            ActivityLog::log(
                'CREATE', 
                'School', 
                "Created new blank school: School $id"
            );
            return redirect('/schools')->with('success', 'Blank school added successfully.');
        }

        // Otherwise, normal validation/creation
        $validatedData = $request->validate([
            'school_id'   => 'required|string|max:255|unique:schools,school_id',
            'name'        => 'required|string|max:255',
            'district_id' => 'required|exists:districts,id',
        ]);
        $validatedData['school_id'] = $input['school_id'];
        $validatedData['name'] = $input['name'];

        School::create($validatedData);

        ActivityLog::log(
            'CREATE', 
            'School', 
            "Created new school: {$validatedData['name']}"
        );

        return redirect('/schools')->with('success', 'School added successfully.');
    }

    public function edit(School $school)
    {
        $this->assertCanWriteSchool();
        $this->assertSchoolRecordAccess($school);

        $districts = District::orderBy('name')->get(); 
        return view('schools.edit', compact('school', 'districts'));
    }

    public function update(Request $request, School $school)
    {
        $this->assertCanWriteSchool();
        $this->assertSchoolRecordAccess($school);

        $validatedData = $request->validate([
            'school_id'   => 'required|string|max:255|unique:schools,school_id,' . $school->id,
            'name'        => 'required|string|max:255',
            'district_id' => 'required|exists:districts,id',
            'address'     => 'nullable|string'
        ]);

        $original = $school->getOriginal();

        $school->update($validatedData);

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
        $this->assertAdminOnly();

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
        $this->assertSchoolRecordAccess($school);

        $personnel = \App\Models\Personnel::with('pdsMain')
            ->where('assigned_school_id', $school->id)
            ->where('is_active', true)
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('schools.show', compact('school', 'personnel'));
    }

    public function editProfile(School $school)
    {
        $this->assertCanWriteSchool();
        $this->assertSchoolRecordAccess($school);

        $districts = \App\Models\District::orderBy('name')->get();
        return view('schools.edit_profile', compact('school', 'districts'));
    }

    public function updateProfile(Request $request, School $school)
    {
        $this->assertCanWriteSchool();
        $this->assertSchoolRecordAccess($school);

        $nearby = $request->has('nearby_institutions') ? implode(', ', $request->nearby_institutions) : '';
        $paths = $request->has('access_paths') ? implode(', ', $request->access_paths) : '';

        $data = $request->except(['_token', 'nearby_institutions', 'access_paths']);
        $data['nearby_institutions'] = $nearby;
        $data['access_paths'] = $paths;

        $school->update($data);

        return redirect("/schools/{$school->id}")->with('success', 'School profile updated successfully.');
    }
}