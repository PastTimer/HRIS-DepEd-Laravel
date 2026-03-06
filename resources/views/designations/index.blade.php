@extends('layouts.app')
@section('title', 'Designations')
@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col">
            <div class="card shadow">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Designation List</h3>
                    <a href="/designations/create" class="btn btn-sm btn-primary">Add Designation</a>
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
                                <th>Title</th>
                                <th>Type</th>
                                <th>Employee Count</th> <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($designations as $d)
                            <tr>
                                <td><strong>{{ $d->title }}</strong></td>
                                <td><span class="badge badge-info">{{ strtoupper($d->type) }}</span></td>
                                
                                <td>{{ $d->employees_count ?? 0 }}</td> 
                                
                                <td>
                                    <a href="/designations/{{ $d->id }}/edit" class="btn btn-sm btn-warning">Edit</a>
                                    
                                    <form method="POST" action="/designations/{{ $d->id }}" style="display:inline;">
                                        @csrf 
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this designation?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer py-4">
                    {{ $designations->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection