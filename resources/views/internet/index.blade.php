@extends('layouts.app')
@section('title', 'Internet Connectivity Profiles')

@section('content')
<style>
    .table-legacy thead th { background-color: #f6f9fc; color: #8898aa; text-transform: uppercase; font-size: .65rem; font-weight: bold; padding: 0.75rem; }
    .clickable-row { cursor: pointer; }
    .clickable-row:hover { background-color: #f8f9fe !important; }
</style>

<div class="container-fluid mt-4" data-ajax-content>
    <div class="card shadow">
        <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
            <h3 class="mb-0"><i class="fas fa-poll-h mr-2 text-info"></i> Internet Connectivity Profiles</h3>
            
            <div class="d-flex align-items-center">
                <form action="{{ route('internet.index') }}" method="GET" class="mb-0" data-ajax-search-form>
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="Search school name or ID..." value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                            @if(request('search'))
                                <a href="{{ route('internet.index') }}" class="btn btn-outline-danger" title="Clear Search" data-ajax-clear-search>
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
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
                    <tr class="clickable-row" onclick="window.location='{{ route('internet.show', $s->id) }}';">
                        <td>
                            <strong>{{ $s->name }}</strong><br>
                            <small class="text-muted">{{ $s->school_id }}</small>
                        </td>
                        <td>{{ optional($s->district)->name ?? 'N/A' }}</td>
                        <td>
                            @if($s->internetProfile && $s->internetProfile->updated_at)
                                <span class="badge badge-success">Updated: {{ $s->internetProfile->updated_at->format('M d, Y') }}</span>
                            @else
                                <span class="badge badge-warning">Pending Survey</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('internet.show', $s->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye mr-1"></i> View Profile
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer py-4 d-flex justify-content-center">
            {{ $schools->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endsection