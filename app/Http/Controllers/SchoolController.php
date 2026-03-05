<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index()
    {
        $schools = School::orderBy('name', 'asc')->paginate(15);
        return view('schools.index', compact('schools'));
    }

    public function create()
    {
        return view('schools.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|unique:schools,school_id',
            'name' => 'required|string|max:255',
            'district' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        $validated['is_active'] = true;

        School::create($validated);

        return redirect('/schools')->with('success', 'School added successfully.');
    }
}