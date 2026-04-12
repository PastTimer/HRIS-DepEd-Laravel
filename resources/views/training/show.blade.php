@extends('layouts.app')
@section('title', 'Training Details')

@section('content')
<div class="container-fluid mt-4">
    @php
        $isPersonnel = Auth::user() && Auth::user()->hasRole('personnel');
        $isPending = $training->verification_status === 'pending';
        $isVerified = $training->verification_status === 'verified';
        $canEdit = ($isPersonnel && $isPending) || (!$isPersonnel && $isVerified);
    @endphp

    <div class="row">
        <div class="col-xl-8 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
                    <h3 class="mb-0 text-primary">
                        <i class="ni ni-hat-3 mr-2"></i> Training Details
                    </h3>
                    <div>
                        @if($canEdit)
                        <a href="{{ route('training.edit', $training->id) }}" class="btn btn-sm btn-info mr-2">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </a>
                        @endif
                        <a href="{{ route('training.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Back
                        </a>
                    </div>
                </div>

                <div class="card-body bg-secondary">
                    <div class="form-group mb-3">
                        <label class="form-control-label text-muted">Title / Seminar Name</label>
                        <div class="font-weight-bold">{{ $training->title }}</div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="form-control-label text-muted">Total Hours</label>
                            <div class="font-weight-bold">{{ $training->hours }}</div>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="form-control-label text-muted">Start Date</label>
                            <div class="font-weight-bold">{{ $training->start_date ? \Carbon\Carbon::parse($training->start_date)->format('M d, Y') : 'N/A' }}</div>
                        </div>
                        <div class="col-md-4 form-group mb-3">
                            <label class="form-control-label text-muted">End Date</label>
                            <div class="font-weight-bold">{{ $training->end_date ? \Carbon\Carbon::parse($training->end_date)->format('M d, Y') : 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="form-control-label text-muted">Type</label>
                            <div class="font-weight-bold">{{ $training->type }}</div>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="form-control-label text-muted">Sponsor</label>
                            <div class="font-weight-bold">{{ $training->sponsor }}</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="form-control-label text-muted">Status</label>
                            <div>
                                @if($training->verification_status === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($training->verification_status === 'verified')
                                    <span class="badge badge-success">Approved</span>
                                @elseif($training->verification_status === 'rejected')
                                    <span class="badge badge-danger">Rejected</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="form-control-label text-muted">Rejection Reason</label>
                            <div class="font-weight-bold">{{ $training->rejection_reason ?: 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
