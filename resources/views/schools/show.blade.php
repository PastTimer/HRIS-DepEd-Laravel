@extends('layouts.app')
@section('title', 'Station Profile: ' . $school->name)

@section('content')
<style>
    .label-caps { font-size: 0.65rem; text-transform: uppercase; color: #adb5bd; font-weight: 700; letter-spacing: .025em; }
    .table-legacy thead th { background-color: #f6f9fc; color: #8898aa; text-transform: uppercase; font-size: .65rem; letter-spacing: 1px; padding: 0.75rem; }
    .table-legacy tbody td { padding: 0.5rem 0.75rem; font-size: 0.85rem; }
</style>

<div class="container-fluid mt-4">
    <div class="card shadow">
        <div class="card-header bg-white border-0 pb-0">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0"><i class="fas fa-school mr-2 text-primary"></i> {{ $school->name }}</h3>
                <a href="/schools" class="btn btn-sm btn-secondary">Back to List</a>
            </div>
            <ul class="nav nav-pills nav-fill flex-column flex-sm-row" id="schoolTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active py-3" data-toggle="tab" href="#school_profile"><i class="ni ni-building mr-2"></i> STAKEHOLDER PROFILE</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3" data-toggle="tab" href="#personnel_list"><i class="ni ni-badge mr-2"></i> STATION PERSONNEL</a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content">
                
                <div class="tab-pane fade show active" id="school_profile">
                    <div class="text-right mb-4">
                        <a href="/schools/{{ $school->id }}/profile/edit" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit mr-1"></i> Edit Stakeholder Data
                        </a>
                    </div>

                    <h6 class="heading-small text-muted mb-4">General Information</h6>
                    <div class="row mb-4 pl-lg-4">
                        <div class="col-md-3"><div class="label-caps">School ID</div><div class="h5">{{ $school->school_id }}</div></div>
                        <div class="col-md-3"><div class="label-caps">Governance Level</div><div class="h5">{{ $school->governance_level ?? '---' }}</div></div>
                        <div class="col-md-3"><div class="label-caps">RO / SDO</div><div class="h5">{{ $school->ro ?? '---' }} / {{ $school->sdo ?? '---' }}</div></div>
                        <div class="col-md-3"><div class="label-caps">District / PSGC</div><div class="h5">{{ $school->district ? $school->district->name : '---' }} / {{ $school->psgc ?? '---' }}</div></div>
                    </div>

                    <hr class="my-4">

                    <div class="row">
                        <div class="col-lg-8 border-right">
                            <h6 class="heading-small text-muted mb-3">Location & Access</h6>
                            <div class="row mb-4 pl-lg-2">
                                <div class="col-md-12 mb-3">
                                    <div class="label-caps">Full Address</div>
                                    <div class="text-dark">{{ $school->address_street ?? '' }} {{ $school->address_barangay ?? '' }}, {{ $school->address_city ?? '' }}, {{ $school->address_province ?? '' }}</div>
                                </div>
                                <div class="col-md-4"><div class="label-caps">Coordinates</div><div>{{ $school->coordinates_lat ?? '0' }}, {{ $school->coordinates_long ?? '0' }}</div></div>
                                <div class="col-md-4"><div class="label-caps">Travel Time</div><div>{{ $school->travel_time_min ?? '0' }} mins to center</div></div>
                                <div class="col-md-4"><div class="label-caps">Access Paths</div><div><span class="badge badge-secondary">{{ $school->access_paths ?? 'N/A' }}</span></div></div>
                            </div>

                            <h6 class="heading-small text-muted mb-3">Communication Details</h6>
                            <div class="row mb-4 pl-lg-2">
                                <div class="col-md-4"><div class="label-caps">Mobile 1</div><div>{{ $school->contact_mobile1 ?? '---' }}</div></div>
                                <div class="col-md-4"><div class="label-caps">Mobile 2</div><div>{{ $school->contact_mobile2 ?? '---' }}</div></div>
                                <div class="col-md-4"><div class="label-caps">Landline</div><div>{{ $school->contact_landline ?? '---' }}</div></div>
                            </div>
                        </div>

                        <div class="col-lg-4 pl-lg-4">
                            <h6 class="heading-small text-muted mb-3">Key Personnel</h6>
                            <div class="p-3 bg-secondary rounded mb-3">
                                <div class="label-caps mb-1">School Head</div>
                                <strong class="text-dark">{{ $school->head_name ?? 'NOT SET' }}</strong><br>
                                <small class="text-muted d-block">{{ $school->head_position ?? '---' }}</small>
                                <small class="text-primary">{{ $school->head_email ?? '' }}</small><br>
                            </div>
                            <div class="p-3 bg-secondary rounded mb-3">
                                <div class="label-caps mb-1">Admin / Inventory Clerk</div>
                                <strong>{{ $school->admin_name ?? 'NOT SET' }}</strong><br>
                                <small class="text-muted">{{ $school->admin_mobile ?? '---' }}</small>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row pl-lg-4">
                        <div class="col-md-12 mb-4">
                            <h6 class="heading-small text-muted mb-2">Nearby Institutions</h6>
                            @if(!empty($school->nearby_institutions))
                                @foreach(explode(', ', $school->nearby_institutions) as $inst)
                                    <span class="badge badge-pill badge-outline-primary mr-2 mb-2">{{ $inst }}</span>
                                @endforeach
                            @else
                                <span class="text-muted italic">No nearby institutions recorded.</span>
                            @endif
                        </div>
                        <div class="col-md-12">
                            <label class="label-caps">General Notes</label>
                            <p class="text-sm text-muted font-italic">{{ $school->notes ?? 'None' }}</p>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="personnel_list">
                    <div class="table-responsive">
                        <table class="table align-items-center table-flush table-hover table-legacy">
                            <thead>
                                <tr>
                                    <th>Personnel Name</th>
                                    <th>Position</th>
                                    <th>Employee ID</th>
                                    <th class="text-right">Contact</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($personnel as $emp)
                                <tr class="clickable-row" style="cursor: pointer;" onclick="window.location='/employees/{{ $emp->id }}';">
                                    <td><span class="text-dark font-weight-bold">{{ strtoupper($emp->last_name) }}</span>, {{ $emp->first_name }}</td>
                                    <td>{{ $emp->position }}</td>
                                    <td>{{ $emp->employee_id }}</td>
                                    <td class="text-right">{{ $emp->contact_no ?? '---' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-5">No personnel assigned.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $personnel->links('pagination::bootstrap-4') }}</div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection