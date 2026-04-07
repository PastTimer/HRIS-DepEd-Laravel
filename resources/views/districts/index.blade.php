@extends('layouts.app')
@section('title', 'Districts')
@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col">
            <div class="card shadow">
                <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
                    <h3 class="mb-0"><i class="fas fa-map-marker-alt mr-2 text-primary"></i> District Directory</h3>
                    <div class="d-flex align-items-center">
                        <a href="{{ route('schools.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Back to School Directory
                        </a>
                        <a href="{{ route('districts.create') }}" class="btn btn-sm btn-success mr-2">
                            <i class="fas fa-plus mr-1"></i> New District
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
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($districts as $district)
                            <tr>
                                <td>{{ $district->id }}</td>
                                <td><strong>{{ $district->name }}</strong></td>
                                <td>
                                    <a href="{{ route('districts.edit', $district->id) }}" class="btn btn-sm btn-info">Edit</a>
                                    <form method="POST" action="{{ route('districts.destroy', $district->id) }}" style="display:inline;">
                                        @csrf 
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this district?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer py-4">
                    {{ $districts->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
