@extends('layouts.app')
@section('title', 'School Profile')
@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col">
            <div class="card shadow">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Station / School List</h3>
                    <a href="/schools/create" class="btn btn-sm btn-primary">Add School</a>
                </div>
                
                @if(session('success'))
                    <div class="alert alert-success m-3">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table align-items-center table-flush">
                        <thead class="thead-light">
                            <tr>
                                <th>School ID</th>
                                <th>School Name</th>
                                <th>District</th>
                                <th>Address</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schools as $school)
                            <tr>
                                <td>{{ $school->school_id }}</td>
                                <td><strong>{{ $school->name }}</strong></td>
                                <td>{{ $school->district }}</td>
                                <td>{{ Str::limit($school->address, 30) }}</td>
                                <td>
                                    <a href="/schools/{{ $school->id }}/edit" class="btn btn-sm btn-info">Edit</a>
                                    
                                    <form method="POST" action="/schools/{{ $school->id }}" style="display:inline;">
                                        @csrf 
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this school?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer py-4">
                    {{ $schools->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection