@extends('layouts.app')
@section('title', 'Edit ISP Connection')

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow mb-4">
        <div class="card-header bg-white border-0">
            <div class="row align-items-center">
                <div class="col-8">
                    <h3 class="mb-0">Edit Connection: {{ $isp->provider }}</h3>
                </div>
                <div class="col-4 text-right">
                    <a href="{{ route('isp.index') }}" class="btn btn-sm btn-secondary">Back to List</a>
                </div>
            </div>
        </div>
        
        <div class="card-body bg-secondary">
            <form action="{{ route('isp.update', $isp->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <h6 class="heading-small text-muted mb-4">Connection Information</h6>
                <div class="pl-lg-4">
                    <div class="row">
                        <div class="col-lg-6 form-group">
                            <label class="form-control-label">School</label>
                            
                            <input type="hidden" name="school_id" value="{{ $isp->school_id }}">
                            
                            <input type="text" class="form-control bg-white" value="{{ $isp->school->name ?? 'Unknown School' }}" readonly>
                            <small class="text-muted">The school assignment cannot be changed once created.</small>
                        </div>
                        <div class="col-lg-6 form-group">
                            <label class="form-control-label">Internet Service Provider</label>
                            <select name="provider" class="form-control" required>
                                @foreach(['PLDT', 'Globe', 'Smart', 'Converge', 'Starlink', 'SkyCable', 'DITO', 'Others'] as $provider)
                                    <option value="{{ $provider }}" {{ $isp->provider == $provider ? 'selected' : '' }}>{{ $provider }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4 form-group">
                            <label class="form-control-label">Account No. / Reference No.</label>
                            <input type="text" name="account_no" class="form-control" value="{{ $isp->account_no }}">
                        </div>
                        <div class="col-lg-4 form-group">
                            <label class="form-control-label">Connection Type</label>
                            <select name="internet_type" class="form-control">
                                @foreach(['Fiber' => 'Fiber Optics', 'DSL' => 'DSL', 'Cable' => 'Cable Internet', 'Wireless/LTE' => 'Fixed Wireless / LTE', 'Satellite' => 'Satellite', 'Mobile Data' => 'Mobile Data / Pocket WiFi', 'Leased Line' => 'Leased Line'] as $val => $label)
                                    <option value="{{ $val }}" {{ $isp->internet_type == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4 form-group">
                            <label class="form-control-label">Subscription Type</label>
                            <select name="subscription_type" class="form-control">
                                <option value="Postpaid" {{ $isp->subscription_type == 'Postpaid' ? 'selected' : '' }}>Postpaid</option>
                                <option value="Prepaid" {{ $isp->subscription_type == 'Prepaid' ? 'selected' : '' }}>Prepaid</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-3 form-group">
                            <label class="form-control-label">Purpose of Subscription</label>
                            <input type="text" name="purpose" class="form-control" value="{{ $isp->purpose }}">
                        </div>
                        <div class="col-lg-3 form-group">
                            <label class="form-control-label">Mode of Acquisition</label>
                            <input type="text" name="acquisition_mode" class="form-control" value="{{ $isp->acquisition_mode }}">
                        </div>
                        <div class="col-lg-3 form-group">
                            <label class="form-control-label">Donor</label>
                            <input type="text" name="donor" class="form-control" value="{{ $isp->donor }}">
                        </div>
                        <div class="col-lg-3 form-group">
                            <label class="form-control-label">Source of Funds</label>
                            <input type="text" name="fund_source" class="form-control" value="{{ $isp->fund_source }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6 form-group">
                            <label class="form-control-label">Plan Bandwidth / Max Speed (Mbps)</label>
                            <input type="text" name="plan_speed" class="form-control" value="{{ $isp->plan_speed }}">
                        </div>
                        <div class="col-lg-6 form-group">
                            <label class="form-control-label">Minimum Guaranteed Speed (Mbps)</label>
                            <input type="text" name="min_speed" class="form-control" value="{{ $isp->min_speed }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4 form-group">
                            <label class="form-control-label">Monthly Cost (MRC)</label>
                            <input type="number" step="0.01" name="monthly_mrc" class="form-control" value="{{ $isp->monthly_mrc }}">
                        </div>
                        <div class="col-lg-4 form-group">
                            <label class="form-control-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="Active" {{ $isp->status == 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ $isp->status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="Pending" {{ $isp->status == 'Pending' ? 'selected' : '' }}>Pending Installation</option>
                            </select>
                        </div>
                        <div class="col-lg-4 form-group">
                            <label class="form-control-label">Area Coverage</label>
                            <select name="area_coverage" class="form-control">
                                <option value="Admin Office" {{ $isp->area_coverage == 'Admin Office' ? 'selected' : '' }}>Admin Office Only</option>
                                <option value="Faculty" {{ $isp->area_coverage == 'Faculty' ? 'selected' : '' }}>Faculty Room</option>
                                <option value="Computer Lab" {{ $isp->area_coverage == 'Computer Lab' ? 'selected' : '' }}>Computer Lab</option>
                                <option value="Classrooms" {{ $isp->area_coverage == 'Classrooms' ? 'selected' : '' }}>Classrooms</option>
                                <option value="Whole School" {{ $isp->area_coverage == 'Whole School' ? 'selected' : '' }}>Whole School (Campus-wide)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4 form-group">
                            <label class="form-control-label">Installation Date</label>
                            <input type="date" name="installation_date" class="form-control" value="{{ optional($isp->installation_date)->format('Y-m-d') }}">
                        </div>
                        <div class="col-lg-4 form-group">
                            <label class="form-control-label">Contract End Date</label>
                            <input type="date" name="contract_end_date" class="form-control" value="{{ optional($isp->contract_end_date)->format('Y-m-d') }}">
                        </div>
                        <div class="col-lg-4 form-group">
                            <label class="form-control-label">IP Configuration</label>
                            <select name="ip_type" class="form-control">
                                <option value="Dynamic" {{ $isp->ip_type == 'Dynamic' ? 'selected' : '' }}>Dynamic IP</option>
                                <option value="Static" {{ $isp->ip_type == 'Static' ? 'selected' : '' }}>Static IP</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6 form-group">
                            <label class="form-control-label">Public IP Address</label>
                            <input type="text" name="public_ip" class="form-control" value="{{ $isp->public_ip }}" placeholder="x.x.x.x">
                        </div>
                        <div class="col-lg-6 form-group">
                            <label class="form-control-label">Package Inclusions</label>
                            <textarea name="package_inclusion" rows="2" class="form-control">{{ $isp->package_inclusion }}</textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-control-label">Remarks</label>
                        <textarea name="remarks" rows="2" class="form-control">{{ $isp->remarks }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-lg-3 form-group">
                            <label class="form-control-label">Access Points (Count)</label>
                            <input type="number" name="access_points_count" class="form-control" min="0" value="{{ $isp->access_points_count }}">
                        </div>
                        <div class="col-lg-3 form-group">
                            <label class="form-control-label">Access Points Location</label>
                            <input type="text" name="access_points_loc" class="form-control" value="{{ $isp->access_points_loc }}">
                        </div>
                        <div class="col-lg-3 form-group">
                            <label class="form-control-label">Admin Rooms Covered</label>
                            <input type="number" name="admin_rooms_covered" class="form-control" min="0" value="{{ $isp->admin_rooms_covered }}">
                        </div>
                        <div class="col-lg-3 form-group">
                            <label class="form-control-label">Classrooms Covered</label>
                            <input type="number" name="classrooms_covered" class="form-control" min="0" value="{{ $isp->classrooms_covered }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-3 form-group">
                            <label class="form-control-label">Admin Connectivity Rating (1-5)</label>
                            <input type="number" name="admin_connectivity_rating" class="form-control" min="1" max="5" value="{{ $isp->admin_connectivity_rating }}">
                        </div>
                        <div class="col-lg-3 form-group">
                            <label class="form-control-label">Classroom Connectivity Rating (1-5)</label>
                            <input type="number" name="classroom_connectivity_rating" class="form-control" min="1" max="5" value="{{ $isp->classroom_connectivity_rating }}">
                        </div>
                        <div class="col-lg-3 form-group">
                            <label class="form-control-label">Signal Quality</label>
                            <input type="text" name="signal_quality" class="form-control" value="{{ $isp->signal_quality }}">
                        </div>
                        <div class="col-lg-3 form-group">
                            <label class="form-control-label">ISP Service Rating (1-5)</label>
                            <input type="number" name="isp_service_rating" class="form-control" min="1" max="5" value="{{ $isp->isp_service_rating }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4 form-group">
                            <label class="form-control-label">Active ISP Counter</label>
                            <input type="number" name="active_isp_counter" class="form-control" min="0" value="{{ $isp->active_isp_counter }}">
                        </div>
                        <div class="col-lg-4 form-group">
                            <label class="form-control-label">Admin Counter (Custom)</label>
                            <input type="number" name="active_custom_counter_2" class="form-control" min="0" value="{{ $isp->active_custom_counter_2 }}">
                        </div>
                        <div class="col-lg-4 form-group">
                            <label class="form-control-label">Classroom Counter (Custom)</label>
                            <input type="number" name="active_custom_counter_3" class="form-control" min="0" value="{{ $isp->active_custom_counter_3 }}">
                        </div>
                    </div>
                </div>

                <div class="text-right mt-4">
                    <button type="submit" class="btn btn-primary px-5">Update Connection</button>
                    <a href="{{ route('isp.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mt-4">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Speed Test History</h3>
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#speedTestModal">
                <i class="fas fa-rocket mr-1"></i> Log New Test
            </button>
        </div>
        <div class="table-responsive">
            <table class="table align-items-center table-flush">
                <thead class="thead-light">
                    <tr>
                        <th>Date/Time</th>
                        <th>Download</th>
                        <th>Upload</th>
                        <th>Ping</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($isp->speedTests as $test)
                    <tr>
                        <td>{{ date('M d, Y h:i A', strtotime($test->test_date)) }}</td>
                        <td><span class="text-success font-weight-bold">{{ $test->download_mbps }}</span> Mbps</td>
                        <td>{{ $test->upload_mbps }} Mbps</td>
                        <td>{{ $test->ping_ms }} ms</td>
                        <td>{{ $test->remarks_speed }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-4">No speed tests recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="speedTestModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Log Speed Test Result</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('isp.speedtest', $isp->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="form-control-label">Date</label>
                            <input type="date" name="test_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-control-label">Time</label>
                            <input type="time" name="test_time" class="form-control" value="{{ date('H:i') }}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="form-control-label">Download</label>
                            <input type="number" step="0.01" name="download_mbps" class="form-control" placeholder="Mbps" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-control-label">Upload</label>
                            <input type="number" step="0.01" name="upload_mbps" class="form-control" placeholder="Mbps" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-control-label">Ping</label>
                            <input type="number" name="ping_ms" class="form-control" placeholder="ms" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-control-label">Remarks</label>
                        <textarea name="remarks_speed" class="form-control" rows="2" placeholder="Weather conditions, network load..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Result</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection