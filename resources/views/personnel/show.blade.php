@extends('layouts.app')
@section('title', 'Personnel Profile')

@section('content')
@php
    $pds = $personnel->pdsMain;
    $lastName = $pds->last_name ?? 'N/A';
    $firstName = $pds->first_name ?? '';
    $gender = $pds?->birth_sex ? ucfirst(strtolower($pds->birth_sex)) : 'N/A';
    $civilStatus = $pds?->civil_status ? ucfirst(strtolower($pds->civil_status)) : null;
@endphp
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-xl-4">
            <div class="card card-profile shadow mb-4">
                <div class="card-body pt-5 text-center">
                    <div class="mb-4">
                        <img src="{{ $personnel->profile_photo ? asset('storage/' . $personnel->profile_photo) : asset('uploads/default/defaultpic.png') }}" class="rounded-circle border shadow-sm" width="140" style="height: 140px; object-fit: cover;">
                    </div>
                    <h2 class="mb-0 text-dark">{{ $lastName }}, {{ $firstName }}</h2>
                    <p class="text-muted mb-3">{{ $personnel->position->title ?? 'N/A' }}</p>
                    
                    <div class="badge badge-pill badge-primary mb-4 px-4 py-2">
                        {{ $personnel->employee_type ?? 'N/A' }}
                    </div>

                    <div class="row text-left mt-2">
                        <div class="col-12 mb-2">
                            <small class="text-uppercase text-muted font-weight-bold">Station</small>
                            <div class="h5">{{ $personnel->school->name ?? 'Unassigned' }}</div>
                        </div>
                        <div class="col-6">
                            <small class="text-uppercase text-muted font-weight-bold">Gender</small>
                            <div class="h5">{{ $gender }}</div>
                        </div>
                        <div class="col-6">
                            <small class="text-uppercase text-muted font-weight-bold">Civil Status</small>
                            <div class="h5">{{ $civilStatus ?? '---' }}</div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('personnel.index') }}" class="btn btn-sm btn-secondary">Back to List</a>
                        @if($personnel->is_active)
                            <span class="text-success font-weight-bold"><i class="fas fa-circle mr-1"></i> Active</span>
                        @else
                            <span class="text-danger font-weight-bold"><i class="fas fa-circle mr-1"></i> Inactive</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card shadow">
                <div class="card-header bg-white p-0">
                    <ul class="nav nav-tabs nav-fill" id="profileTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active py-3" data-toggle="tab" href="#pds">
                                <i class="ni ni-single-02 mr-2"></i> PDS Info
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-3" data-toggle="tab" href="#inventory">
                                <i class="ni ni-archive-2 mr-2"></i> Inventory
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-3" data-toggle="tab" href="#history">
                                <i class="ni ni-hat-3 mr-2"></i> History
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body" style="min-height: 500px;">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="pds">
                            <h6 class="heading-small text-muted mb-4">Government Identifiers</h6>
                            <div class="row mb-4">
                                <div class="col-md-4"><strong>GSIS:</strong><br>{{ $pds->umid_id_number ?? 'N/A' }}</div>
                                <div class="col-md-4"><strong>SSS:</strong><br>{{ $pds->sss_number ?? 'N/A' }}</div>
                                <div class="col-md-4"><strong>TIN:</strong><br>{{ $pds->tin_number ?? 'N/A' }}</div>
                                <div class="col-md-4"><strong>Pag-Ibig:</strong><br>{{ $pds->pagibig_number ?? 'N/A' }}</div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <h6 class="heading-small text-muted mb-4">Contact & Address</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3"><strong>Email:</strong><br>{{ $pds->email_address ?? 'N/A' }}</div>
                                <div class="col-md-6 mb-3"><strong>Contact Number:</strong><br>{{ $pds->mobile ?? 'N/A' }}</div>
                                <div class="col-md-12"><strong>Residential Address:</strong><br>{{ $pds->residential_address ?? 'N/A' }}</div>
                            </div>



                            <!-- TESTING: FULL PERSONNEL DATA DUMP -->
                            <div style="background: #f6f6f6; border: 1px solid #000000; border-radius: 6px; padding: 1.5rem; margin-top: 2rem;">
                                <h5 class="mb-3">[TEST] PDS Data <br> pds_main = 1:1 to personnel <br> other tables = M:1 to personnel <br> personnel = not covered in pds</h5>
                                <div class="mb-3">
                                    <strong>personnel</strong>
                                    <ul class="mb-2">
                                        @foreach($personnel->getAttributes() as $key => $val)
                                            <li><code>{{ $key }}</code>: <span class="text-monospace">{{ is_scalar($val) ? $val : json_encode($val) }}</span></li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="mb-3">
                                    <strong>pds_main</strong>
                                    <ul class="mb-2">
                                        @if($pds)
                                            @foreach($pds->getAttributes() as $key => $val)
                                                <li><code>{{ $key }}</code>: <span class="text-monospace">{{ is_scalar($val) ? $val : json_encode($val) }}</span></li>
                                            @endforeach
                                        @else
                                            <li class="text-danger">No pds_main record found.</li>
                                        @endif
                                    </ul>
                                </div>
                                @foreach([
                                    'pdsChildren' => 'pds_children',
                                    'pdsEducation' => 'pds_education',
                                    'pdsEligibility' => 'pds_eligibility',
                                    'pdsWorkExperience' => 'pds_work_experience',
                                    'pdsTraining' => 'pds_training',
                                    'pdsReferences' => 'pds_references',
                                    'pdsSubmissions' => 'pds_submissions',
                                ] as $relation => $label)
                                    <div class="mb-3">
                                        <strong>{{ $label }}</strong>
                                        <ul class="mb-2">
                                            @forelse($personnel->$relation as $row)
                                                <li>
                                                    <ul>
                                                        @foreach($row->getAttributes() as $key => $val)
                                                            <li><code>{{ $key }}</code>: <span class="text-monospace">{{ is_scalar($val) ? $val : json_encode($val) }}</span></li>
                                                        @endforeach
                                                    </ul>
                                                </li>
                                            @empty
                                                <li class="text-muted">No records.</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                @endforeach
                            </div>


                            
                        </div>

                        <div class="tab-pane fade" id="inventory">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="mb-0">Assigned Assets</h4>
                                <span class="badge badge-info">{{ $personnel->equipment->count() }} Items</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-items-center table-flush">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Property No</th>
                                            <th>Item Name</th>
                                            <th>Condition</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($personnel->equipment as $item)
                                        <tr>
                                            <td class="font-weight-bold">{{ $item->property_no }}</td>
                                            <td>{{ $item->item }}<br><small class="text-muted">{{ $item->brand_manufacturer }}</small></td>
                                            <td><span class="badge badge-success">{{ $item->equipment_condition }}</span></td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" class="text-center py-5 text-muted">No equipment accountability found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="history">
                            <div class="row">
                                <div class="col-lg-6">
                                    <h4 class="mb-3 text-primary"><i class="ni ni-books mr-2"></i> Seminar History</h4>
                                    <div class="timeline timeline-one-side">
                                        @forelse($personnel->trainings as $tr)
                                        <div class="timeline-block mb-3">
                                            <span class="timeline-step badge-success"><i class="ni ni-check-bold"></i></span>
                                            <div class="timeline-content">
                                                <small class="text-muted font-weight-bold">{{ $tr->date_from }}</small>
                                                <h5 class="mt-1 mb-0">{{ $tr->title }}</h5>
                                                <p class="text-sm mt-1 mb-0 text-muted">{{ $tr->hours }} Hours</p>
                                            </div>
                                        </div>
                                        @empty
                                        <p class="text-muted small">No training records found.</p>
                                        @endforelse
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <h4 class="mb-3 text-danger"><i class="ni ni-paper-diploma mr-2"></i> Special Orders</h4>
                                    <div class="list-group list-group-flush">
                                        @forelse($personnel->specialOrders as $so)
                                        <div class="list-group-item px-0">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <h5 class="mb-0">SO #{{ $so->so_no }}-{{ $so->series_year }}</h5>
                                                    <small class="text-muted">{{ Str::limit($so->title, 40) }}</small>
                                                </div>
                                                <div class="col-auto">
                                                    <span class="badge badge-pill badge-secondary">{{ $so->type }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        @empty
                                        <p class="text-muted small">No special orders found.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection