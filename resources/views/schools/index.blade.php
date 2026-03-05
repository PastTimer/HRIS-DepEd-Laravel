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
                                    <a href="#" class="btn btn-sm btn-info">Edit</a>
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