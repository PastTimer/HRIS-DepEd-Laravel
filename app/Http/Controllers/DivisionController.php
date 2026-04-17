<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DivisionController extends Controller
{
    public function index()
    {
        $this->assertAdminOnly();
        $divisions = Division::with('districts')->get();
        return view('divisions.index', compact('divisions'));
    }

    public function show(Division $division)
    {
        $this->assertAdminOnly();
        $division->load('districts');
        return view('divisions.show', compact('division'));
    }

    public function create()
    {
        $this->assertAdminOnly();
        return view('divisions.create');
    }

    public function store(Request $request)
    {
        $this->assertAdminOnly();
        $data = $request->validate(['name' => 'required|string|unique:divisions,name']);
        Division::create($data);
        return redirect()->route('divisions.index')->with('success', 'Division created.');
    }

    public function edit(Division $division)
    {
        $this->assertAdminOnly();
        return view('divisions.edit', compact('division'));
    }

    public function update(Request $request, Division $division)
    {
        $this->assertAdminOnly();
        $data = $request->validate(['name' => 'required|string|unique:divisions,name,' . $division->id]);
        $division->update($data);
        return redirect()->route('divisions.index')->with('success', 'Division updated.');
    }

    public function destroy(Division $division)
    {
        $this->assertAdminOnly();
        $division->delete();
        return redirect()->route('divisions.index')->with('success', 'Division deleted.');
    }

    private function assertAdminOnly(): void
    {
        $user = Auth::user();
        abort_if(!$user || !$user->hasRole('admin'), 403);
    }
}
