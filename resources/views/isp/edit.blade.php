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
                                @foreach(['PLDT', 'Globe', 'Smart', 'Converge', 'Starlink', 'DICT', 'SkyCable', 'DITO', 'Others'] as $provider)
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
                                @foreach(['Fiber' => 'Fiber Optics', 'DSL' => 'DSL', 'Cable' => 'Cable Internet', 'Wireless/LTS' => 'Fixed Wireless / LTE', 'Satellite' => 'Satellite', 'Mobile Data' => 'Mobile Data / Pocket WiFi', 'Leased Line' => 'Leased Line'] as $val => $label)
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
                        <div class="col-lg-6 form-group">
                            <label class="form-control-label">Plan Bandwidth / Max Speed (Mbps)</label>
                            <input type="text" name="plan_speed" class="form-control" value="{{ $isp->plan_speed }}">
                        </div>
                        <div class="col-lg-6 form-group">
                            <label class="form-control-label">Monthly Cost (MRC)</label>
                            <input type="number" step="0.01" name="monthly_mrc" class="form-control" value="{{ $isp->monthly_mrc }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6 form-group">
                            <label class="form-control-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="Active" {{ $isp->status == 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ $isp->status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="Pending" {{ $isp->status == 'Pending' ? 'selected' : '' }}>Pending Installation</option>
                            </select>
                        </div>
                        <div class="col-lg-6 form-group">
                            <label class="form-control-label">Contract End Date</label>
                            <input type="date" name="contract_end_date" class="form-control" value="{{ $isp->contract_end_date }}">
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

<div class="modal fade" id="speedTestModal" tabindex="-1" role="dialog" aria-hidden="true">
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