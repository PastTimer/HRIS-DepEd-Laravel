<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Models\Employee;

class DesignationController extends Controller
{
    public function index()
    {
        $designations = Designation::withCount('employees')->paginate(10);
        return view('designations.index', compact('designations'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title'       => 'required|string|max:255|unique:designations,title',
            'type'        => 'required|in:teaching,nonteaching',
            'description' => 'nullable|string'
        ]);

        ActivityLog::log(
            'CREATE', 
            'Designation', 
            "Created new designation: {$validatedData['title']}"
        );

        Designation::create($validatedData);

        return redirect('/designations')->with('success', 'Designation added successfully.');
    }

    public function create()
    {
        return view('designations.create');
    }

    public function edit(Designation $designation)
    {
        return view('designations.edit', compact('designation'));
    }

    public function update(Request $request, Designation $designation)
    {
        $validatedData = $request->validate([
            'title'       => 'required|string|max:255|unique:designations,title,' . $designation->id,
            'type'        => 'required|in:teaching,nonteaching',
            'description' => 'nullable|string'
        ]);

        $original = $designation->getOriginal();

        $designation->update($validatedData);

        $changes = [];
        foreach ($designation->getChanges() as $key => $newValue) {
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
                'Designation', 
                "Updated designation details for: {$designation->title}",
                $changes 
            );
        }

        return redirect('/designations')->with('success', 'Designation updated successfully.');
    }

    public function destroy(Designation $designation)
    {
        $employeeCount = $designation->employees()->count();

        if ($employeeCount > 0) {
            return redirect()->back()->with('error', 
                "Cannot delete '{$designation->title}'. There are currently {$employeeCount} employee(s) assigned to this designation. Please reassign or remove them first."
            );
        }
        $designation->delete();
        
        return redirect()->back()->with('success', 'Designation deleted successfully.');
    }

    public function show(Designation $designation)
    {
        $employees = Employee::where('designation_id', $designation->id)
            ->with('school')
            ->orderBy('last_name')
            ->paginate(20);

        return view('designations.show', compact('designation', 'employees'));
    }
}