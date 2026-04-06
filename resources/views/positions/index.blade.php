@extends('layouts.app')
@section('title', 'Positions')
@section('content')

<style>
    /* Makes it obvious the row is interactive */
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
                    <h3 class="mb-0"><i class="fas fa-briefcase mr-2 text-primary"></i> Positions</h3>
                    
                    <div class="d-flex align-items-center">
                        <form action="{{ route('positions.index') }}" method="GET" class="mr-3 mb-0">
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" style="min-width: 250px;" 
                                    placeholder="Search title, type, or description..." value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    @if(request('search'))
                                        <a href="{{ route('positions.index') }}" class="btn btn-outline-danger" title="Clear Search">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>

                        <a href="{{ route('positions.create') }}" class="btn btn-sm btn-success">
                            <i class="fas fa-plus mr-1"></i> New Position
                        </a>
                    </div>
                </div>   
                @if(session('success'))
                    <div class="alert alert-success m-3 alert-dismissible fade show" role="alert">
                        <span class="alert-text">{{ session('success') }}</span>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table align-items-center table-flush table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th class="text-center">Employee Count</th> 
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($positions as $p)
                            <tr class="clickable-row" onclick="window.location='/positions/{{ $p->id }}';">
                                <td><strong>{{ $p->title }}</strong></td>
                                <td><span class="badge badge-info">{{ strtoupper($p->type) }}</span></td>
                                
                                <td class="text-center">
                                    <span class="badge badge-pill badge-secondary">
                                        {{ $p->employees_count ?? 0 }}
                                    </span>
                                </td> 
                                
                                <td class="text-right" onclick="event.stopPropagation();">
                                    <a href="/positions/{{ $p->id }}/edit" class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    
                                    <form method="POST" action="/positions/{{ $p->id }}" style="display:inline;">
                                        @csrf 
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this position? This may affect assigned personnel.')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="card-footer py-4">
                    {{ $positions->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
    @if(session('error'))
        <div class="alert alert-danger m-3 alert-dismissible fade show" role="alert">
            <span class="alert-icon"><i class="fas fa-exclamation-triangle"></i></span>
            <span class="alert-text"><strong>Action Denied:</strong> {{ session('error') }}</span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
</div>
@endsection