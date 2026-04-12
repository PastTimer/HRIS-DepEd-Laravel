@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-end align-items-center mb-4">
        <span class="badge badge-pill badge-info px-3 py-2">Role: {{ strtoupper(str_replace('_', ' ', $roleName)) }}</span>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="card card-stats ppc-card mb-4">
                <div class="card-body">
                    <h5 class="card-title text-uppercase text-muted mb-0">Active Personnel</h5>
                    <span class="h2 font-weight-bold mb-0">{{ number_format($activePersonnelCount) }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stats shadow mb-4">
                <div class="card-body">
                    <h5 class="card-title text-uppercase text-muted mb-0">Inactive Personnel</h5>
                    <span class="h2 font-weight-bold mb-0">{{ number_format($inactivePersonnelCount) }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stats shadow mb-4">
                <div class="card-body">
                    @if(in_array($roleName, ['school', 'encoding_officer']))
                        <h5 class="card-title text-uppercase text-muted mb-0">Pending Special Orders</h5>
                        <span class="h2 font-weight-bold mb-0">{{ number_format($pendingSpecialOrdersCount ?? 0) }}</span>
                    @else
                        <h5 class="card-title text-uppercase text-muted mb-0">Active Stations</h5>
                        <span class="h2 font-weight-bold mb-0">{{ number_format($activeSchoolsCount) }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stats shadow mb-4">
                <div class="card-body">
                    <h5 class="card-title text-uppercase text-muted mb-0">Total Special Orders</h5>
                    <span class="h2 font-weight-bold mb-0">{{ number_format($totalSpecialOrdersVisible) }}</span>
                </div>
            </div>
        </div>
    </div>



    <div class="row">
        <div class="col-lg-4">
            <div class="card ppc-card mb-4">
                <div class="card-header bg-white border-0">
                    <h4 class="mb-0">Personnel Distribution</h4>
                </div>
                <div class="card-body">
                    @forelse($employeeTypeBreakdown as $row)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>{{ $row->employee_type }}</span>
                            <strong>{{ number_format($row->total) }}</strong>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No personnel data available.</p>
                    @endforelse
                </div>
            </div>

            @if($schoolSnapshot)
                <div class="card shadow mb-4">
                    <div class="card-header bg-white border-0">
                        <h4 class="mb-0">School Context</h4>
                    </div>
                    <div class="card-body">
                        <div class="small text-muted">School</div>
                        <div class="font-weight-bold mb-2">{{ $schoolSnapshot->name }}</div>

                        <div class="small text-muted">Governance Level</div>
                        <div class="mb-2">{{ $schoolSnapshot->governance_level ?? 'N/A' }}</div>

                        <div class="small text-muted">School Head</div>
                        <div>{{ $schoolSnapshot->head_name ?? 'N/A' }}</div>
                    </div>
                </div>
            @endif

            <div class="card shadow mb-4">
                <div class="card-header bg-white border-0">
                    <h4 class="mb-0">Quick Links</h4>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($quickLinks as $link)
                        <a href="{{ route($link['route']) }}" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="{{ $link['icon'] }} mr-2"></i>
                            <span>{{ $link['label'] }}</span>
                        </a>
                    @empty
                        <div class="list-group-item text-muted">No quick links available for your role.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Recent Personnel</h4>
                    @if(auth()->user()?->hasAnyRole(['admin', 'school', 'encoding_officer']))
                        <a href="{{ route('personnel.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table align-items-center table-flush mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Name</th>
                                <th>Employee Type</th>
                                <th>School</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPersonnel as $person)
                                @php
                                    $name = trim(($person->pdsMain->last_name ?? '') . ', ' . ($person->pdsMain->first_name ?? ''));
                                @endphp
                                <tr>
                                    <td>{{ $name !== ',' && $name !== '' ? $name : ('Personnel #' . $person->id) }}</td>
                                    <td>{{ $person->employee_type ?? 'N/A' }}</td>
                                    <td>{{ $person->school->name ?? 'N/A' }}</td>
                                    <td>
                                        @if($person->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No personnel to display.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Recent Special Orders</h4>
                    <a href="{{ route('specialorder.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-items-center table-flush mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>SO</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSpecialOrders as $so)
                                <tr>
                                    <td>{{ $so->so_number }}-{{ $so->series_year }}</td>
                                    <td>{{ $so->title }}</td>
                                    <td>{{ $so->type->name ?? 'N/A' }}</td>
                                    <td>
                                        @if($so->status === 'Approved')
                                            <span class="badge badge-success">Approved</span>
                                        @elseif($so->status === 'Rejected')
                                            <span class="badge badge-danger">Rejected</span>
                                        @else
                                            <span class="badge badge-warning">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No special orders to display.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header bg-white border-0">
                    <h4 class="mb-0">Recent Activity</h4>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($recentActivities as $activity)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>{{ $activity->action_type }}</strong>
                                    <span class="text-muted">in {{ $activity->module }}</span>
                                    <div class="small text-muted">{{ $activity->description }}</div>
                                </div>
                                <small class="text-muted">{{ optional($activity->created_at)->diffForHumans() }}</small>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-muted">No recent activity found.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection