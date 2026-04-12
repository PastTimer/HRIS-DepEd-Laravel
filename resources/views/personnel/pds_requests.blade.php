@extends('layouts.app')
@section('title', 'PDS Edit Requests')

@section('content')
<div class="container-fluid mt-4" data-ajax-content>
    <div class="row">
        <div class="col">
            <div class="card shadow">
                <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
                    <h3 class="mb-0"><i class="fas fa-clipboard-check mr-2 text-warning"></i> PDS Edit Requests</h3>
                    <form action="{{ route('pds.requests.index') }}" method="GET" class="mb-0">
                        <div class="input-group input-group-sm" style="min-width: 260px;">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name, emp id, status...">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                                @if(request('search'))
                                    <a href="{{ route('pds.requests.index') }}" class="btn btn-outline-danger"><i class="fas fa-times"></i></a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>

                @if(session('success'))
                    <div class="alert alert-success m-3 mb-0">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger m-3 mb-0">{{ session('error') }}</div>
                @endif

                <div class="table-responsive">
                    <table class="table align-items-center table-flush table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Personnel</th>
                                <th>Submitted By</th>
                                <th class="text-center">Submitted At</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Reviewed By</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $requestItem)
                                @php
                                    $fullName = trim(($requestItem->personnel->pdsMain->last_name ?? '') . ', ' . ($requestItem->personnel->pdsMain->first_name ?? ''));
                                    $canDelete = Auth::user()?->hasRole('personnel')
                                        && (int) Auth::id() === (int) ($requestItem->submitted_by ?? 0)
                                        && strtoupper((string) $requestItem->status) === 'SUBMITTED';
                                @endphp
                                <tr>
                                    <td>#{{ $requestItem->id }}</td>
                                    <td>
                                        <div class="font-weight-bold">{{ $fullName !== ',' ? $fullName : ($requestItem->personnel->emp_id ?? '--') }}</div>
                                        <small class="text-muted">EMP ID: {{ $requestItem->personnel->emp_id ?? '--' }}</small>
                                    </td>
                                    <td>{{ $requestItem->submitter->username ?? '--' }}</td>
                                    <td class="text-center">{{ optional($requestItem->submitted_at)->format('Y-m-d H:i') }}</td>
                                    <td class="text-center">
                                        @if($requestItem->status === 'APPROVED')
                                            <span class="badge badge-success">APPROVED</span>
                                        @elseif($requestItem->status === 'REJECTED')
                                            <span class="badge badge-danger">REJECTED</span>
                                        @else
                                            <span class="badge badge-warning">SUBMITTED</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $requestItem->reviewer->username ?? '-' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('pds.requests.show', $requestItem) }}" class="btn btn-sm btn-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($canDelete)
                                            <form action="{{ route('pds.requests.destroy', $requestItem) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this submitted PDS request? This cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete Request">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">No PDS edit requests found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer py-4 d-flex justify-content-center">
                    {{ $requests->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
