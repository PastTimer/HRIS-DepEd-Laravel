@extends('layouts.app')
@section('title', 'Employee Directory')
@section('content')

<style>
    /* Makes it obvious the row is clickable */
    .clickable-row {
        cursor: pointer;
    }
    .clickable-row:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
</style>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col">
            <div class="card shadow">
                <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
                    <h3 class="mb-0"><i class="fas fa-user-tie mr-2 text-primary"></i> Employee Directory</h3>
                    
                    <div class="d-flex align-items-center">
                        <form action="{{ route('employees.index') }}" method="GET" class="mr-3 mb-0">
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" style="min-width: 280px;" 
                                    placeholder="Search name, ID, school, or title..." value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    @if(request('search'))
                                        <a href="{{ route('employees.index') }}" class="btn btn-outline-danger" title="Clear Search">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>

                        <a href="{{ route('employees.create') }}" class="btn btn-sm btn-success">
                            <i class="fas fa-plus mr-1"></i> Add Employee
                        </a>
                    </div>
                </div>
                
                @if(session('success'))
                    <div class="alert alert-success m-3 alert-dismissible fade show" role="alert">
                        <span class="alert-icon"><i class="ni ni-like-2"></i></span>
                        <span class="alert-text"><strong>Success!</strong> {{ session('success') }}</span>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table align-items-center table-flush table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Photo</th>
                                <th>Employee ID</th>
                                <th>Full Name</th>
                                <th>Position</th>
                                <th>Station</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $emp)
                            <tr class="clickable-row" onclick="window.location='/employees/{{ $emp->id }}';">
                                <td>
                                    @if($emp->photo_path)
                                        <img src="{{ asset('storage/' . $emp->photo_path) }}" alt="avatar" class="rounded-circle img-thumbnail" style="width: 45px; height: 45px; object-fit: cover;">
                                    @else
                                        <img src="{{ asset('uploads/default/defaultpic.png') }}" alt="avatar" class="rounded-circle img-thumbnail" style="width: 45px; height: 45px; object-fit: cover;">
                                    @endif
                                </td>
                                
                                <td>{{ $emp->employee_id ?? 'N/A' }}</td>
                                
                                <td>
                                    <strong>{{ strtoupper($emp->last_name) }}</strong>, {{ $emp->first_name }} 
                                    {{ $emp->name_ext }} {{ $emp->middle_name }}
                                </td>
                                
                                <td>{{ $emp->designation->title ?? 'Unknown Position' }}</td>
                                <td>{{ $emp->school->name ?? 'Unassigned' }}</td>
                                
                                <td>
                                    @if($emp->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                                
                                <td class="text-center" onclick="event.stopPropagation();">
                                    <a href="/employees/{{ $emp->id }}/edit" class="btn btn-sm btn-info" title="Edit">
                                        Edit
                                    </a>
                                    
                                    <form method="POST" action="/employees/{{ $emp->id }}" style="display:inline;">
                                        @csrf 
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to remove this employee record?')" title="Delete">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <h4 class="text-muted mb-0">No employees found.</h4>
                                    <p class="text-sm">Click "Add Personnel" to register a new employee.</p>
                                </td>
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