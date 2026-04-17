<?php

namespace App\Http\Controllers;

use App\Models\Cluster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClusterController extends Controller
{
    public function index()
    {
        $this->assertAdminOnly();
        $clusters = Cluster::with('districts')->get();
        return view('clusters.index', compact('clusters'));
    }

    public function show(Cluster $cluster)
    {
        $this->assertAdminOnly();
        $cluster->load('districts');
        return view('clusters.show', compact('cluster'));
    }

    public function create()
    {
        $this->assertAdminOnly();
        return view('clusters.create');
    }

    public function store(Request $request)
    {
        $this->assertAdminOnly();
        $data = $request->validate(['name' => 'required|string|unique:clusters,name']);
        Cluster::create($data);
        return redirect()->route('clusters.index')->with('success', 'Cluster created.');
    }

    public function edit(Cluster $cluster)
    {
        $this->assertAdminOnly();
        return view('clusters.edit', compact('cluster'));
    }

    public function update(Request $request, Cluster $cluster)
    {
        $this->assertAdminOnly();
        $data = $request->validate(['name' => 'required|string|unique:clusters,name,' . $cluster->id]);
        $cluster->update($data);
        return redirect()->route('clusters.index')->with('success', 'Cluster updated.');
    }

    public function destroy(Cluster $cluster)
    {
        $this->assertAdminOnly();
        $cluster->delete();
        return redirect()->route('clusters.index')->with('success', 'Cluster deleted.');
    }

    private function assertAdminOnly(): void
    {
        $user = Auth::user();
        abort_if(!$user || !$user->hasRole('admin'), 403);
    }
}
