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
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0 text-primary"><i class="fas fa-clipboard-list mr-2"></i> System Audit Trail</h3>
                    </div>
                    <div class="text-muted text-sm">
                        @if(Auth::check() && Auth::user()->role === 'school')
                            <i class="fas fa-school mr-1"></i> Filtered by {{ Auth::user()->access_level }}
                        @else
                            <i class="fas fa-globe mr-1"></i> Showing all system logs
                        @endif
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-items-center table-flush table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center">DATE & TIME</th>
                                <th>USER</th>
                                <th class="text-center">ACTION</th>
                                <th class="text-center">MODULE</th>
                                <th>DESCRIPTION</th>
                                <th class="text-center">IP ADDRESS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td class="text-center text-sm">
                                    {{ $log->created_at->format('M d, Y') }} <br>
                                    <small class="text-muted">{{ $log->created_at->format('H:i A') }}</small>
                                </td>
                                
                                <td>
                                    <span class="font-weight-bold">{{ $log->username ?? 'System' }}</span><br>
                                    <small class="text-muted">{{ strtoupper($log->user_role ?? 'N/A') }}</small>
                                </td>
                                
                                <td class="text-center">
                                    <span class="badge badge-pill badge-{{ strtoupper($log->action_type) }}">
                                        {{ strtoupper($log->action_type) }}
                                    </span>
                                </td>
                                
                                <td class="text-center text-sm">{{ $log->module }}</td>
                                
                                <td class="text-sm" style="white-space: normal; min-width: 300px;">
                                    {{ $log->description }}
                                    
                                    @if(!empty($log->changes) && is_array($log->changes))
                                        <div class="mt-2 pl-2 border-left border-info">
                                            <small class="text-info font-weight-bold">Changes:</small><br>
                                            @foreach($log->changes as $field => $values)
                                                <small class="text-muted">
                                                    &bull; <strong>{{ ucfirst(str_replace('_', ' ', $field)) }}:</strong> 
                                                    <span class="text-danger">{{ $values['old'] ?? '(empty)' }}</span> &rarr; 
                                                    <span class="text-success">{{ $values['new'] ?? '(empty)' }}</span>
                                                </small><br>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                
                                <td class="text-center text-sm text-muted">{{ $log->ip_address ?? 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-history fa-3x mb-3"></i>
                                    <h4>No activity logs found.</h4>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="card-footer py-4">
                    {{ $logs->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection