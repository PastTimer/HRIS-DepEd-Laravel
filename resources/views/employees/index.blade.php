@extends('layouts.app')

@section('title', 'Personnel Masterlist')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col">
            <div class="card shadow">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Active Personnel</h3>
                    <a href="/employees/create" class="btn btn-sm btn-primary">Add New Employee</a>
                </div>
                
                <div class="table-responsive">
                    <table class="table align-items-center table-flush">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">ID Number</th>
                                <th scope="col">Name</th>
                                <th scope="col">Position</th>
                                <th scope="col">Station / School</th>
                                <th scope="col">Status</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="list">
                            @forelse($employees as $employee)
                            <tr>
                                <td>{{ $employee->employee_id }}</td>
                                <td>
                                    <strong>{{ $employee->last_name }}, {{ $employee->first_name }}</strong>
                                </td>
                                <td>
                                    {{ $employee->designation ? $employee->designation->title : 'N/A' }}
                                </td>
                                <td>
                                    {{ $employee->school ? $employee->school->name : 'Unassigned' }}
                                </td>
                                <td>
                                    <span class="badge badge-dot mr-4">
                                        <i class="bg-success"></i>
                                        <span class="status">Active</span>
                                    </span>
                                </td>
                                <td>
                                    <a href="/employees/{{ $employee->id }}/edit" class="btn btn-sm btn-info">Edit</a>
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">No active personnel found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="card-footer py-4">
                    {{ $employees->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection