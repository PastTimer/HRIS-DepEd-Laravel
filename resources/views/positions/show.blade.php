@extends('layouts.app')
@section('title', 'Position: ' . $position->title)

@section('content')
<style>
    /* Legacy-style tight formatting for the personnel list */
    .table-legacy thead th {
        background-color: #f6f9fc;
        color: #8898aa;
        text-transform: uppercase;
        font-size: .65rem;
        letter-spacing: 1px;
        padding: 0.75rem;
    }
    .table-legacy tbody td {
        padding: 0.5rem 0.75rem;
        font-size: 0.85rem;
    }
    .clickable-row { cursor: pointer; }
    .clickable-row:hover { background-color: #f8f9fe !important; }
</style>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-xl-12">
            <div class="card shadow">
                <div class="card-header border-0 bg-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="text-uppercase text-muted ls-1 mb-1">Position Overview</h6>
                            <h3 class="mb-0">{{ strtoupper($position->title) }}</h3>
                        </div>
                        <div class="col text-right">
                            <span class="badge badge-primary px-3 py-2">{{ $employees->total() }} Total Personnel</span>
                            <a href="/positions" class="btn btn-sm btn-secondary ml-2">Back to List</a>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-items-center table-flush table-hover table-legacy">
                        <thead>
                            <tr>
                                <th>Personnel Name</th>
                                <th>Employee ID</th>
                                <th>Current School</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $emp)
                            <tr class="clickable-row" onclick="window.location='/employees/{{ $emp->id }}';">
                                <td>
                                    <span class="text-dark font-weight-bold">{{ strtoupper($emp->last_name) }}</span>, {{ $emp->first_name }}
                                </td>
                                <td>{{ $emp->employee_id ?? 'N/A' }}</td>
                                <td>
                                    <i class="fas fa-school mr-2 text-muted"></i>
                                    {{ $emp->school->name ?? 'Unassigned' }}
                                </td>
                                <td>
                                    @if($emp->is_active)
                                        <span class="badge badge-dot mr-4">
                                            <i class="bg-success"></i> <span class="status">Active</span>
                                        </span>
                                    @else
                                        <span class="badge badge-dot mr-4">
                                            <i class="bg-danger"></i> <span class="status">Inactive</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="/employees/{{ $emp->id }}" class="btn btn-sm btn-icon-only text-light" title="View Profile">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-user-slash fa-3x mb-3"></i>
                                        <p>No personnel currently assigned to this position.</p>
                                    </div>
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