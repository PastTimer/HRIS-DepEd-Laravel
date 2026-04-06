@extends('layouts.app')
@section('title', 'Audit Trail')
@section('content')

<style>
    /* Custom Badge Colors from your legacy system */
    .badge-CREATE { background-color: #2dce89; color: white; }
    .badge-UPDATE { background-color: #11cdef; color: white; }
    .badge-DELETE { background-color: #f5365c; color: white; }
    .badge-LOGIN  { background-color: #5e72e4; color: white; }
    .badge-LOGOUT { background-color: #8898aa; color: white; }
    .badge-VIEW   { background-color: #fb6340; color: white; }
</style>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card card-stats h-100 shadow-sm">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-muted mb-0">TOTAL LOGS</h5>
                            <span class="h2 font-weight-bold mb-0 text-dark">{{ number_format($totalLogs) }}</span>
                        </div>
                        <div class="col-auto">
                            <div class="icon icon-shape bg-gradient-info text-white rounded-circle shadow">
                                <i class="fas fa-list"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-stats h-100 shadow-sm">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-muted mb-0">LOGS TODAY</h5>
                            <span class="h2 font-weight-bold mb-0 text-dark">{{ number_format($logsToday) }}</span>
                        </div>
                        <div class="col-auto">
                            <div class="icon icon-shape bg-gradient-success text-white rounded-circle shadow">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-stats h-100 shadow-sm">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-muted mb-0">ACTIVE USERS</h5>
                            <span class="h2 font-weight-bold mb-0 text-dark">{{ number_format($uniqueUsers) }}</span>
                        </div>
                        <div class="col-auto">
                            <div class="icon icon-shape bg-gradient-primary text-white rounded-circle shadow">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="card shadow">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center flex-wrap">
                    <div class="mb-2 mb-md-0">
                        <h3 class="mb-0 text-primary"><i class="fas fa-clipboard-list mr-2"></i> System Audit Trail</h3>
                        <div class="text-muted text-sm mt-1">
                            @if(Auth::check() && Auth::user()->hasRole('school') && Auth::user()->school)
                                <i class="fas fa-school mr-1"></i> Filtered by {{ Auth::user()->school->name }}
                            @else
                                <i class="fas fa-globe mr-1"></i> Showing all system logs
                            @endif
                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        <form action="{{ route('logs.index') }}" method="GET" class="mb-0">
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" style="min-width: 260px;" placeholder="Search user, action, module..." value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    @if(request('search'))
                                        <a href="{{ route('logs.index') }}" class="btn btn-outline-danger" title="Clear Search">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table align-items-center table-flush table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 15%">Date & Time</th>
                                <th style="width: 15%">User & IP</th>
                                <th style="width: 15%">Action & Module</th>
                                <th style="width: 25%">Description</th>
                                <th style="width: 30%">Data Changes (Old &rarr; New)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>
                                        <span class="text-sm font-weight-bold">{{ $log->created_at->format('M d, Y') }}</span><br>
                                        <small class="text-muted">{{ $log->created_at->format('h:i:s A') }}</small>
                                    </td>

                                    <td>
                                        <strong>{{ $log->username ?? 'System / Guest' }}</strong><br>
                                        <span class="badge badge-secondary badge-sm">{{ ucfirst($log->user_role) }}</span>
                                        <small class="text-muted d-block mt-1"><i class="fas fa-desktop mr-1"></i> {{ $log->ip_address }}</small>
                                    </td>

                                    <td>
                                        @php
                                            // Color code the action badge
                                            $badgeColor = 'success'; // Default (CREATE)
                                            if ($log->action_type === 'UPDATE') $badgeColor = 'info';
                                            if ($log->action_type === 'DELETE') $badgeColor = 'danger';
                                        @endphp
                                        <span class="badge badge-{{ $badgeColor }}">{{ $log->action_type }}</span><br>
                                        <small class="text-dark font-weight-bold">{{ $log->module }}</small>
                                    </td>

                                    <td class="text-wrap text-sm" style="max-width: 250px;">
                                        {{ $log->description }}
                                    </td>

                                    <td>
                                        @if($log->changes && is_array($log->changes) && count($log->changes) > 0)
                                            <ul class="list-unstyled mb-0" style="font-size: 0.85rem;">
                                                @foreach($log->changes as $field => $values)
                                                    <li class="mb-1 border-bottom pb-1">
                                                        <strong class="text-dark">{{ ucfirst(str_replace('_', ' ', $field)) }}:</strong> 
                                                        
                                                        <span class="text-danger"><del>{{ $values['old'] ?? 'empty' }}</del></span> 
                                                        
                                                        <i class="fas fa-arrow-right text-muted mx-1" style="font-size: 0.7rem;"></i> 
                                                        
                                                        <span class="text-success font-weight-bold">{{ $values['new'] ?? 'empty' }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-muted text-sm"><i class="fas fa-info-circle mr-1"></i> No specific field changes recorded</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-search fa-2x mb-3"></i>
                                            <h4>No audit logs found</h4>
                                            <p>Try adjusting your search criteria or check back later.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer py-4">
                    {{ $logs->appends(request()->query())->links() }}
                </div>
                
                <div class="card-footer py-4">
                    {{ $logs->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection