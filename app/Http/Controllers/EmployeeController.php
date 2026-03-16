<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\School;
use App\Models\Designation;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $employees = Employee::with(['school', 'designation'])
            ->when($search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    // 1. Search Employee Table Directly
                    $q->where('last_name', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%")
                    ->orWhere('item_no', 'like', "%{$search}%")
                    ->orWhere('email_address', 'like', "%{$search}%")
                    
                    // 2. Search linked Designation (Job Title)
                    ->orWhereHas('designation', function($desigQuery) use ($search) {
                        $desigQuery->where('title', 'like', "%{$search}%");
                    })
                    
                    // 3. Search linked School (Station)
                    ->orWhereHas('school', function($schoolQuery) use ($search) {
                        $schoolQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('school_id', 'like', "%{$search}%");
                    });
                });
            })
            ->orderBy('last_name', 'asc')
            ->paginate(15)
            ->appends(['search' => $search]);

        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        $schools = School::where('is_active', true)->orderBy('name')->get();
        $designations = Designation::orderBy('title')->get();
        return view('employees.create', compact('schools', 'designations'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            // Personal
            'last_name'      => 'required|string|max:255',
            'first_name'     => 'required|string|max:255',
            'middle_name'    => 'nullable|string|max:255',
            'name_ext'       => 'nullable|string|max:10',
            'gender'         => 'required|in:Male,Female',
            'date_of_birth'  => 'required|date',
            'place_of_birth' => 'nullable|string|max:255',
            'civil_status'   => 'nullable|string|max:50',
            'blood_type'     => 'nullable|string|max:10',
            
            // Employment
            'employee_id'    => 'nullable|string|max:255|unique:employees,employee_id',
            'designation_id' => 'required|exists:designations,id',
            'item_no'        => 'nullable|string|max:255',
            'step'           => 'required|integer|min:1',
            'last_step'      => 'required|date',
            'sg'             => 'nullable|string|max:50',
            'employee_type'  => 'required|string|max:100',
            
            // Station
            'school_id'          => 'required|exists:schools,id',
            'deployed_school_id' => 'nullable|exists:schools,id',
            
            // IDs
            'gsis_no'        => 'nullable|string|max:100',
            'pagibig_no'     => 'nullable|string|max:100',
            'philhealth_no'  => 'nullable|string|max:100',
            'sss_no'         => 'nullable|string|max:100',
            'tin_no'         => 'nullable|string|max:100',
            
            // Contact
            'contact_no'     => 'nullable|string|max:50',
            'email_address'  => 'nullable|email|max:255',
            'address'        => 'nullable|string',
            
            'is_active'      => 'required|boolean',
            'photo'          => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', 
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('employee_photos', 'public');
        }
        $deployedSchool = $request->deployed_school_id ? $request->deployed_school_id : $request->school_id;

        Employee::create(array_merge($validatedData, [
            'deployed_school_id' => $deployedSchool,
            'photo_path'         => $photoPath,
        ]));

        ActivityLog::log(
            'CREATE', 
            'Employee', 
            "Created new employee: {$validatedData['first_name']} {$validatedData['last_name']}"
        );

        return redirect('/employees')->with('success', 'Employee record added successfully.');
    }

    public function edit(Employee $employee)
    {
        $schools = School::orderBy('name')->get();
        $designations = Designation::orderBy('title')->get();

        return view('employees.edit', compact('employee', 'schools', 'designations'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validatedData = $request->validate([
            'last_name'      => 'required|string|max:255',
            'first_name'     => 'required|string|max:255',
            'middle_name'    => 'nullable|string|max:255',
            'name_ext'       => 'nullable|string|max:10',
            'gender'         => 'required|in:Male,Female',
            'date_of_birth'  => 'required|date',
            'place_of_birth' => 'nullable|string|max:255',
            'civil_status'   => 'nullable|string|max:50',
            'blood_type'     => 'nullable|string|max:10',
            
            'employee_id'    => 'nullable|string|max:255|unique:employees,employee_id,' . $employee->id,
            'designation_id' => 'required|exists:designations,id',
            'item_no'        => 'nullable|string|max:255',
            'step'           => 'required|integer|min:1',
            'last_step'      => 'required|date',
            'sg'             => 'nullable|string|max:50',
            'employee_type'  => 'required|string|max:100',
            
            'school_id'          => 'required|exists:schools,id',
            'deployed_school_id' => 'nullable|exists:schools,id',
            
            'gsis_no'        => 'nullable|string|max:100',
            'pagibig_no'     => 'nullable|string|max:100',
            'philhealth_no'  => 'nullable|string|max:100',
            'sss_no'         => 'nullable|string|max:100',
            'tin_no'         => 'nullable|string|max:100',
            
            'contact_no'     => 'nullable|string|max:50',
            'email_address'  => 'nullable|email|max:255',
            'address'        => 'nullable|string',
            
            'is_active'      => 'required|boolean',
            'photo'          => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $photoPath = $employee->photo_path; 

        if ($request->hasFile('photo')) {
            if ($photoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($photoPath)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($photoPath);
            }
            $photoPath = $request->file('photo')->store('employee_photos', 'public');
        }

        $deployedSchool = $request->deployed_school_id ? $request->deployed_school_id : $request->school_id;

        $original = $employee->getOriginal();

        $employee->update(array_merge($validatedData, [
            'deployed_school_id' => $deployedSchool,
            'photo_path'         => $photoPath,
        ]));

        $changes = [];
        foreach ($employee->getChanges() as $key => $newValue) {
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
                'Employee', 
                "Updated employee details for: {$employee->first_name} {$employee->last_name}",
                $changes 
            );
        }

        return redirect('/employees')->with('success', 'Employee record updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        if ($employee->photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($employee->photo_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($employee->photo_path);
        }

        ActivityLog::log(
            'DELETE', 
            'Employee', 
            "Permanently deleted employee: {$employee->first_name} {$employee->last_name}"
        );

        $employee->delete();

        return redirect('/employees')->with('success', 'Employee record removed successfully.');
    }

    public function specialOrders()
    {
        return $this->belongsToMany(SpecialOrder::class, 'employee_special_order', 'employee_id', 'special_order_id')->withTimestamps();
    }

    public function show($id)
    {
        $employee = \App\Models\Employee::with([
            'school', 
            'equipment', 
            'trainings',  
            'specialOrders' 
        ])->findOrFail($id);

        return view('employees.show', compact('employee'));
    }
}