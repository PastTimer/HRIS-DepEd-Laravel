@extends('layouts.app')
@section('title', 'Personnel Profile')

@section('content')
@php
    $pds = $personnel->pdsMain;
    $lastName = $pds->last_name ?? 'N/A';
    $firstName = $pds->first_name ?? '';
    $gender = $pds?->birth_sex ? ucfirst(strtolower($pds->birth_sex)) : 'N/A';
    $civilStatus = $pds?->civil_status ? ucfirst(strtolower($pds->civil_status)) : null;
    $canManageServiceRecords = auth()->user()?->hasAnyRole(['admin', 'school']);
    $canExportServiceRecords = auth()->user()?->hasAnyRole(['admin', 'school', 'encoding_officer', 'personnel']);
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
                        <a href="{{ route('personnel.pds.export', $personnel->id) }}" class="btn btn-sm btn-primary">Export PDS PDF</a>
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
                            <a class="nav-link py-3" data-toggle="tab" href="#service-records">
                                <i class="ni ni-badge mr-2"></i> Service Records
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

                        <div class="tab-pane fade" id="service-records">
                            @if(session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="mb-0">Service Records</h4>
                                <div>
                                    @if($canManageServiceRecords)
                                        <button class="btn btn-primary mr-2" data-toggle="modal" data-target="#addServiceRecordModal">
                                            <i class="fas fa-plus"></i> Add Record
                                        </button>
                                    @endif
                                    @if($canExportServiceRecords)
                                        <a href="{{ route('service-records.export.xlsx-format', $personnel->id) }}" class="btn btn-outline-success">
                                            <i class="fas fa-file-excel"></i> Export
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <!-- Add Service Record Modal -->
                            @if($canManageServiceRecords)
                            <div class="modal fade" id="addServiceRecordModal" tabindex="-1" role="dialog" aria-labelledby="addServiceRecordModalLabel" aria-hidden="true">
                              <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="addServiceRecordModalLabel">Add Service Record</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                      <span aria-hidden="true">&times;</span>
                                    </button>
                                  </div>
                                  <form action="{{ route('service-records.store', $personnel->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                      <div class="row">
                                        <div class="col-md-3 form-group">
                                            <label>Date From <span class="text-danger">*</span></label>
                                            <input type="date" name="date_from" class="form-control" value="{{ old('date_from', now()->toDateString()) }}" required>
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Date To</label>
                                            <input type="date" name="date_to" class="form-control" value="{{ old('date_to') }}">
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Designation <span class="text-danger">*</span></label>
                                            <select name="position_id" class="form-control" required>
                                                <option value="" disabled {{ old('position_id') ? '' : 'selected' }}>Select Position</option>
                                                @foreach($positions as $position)
                                                    <option value="{{ $position->id }}" {{ (string) old('position_id', $personnel->position_id) === (string) $position->id ? 'selected' : '' }}>{{ $position->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Status <span class="text-danger">*</span></label>
                                            <select name="status" class="form-control" required>
                                                @foreach($employeeTypes as $type)
                                                    <option value="{{ $type }}" {{ old('status', $personnel->employee_type) === $type ? 'selected' : '' }}>{{ $type }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                      </div>
                                      <div class="row">
                                        <div class="col-md-4 form-group">
                                            <label>Station <span class="text-danger">*</span></label>
                                            <select name="school_id" class="form-control" required>
                                                <option value="" disabled {{ old('school_id') ? '' : 'selected' }}>Select School</option>
                                                @foreach($schools as $school)
                                                    <option value="{{ $school->id }}" {{ (string) old('school_id', $personnel->deployed_school_id ?? $personnel->assigned_school_id) === (string) $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Salary</label>
                                            <input type="number" step="0.01" name="salary" class="form-control" value="{{ old('salary', $personnel->salary_actual) }}">
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Branch</label>
                                            <input type="text" name="branch" class="form-control" value="{{ old('branch', $personnel->branch) }}">
                                        </div>
                                        <div class="col-md-2 form-group">
                                            <label>LV/Abs WO Pay</label>
                                            <input type="text" name="lv_abs_wo_pay" class="form-control" value="{{ old('lv_abs_wo_pay') }}">
                                        </div>
                                      </div>
                                    </div>
                                    <div class="modal-footer">
                                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                      <button type="submit" class="btn btn-primary">Add Record</button>
                                    </div>
                                  </form>
                                </div>
                              </div>
                            </div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-items-center">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Date From</th>
                                            <th>Date To</th>
                                            <th>Designation</th>
                                            <th>Status</th>
                                            <th>Salary</th>
                                            <th>Station</th>
                                            <th>Branch</th>
                                            <th>LV/Abs WO Pay</th>
                                            @if($canManageServiceRecords)
                                                <th style="width: 180px;">Actions</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($personnel->serviceRecords->sortByDesc('date_from') as $record)
                                            <tr>
                                                <td>{{ $record->date_from }}</td>
                                                <td>{{ $record->date_to ?? '---' }}</td>
                                                <td>{{ $record->position->title ?? 'N/A' }}</td>
                                                <td>{{ $record->status }}</td>
                                                <td>{{ $record->salary }}</td>
                                                <td>{{ $record->school->name ?? 'N/A' }}</td>
                                                <td>{{ $record->branch ?? '---' }}</td>
                                                <td>{{ $record->lv_abs_wo_pay ?? '---' }}</td>
                                                @if($canManageServiceRecords)
                                                <td>
                                                    <button class="btn btn-sm btn-warning" type="button" data-toggle="modal" data-target="#editServiceRecordModal-{{ $record->id }}">
                                                        Edit
                                                    </button>
                                                    <form action="{{ route('service-records.destroy', [$personnel->id, $record->id]) }}" method="POST" class="d-inline-block">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this service record?')">Delete</button>
                                                    </form>
                                                </td>
                                                @endif
                                            </tr>

                                            <!-- Edit Service Record Modal -->
                                            @if($canManageServiceRecords)
                                            <div class="modal fade" id="editServiceRecordModal-{{ $record->id }}" tabindex="-1" role="dialog" aria-labelledby="editServiceRecordModalLabel-{{ $record->id }}" aria-hidden="true">
                                              <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                  <div class="modal-header">
                                                    <h5 class="modal-title" id="editServiceRecordModalLabel-{{ $record->id }}">Edit Service Record</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                      <span aria-hidden="true">&times;</span>
                                                    </button>
                                                  </div>
                                                  <form action="{{ route('service-records.update', [$personnel->id, $record->id]) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                      <div class="row">
                                                        <div class="col-md-3 form-group">
                                                            <label>Date From <span class="text-danger">*</span></label>
                                                            <input type="date" name="date_from" class="form-control" value="{{ $record->date_from }}" required>
                                                        </div>
                                                        <div class="col-md-3 form-group">
                                                            <label>Date To</label>
                                                            <input type="date" name="date_to" class="form-control" value="{{ $record->date_to }}">
                                                        </div>
                                                        <div class="col-md-3 form-group">
                                                            <label>Designation <span class="text-danger">*</span></label>
                                                            <select name="position_id" class="form-control" required>
                                                                @foreach($positions as $position)
                                                                    <option value="{{ $position->id }}" {{ (string) $record->position_id === (string) $position->id ? 'selected' : '' }}>{{ $position->title }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3 form-group">
                                                            <label>Status <span class="text-danger">*</span></label>
                                                            <select name="status" class="form-control" required>
                                                                @foreach($employeeTypes as $type)
                                                                    <option value="{{ $type }}" {{ $record->status === $type ? 'selected' : '' }}>{{ $type }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                      </div>
                                                      <div class="row">
                                                        <div class="col-md-4 form-group">
                                                            <label>Station <span class="text-danger">*</span></label>
                                                            <select name="school_id" class="form-control" required>
                                                                @foreach($schools as $school)
                                                                    <option value="{{ $school->id }}" {{ (string) $record->school_id === (string) $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3 form-group">
                                                            <label>Salary</label>
                                                            <input type="number" step="0.01" name="salary" class="form-control" value="{{ $record->salary }}">
                                                        </div>
                                                        <div class="col-md-3 form-group">
                                                            <label>Branch</label>
                                                            <input type="text" name="branch" class="form-control" value="{{ $record->branch }}">
                                                        </div>
                                                        <div class="col-md-2 form-group">
                                                            <label>LV/Abs WO Pay</label>
                                                            <input type="text" name="lv_abs_wo_pay" class="form-control" value="{{ $record->lv_abs_wo_pay }}">
                                                        </div>
                                                      </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                      <button type="submit" class="btn btn-success">Save Changes</button>
                                                    </div>
                                                  </form>
                                                </div>
                                              </div>
                                            </div>
                                            @endif
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-4">No service records yet.</td>
                                            </tr>
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
                                        @forelse($personnel->pdsTraining as $tr)
                                        <div class="timeline-block mb-3">
                                            <span class="timeline-step badge-success"><i class="ni ni-check-bold"></i></span>
                                            <div class="timeline-content">
                                                <small class="text-muted font-weight-bold">{{ $tr->start_date }}</small>
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
                                        @php
                                            $approvedSOs = $personnel->specialOrders->where('status', 'Approved');
                                        @endphp
                                        @forelse($approvedSOs as $so)
                                        <div class="list-group-item px-0">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <h5 class="mb-0">SO #{{ $so->so_number ?? $so->so_no }}-{{ $so->series_year }}</h5>
                                                    <small class="text-muted">{{ Str::limit($so->title, 40) }}</small>
                                                </div>
                                                <div class="col-auto">
                                                    <span class="badge badge-pill badge-primary">{{ $so->type->name ?? ($so->type ?? 'N/A') }}</span>
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

<script>
    (function () {
        if (window.location.hash) {
            var trigger = document.querySelector('a.nav-link[href="' + window.location.hash + '"]');
            if (trigger && window.jQuery) {
                window.jQuery(trigger).tab('show');
            }
        }
    })();
</script>
@endsection