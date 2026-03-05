<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    public function index()
    {
        $designations = Designation::orderBy('title', 'asc')->paginate(15);
        return view('designations.index', compact('designations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255|unique:designations,title',
            'type' => 'required|in:teaching,nonteaching',
            'description' => 'nullable|string'
        ]);

        Designation::create($request->all());
        return redirect('/designations')->with('success', 'Designation added successfully.');
    }

    public function create()
    {
        return view('designations.create');
    }

    public function destroy(Designation $designation)
    {
        $designation->delete();
        return back()->with('success', 'Designation removed.');
    }
}