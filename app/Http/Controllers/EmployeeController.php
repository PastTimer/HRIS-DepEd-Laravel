<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        // 1. Fetch active employees
        $employees = Employee::with(['school', 'designation'])
                            ->where('is_active', true)
                            ->orderBy('last_name', 'asc')
                            ->paginate(15);

        // 2. Pass them to the view
        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        // We need to fetch schools and designations so the user can select them in the dropdowns!
        $schools = \App\Models\School::where('is_active', true)->orderBy('name')->get();
        $designations = \App\Models\Designation::where('is_active', true)->orderBy('title')->get();
        
        return view('employees.create', compact('schools', 'designations'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            // Basic & Assignment
            'employee_id' => 'required|unique:employees,employee_id',
            'school_id' => 'nullable|exists:schools,id',
            'designation_id' => 'nullable|exists:designations,id',
            'employee_type' => 'nullable|string',
            
            // Personal
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'name_ext' => 'nullable|string|max:50',
            'gender' => 'nullable|in:Male,Female',
            'date_of_birth' => 'nullable|date',
            'place_of_birth' => 'nullable|string',
            'civil_status' => 'nullable|string',
            'blood_type' => 'nullable|string',
            
            // Contact
            'contact_no' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',

            // Gov IDs
            'gsis_no' => 'nullable|string',
            'pagibig_no' => 'nullable|string',
            'philhealth_no' => 'nullable|string',
            'sss_no' => 'nullable|string',
            'tin_no' => 'nullable|string',

            // Employment Details
            'item_no' => 'nullable|string',
            'salary_grade' => 'nullable|string',
            'step' => 'nullable|string',
        ]);

        $validatedData['is_active'] = true;

        Employee::create($validatedData);

        return redirect('/employees')->with('success', 'Employee profile created successfully!');
    }
}