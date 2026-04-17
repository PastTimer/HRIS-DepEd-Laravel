@extends('layouts.app')
@section('title', 'Divisions')
@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col">
            <div class="card ppc-card">
                <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
                    <h3 class="mb-0"><i class="fas fa-building mr-2 text-primary"></i> Division Directory</h3>
                    <div class="d-flex align-items-center">
                        <a href="{{ route('schools.index') }}" class="btn btn-outline-secondary btn-sm mr-2">
                            <i class="fas fa-arrow-left mr-1"></i> Back to School Directory
                        </a>
                        <a href="{{ route('districts.index') }}" class="btn btn-outline-secondary btn-sm mr-2">
                            Districts
                        </a>
                        <a href="{{ route('clusters.index') }}" class="btn btn-outline-secondary btn-sm mr-2">
                            Clusters
                        </a>
                        <a href="{{ route('divisions.create') }}" class="btn btn-sm btn-success mr-2">
                            <i class="fas fa-plus mr-1"></i> New Division
                        </a>
                    </div>
                </div>
                @if(session('success'))
                    <div class="alert alert-success m-3">
                        {{ session('success') }}
                    </div>
                @endif
                <div class="table-responsive">
                    <table class="table align-items-center table-flush table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Districts</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($divisions as $division)
                            <tr>
                                <td>{{ $division->id }}</td>
                                <td><strong>{{ $division->name }}</strong></td>
                                <td>
                                    @foreach($division->districts as $district)
                                        <span class="badge badge-info">{{ $district->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <a href="{{ route('divisions.edit', $division->id) }}" class="btn btn-sm btn-info">Edit</a>
                                    <form method="POST" action="{{ route('divisions.destroy', $division->id) }}" style="display:inline;">
                                        @csrf 
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this division?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection