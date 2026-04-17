@extends('layouts.app')
@section('title', 'Clusters')
@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col">
            <div class="card ppc-card">
                <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
                    <h3 class="mb-0"><i class="fas fa-layer-group mr-2 text-primary"></i> Cluster Directory</h3>
                    <div class="d-flex align-items-center">
                        <a href="{{ route('schools.index') }}" class="btn btn-outline-secondary btn-sm mr-2">
                            <i class="fas fa-arrow-left mr-1"></i> Back to School Directory
                        </a>
                        <a href="{{ route('divisions.index') }}" class="btn btn-outline-secondary btn-sm mr-2">
                            Divisions
                        </a>
                        <a href="{{ route('districts.index') }}" class="btn btn-outline-secondary btn-sm mr-2">
                            Districts
                        </a>
                        <a href="{{ route('clusters.create') }}" class="btn btn-sm btn-success mr-2">
                            <i class="fas fa-plus mr-1"></i> New Cluster
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
                            @foreach($clusters as $cluster)
                            <tr>
                                <td>{{ $cluster->id }}</td>
                                <td><strong>{{ $cluster->name }}</strong></td>
                                <td>
                                    @foreach($cluster->districts as $district)
                                        <span class="badge badge-info">{{ $district->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <a href="{{ route('clusters.edit', $cluster->id) }}" class="btn btn-sm btn-info">Edit</a>
                                    <form method="POST" action="{{ route('clusters.destroy', $cluster->id) }}" style="display:inline;">
                                        @csrf 
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this cluster?')">Delete</button>
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