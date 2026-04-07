@extends('layouts.app')
@section('title', 'Equipment Details')
@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-xl-10 col-lg-12 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
                    <h3 class="mb-0 text-primary">
                        <i class="fas fa-desktop mr-2"></i> Equipment Details
                    </h3>
                    <div>
                        <a href="{{ route('equipment.edit', $equipment->id) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </a>
                        <a href="{{ route('equipment.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Inventory
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted mb-1 d-block">Property No.</label>
                            <strong>{{ $equipment->property_no ?? 'N/A' }}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted mb-1 d-block">QR Code</label>
                            <strong>{{ $equipment->qr_code ?? 'N/A' }}</strong>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="text-muted mb-1 d-block">Item</label>
                            <strong>{{ $equipment->item ?? 'N/A' }}</strong>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted mb-1 d-block">Brand / Manufacturer</label>
                            <strong>{{ $equipment->brand_manufacturer ?? 'N/A' }}</strong>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted mb-1 d-block">Model</label>
                            <strong>{{ $equipment->model ?? 'N/A' }}</strong>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="text-muted mb-1 d-block">Assigned School</label>
                            <strong>{{ optional($equipment->school)->name ?? 'N/A' }}</strong>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted mb-1 d-block">Accountable Officer</label>
                            <strong>
                                {{ optional(optional($equipment->accountableOfficer)->pdsMain)->last_name ?? 'N/A' }},
                                {{ optional(optional($equipment->accountableOfficer)->pdsMain)->first_name ?? '' }}
                            </strong>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted mb-1 d-block">Custodian</label>
                            <strong>
                                {{ optional(optional($equipment->custodian)->pdsMain)->last_name ?? 'N/A' }},
                                {{ optional(optional($equipment->custodian)->pdsMain)->first_name ?? '' }}
                            </strong>
                        </div>
                    </div>

                    <hr>

                    <h5 class="text-primary mb-3">
                        <i class="fas fa-exchange-alt mr-2"></i>Movement History
                    </h5>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Document Type</th>
                                    <th>Document No.</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($movements as $movement)
                                    <tr>
                                        <td>{{ optional($movement->movement_date)->format('Y-m-d') ?? 'N/A' }}</td>
                                        <td>
                                            {{ optional(optional($movement->fromPersonnel)->pdsMain)->last_name ?? 'N/A' }},
                                            {{ optional(optional($movement->fromPersonnel)->pdsMain)->first_name ?? '' }}
                                        </td>
                                        <td>
                                            {{ optional(optional($movement->toPersonnel)->pdsMain)->last_name ?? 'N/A' }},
                                            {{ optional(optional($movement->toPersonnel)->pdsMain)->first_name ?? '' }}
                                        </td>
                                        <td>{{ $movement->document_type ?? 'N/A' }}</td>
                                        <td>{{ $movement->document_number ?? 'N/A' }}</td>
                                        <td>{{ $movement->remarks ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No movement history available yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
