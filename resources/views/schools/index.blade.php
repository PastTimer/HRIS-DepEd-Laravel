@extends('layouts.app')
@section('title', 'School Profile')
@section('content')

<style>
    /* Makes it obvious the row is interactive and adds a subtle hover effect */
    .clickable-row {
        cursor: pointer;
    }
    .clickable-row:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }
</style>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col">
            <div class="card shadow">
                <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
                    <h3 class="mb-0"><i class="fas fa-school mr-2 text-primary"></i> School Directory</h3>
                    
                    <div class="d-flex align-items-center">
                        <form action="{{ route('schools.index') }}" method="GET" class="mr-3 mb-0">
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" style="min-width: 280px;" 
                                    placeholder="Search school, district, or address..." value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    @if(request('search'))
                                        <a href="{{ route('schools.index') }}" class="btn btn-outline-danger" title="Clear Search">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>

                        <a href="{{ route('districts.index') }}" class="btn btn-sm btn-secondary mr-2">
                            <i class="fas fa-map-marker-alt mr-1"></i> Districts
                        </a>
                        <a href="{{ route('schools.create') }}" class="btn btn-sm btn-success">
                            <i class="fas fa-plus mr-1"></i> New School
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
                                <th>School ID</th>
                                <th>School Name</th>
                                <th>District</th>
                                <th>Address</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schools as $school)
                            <tr class="clickable-row" onclick="window.location='/schools/{{ $school->id }}';">
                                <td>{{ $school->school_id }}</td>
                                <td><strong>{{ $school->name }}</strong></td>
                                <td>{{ $school->district ? $school->district->name : '' }}</td>
                                <td>
                                    {{
                                        Str::limit(
                                            trim(
                                                ($school->address_street ? $school->address_street . ', ' : '') .
                                                ($school->address_barangay ? $school->address_barangay . ', ' : '') .
                                                ($school->address_city ? $school->address_city . ', ' : '') .
                                                ($school->address_province ? $school->address_province . ', ' : '') .
                                                ($school->psgc ? 'PSGC: ' . $school->psgc : '')
                                            , ', ')
                                        , 30)
                                    }}
                                </td>
                                
                                <td onclick="event.stopPropagation();">
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