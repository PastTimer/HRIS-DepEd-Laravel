@extends('layouts.app')
@section('title', 'Internet Connectivity Dashboard')

@section('content')
<style>
    .table-legacy thead th { background-color: #f6f9fc; color: #8898aa; text-transform: uppercase; font-size: .65rem; font-weight: bold; padding: 0.75rem; }
    .clickable-row { cursor: pointer; }
    .clickable-row:hover { background-color: #f8f9fe !important; }
</style>

<div class="container-fluid mt-4">
    <div class="card shadow">
        <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
            <h3 class="mb-0"><i class="fas fa-wifi mr-2 text-primary"></i> Internet Connectivity Profiles</h3>
            <div class="search-box">
                <input type="text" class="form-control form-control-sm" placeholder="Search school...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-items-center table-flush table-hover table-legacy">
                <thead>
                    <tr>
                        <th>School Name & ID</th>
                        <th>District</th>
                        <th>Profile Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schools as $s)
                    <tr class="clickable-row" onclick="window.location='/internet/{{ $s->id }}';">
                        <td>
                            <strong>{{ $s->name }}</strong><br>
                            <small class="text-muted">{{ $s->school_id }}</small>
                        </td>
                        <td>{{ $s->district }}</td>
                        <td>
                            @if($s->profile_updated)
                                <span class="badge badge-success">Updated: {{ date('M d, Y', strtotime($s->profile_updated)) }}</span>
                            @else
                                <span class="badge badge-warning">Pending Survey</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="/internet/{{ $s->id }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye mr-1"></i> View Profile
                            </a>
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
@endsection