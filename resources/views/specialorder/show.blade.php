@extends('layouts.app')
@section('title', 'Special Order Details')
@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-xl-8">
            <div class="card shadow mb-4">
                <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
                    <h3 class="mb-0 text-primary"><i class="fas fa-file-alt mr-2"></i> Special Order Details</h3>
                    <div>
                        @php
                            $isPersonnel = Auth::user() && Auth::user()->hasRole('personnel');
                            $isPending = $specialorder->status === 'Pending';
                            $isApproved = $specialorder->status === 'Approved';
                            $canEdit = ($isPersonnel && $isPending) || (!$isPersonnel && $isApproved);
                        @endphp
                        @if($canEdit)
                        <a href="{{ route('specialorder.edit', $specialorder) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </a>
                        @endif
                        <a href="{{ route('specialorder.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body bg-secondary">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-control-label text-muted">SO Number</label>
                            <div class="font-weight-bold">{{ $specialorder->so_number }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-control-label text-muted">Series Year</label>
                            <div class="font-weight-bold">{{ $specialorder->series_year }}</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-control-label text-muted">Title</label>
                        <div class="font-weight-bold">{{ $specialorder->title }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-control-label text-muted">Description</label>
                        <div>{{ $specialorder->description ?: 'N/A' }}</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-control-label text-muted">Type</label>
                            <div class="font-weight-bold">{{ $specialorder->type->name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-control-label text-muted">Status</label>
                            <div>
                                @if($specialorder->status === 'Approved')
                                    <span class="badge badge-success">Approved</span>
                                @elseif($specialorder->status === 'Rejected')
                                    <span class="badge badge-danger">Rejected</span>
                                @else
                                    <span class="badge badge-warning">Pending</span>
                                @endif
                            </div>
                            @if($specialorder->status === 'Rejected' && $specialorder->rejection_reason)
                                <div class="mt-2">
                                    <label class="form-control-label text-danger">Reason for Rejection:</label>
                                    <div class="text-danger">{{ $specialorder->rejection_reason }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-control-label text-muted">Created By</label>
                            <div class="font-weight-bold">{{ $specialorder->creator->username ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-control-label text-muted">Approved By</label>
                            <div class="font-weight-bold">{{ $specialorder->approver->username ?? 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-control-label text-muted">Created At</label>
                            <div class="font-weight-bold">{{ optional($specialorder->created_at)->format('Y-m-d H:i') }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-control-label text-muted">Approved At</label>
                            <div class="font-weight-bold">{{ optional($specialorder->approved_at)->format('Y-m-d H:i') ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card shadow">
                <div class="card-header border-0 bg-white">
                    <h3 class="mb-0">Included Personnel</h3>
                </div>
                <div class="table-responsive">
                    <table class="table align-items-center table-flush">
                        <thead class="thead-light">
                            <tr>
                                <th>Name</th>
                                <th class="text-center">Units</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($specialorder->personnel as $personnel)
                                @php($pds = $personnel->pdsMain)
                                <tr>
                                    <td>{{ $pds->last_name ?? 'N/A' }}, {{ $pds->first_name ?? '' }}</td>
                                    <td class="text-center">{{ number_format((float) ($personnel->pivot->units ?? 0), 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">No personnel linked.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
