<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Models\Employee;

class PositionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $positions = Position::withCount('employees')
            ->when($search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
                });
            })
            ->orderBy('title', 'asc')
            ->paginate(10)
            ->appends(['search' => $search]);

        return view('positions.index', ['positions' => $positions]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title'       => 'required|string|max:255|unique:positions,title',
            'type'        => 'required|in:Teaching,Non-teaching,Related Teaching',
            'description' => 'nullable|string'
        ]);

        ActivityLog::log(
            'CREATE', 
            'Position', 
            "Created new position: {$validatedData['title']}"
        );

        Position::create($validatedData);

        return redirect('/positions')->with('success', 'Position added successfully.');
    }

    public function create()
    {
        return view('positions.create');
    }

    public function edit(Position $position)
    {
        return view('positions.edit', ['position' => $position]);
    }

    public function update(Request $request, Position $position)
    {
        $validatedData = $request->validate([
            'title'       => 'required|string|max:255|unique:positions,title,' . $position->id,
            'type'        => 'required|in:Teaching,Non-teaching,Related Teaching',
            'description' => 'nullable|string'
        ]);

        $original = $position->getOriginal();

        $position->update($validatedData);

        $changes = [];
        foreach ($position->getChanges() as $key => $newValue) {
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
                'Position', 
                "Updated position details for: {$position->title}",
                $changes 
            );
        }

        return redirect('/positions')->with('success', 'Position updated successfully.');
    }

    public function destroy(Position $position)
    {
        $employeeCount = $position->employees()->count();

        if ($employeeCount > 0) {
            return redirect()->back()->with('error', 
                "Cannot delete '{$position->title}'. There are currently {$employeeCount} employee(s) assigned to this position. Please reassign or remove them first."
            );
        }
        $position->delete();
        
        return redirect()->back()->with('success', 'Position deleted successfully.');
    }

    public function show(Position $position)
    {
        $employees = Employee::where('position_id', $position->id)
            ->with('school')
            ->orderBy('last_name')
            ->paginate(20);

        return view('positions.show', ['position' => $position, 'employees' => $employees]);
    }
}