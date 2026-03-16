@extends('layouts.app')
@section('title', 'Add ISP Connection')

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow mb-4">
        <div class="card-header bg-white border-0">
            <div class="row align-items-center">
                <div class="col-8">
                    <h3 class="mb-0">Add New Connection</h3>
                </div>
                <div class="col-4 text-right">
                    <a href="{{ route('isp.index') }}" class="btn btn-sm btn-secondary">Back to List</a>
                </div>
            </div>
        </div>
        
        <div class="card-body bg-secondary">
            <form action="{{ route('isp.store') }}" method="POST">
                @csrf
                
                <h6 class="heading-small text-muted mb-4">Connection Information</h6>
                <div class="pl-lg-4">
                    <div class="row">
                        <div class="col-lg-6 form-group">
                            <label class="form-control-label">School</label>
                            <select name="school_id" class="form-control" required>
                                <option value="">Select School...</option>
                                @foreach($schools as $school)
                                    <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>
                                        {{ $school->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-6 form-group">
                            <label class="form-control-label">Internet Service Provider</label>
                            <select name="provider" class="form-control" required>
                                <option value="">Select Provider...</option>
                                @foreach(['PLDT', 'Globe', 'Smart', 'Converge', 'Starlink', 'DICT', 'SkyCable', 'DITO', 'Others'] as $provider)
                                    <option value="{{ $provider }}">{{ $provider }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4 form-group">
                            <label class="form-control-label">Account No. / Reference No.</label>
                            <input type="text" name="account_no" class="form-control" placeholder="e.g. 0212345678">
                        </div>
                        <div class="col-lg-4 form-group">
                            <label class="form-control-label">Connection Type</label>
                            <select name="internet_type" class="form-control">
                                <option value="Fiber">Fiber Optics</option>
                                <option value="DSL">DSL</option>
                                <option value="Cable">Cable Internet</option>
                                <option value="Wireless/LTS">Fixed Wireless / LTE</option>
                                <option value="Satellite">Satellite</option>
                                <option value="Mobile Data">Mobile Data / Pocket WiFi</option>
                                <option value="Leased Line">Leased Line</option>
                            </select>
                        </div>
                        <div class="col-lg-4 form-group">
                            <label class="form-control-label">Subscription Type</label>
                            <select name="subscription_type" class="form-control">
                                <option value="Postpaid">Postpaid</option>
                                <option value="Prepaid">Prepaid</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-3 form-group">
                            <label class="form-control-label">Purpose of Subscription</label>
                            <input type="text" name="purpose" class="form-control">
                        </div>
                        <div class="col-lg-3 form-group">
                            <label class="form-control-label">Mode of Acquisition</label>
                            <input type="text" name="acquisition_mode" class="form-control">
                        </div>
                        <div class="col-lg-3 form-group">
                            <label class="form-control-label">Donor</label>
                            <input type="text" name="donor" class="form-control">
                        </div>
                        <div class="col-lg-3 form-group">
                            <label class="form-control-label">Source of Funds</label>
                            <input type="text" name="fund_source" class="form-control">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6 form-group">
                            <label class="form-control-label">Plan Bandwidth / Max Speed (Mbps)</label>
                            <input type="text" name="plan_speed" class="form-control" placeholder="e.g. 100">
                        </div>
                        <div class="col-lg-6 form-group">
                            <label class="form-control-label">Minimum Guaranteed Speed (Mbps)</label>
                            <input type="text" name="min_speed" class="form-control" placeholder="e.g. 30">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4 form-group">
                            <label class="form-control-label">Monthly Cost (MRC)</label>
                            <input type="number" step="0.01" name="monthly_mrc" class="form-control" placeholder="0.00">
                        </div>
                        <div class="col-lg-4 form-group">
                            <label class="form-control-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="Active" selected>Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Pending">Pending Installation</option>
                            </select>
                        </div>
                        <div class="col-lg-4 form-group">
                            <label class="form-control-label">Area Coverage</label>
                            <select name="area_coverage" class="form-control">
                                <option value="Admin Office">Admin Office Only</option>
                                <option value="Faculty">Faculty Room</option>
                                <option value="Computer Lab">Computer Lab</option>
                                <option value="Classrooms">Classrooms</option>
                                <option value="Whole School">Whole School (Campus-wide)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-control-label">Package Inclusions</label>
                        <textarea name="package_inclusion" rows="2" class="form-control" placeholder="e.g. With Landline, Static IP, Mesh Units..."></textarea>
                    </div>
                </div>

                <hr class="my-4" />
                <h6 class="heading-small text-muted mb-4">Technical & Contract Details</h6>
                <div class="pl-lg-4">
                    <div class="row">
                        <div class="col-lg-4 form-group">
                            <label class="form-control-label">Installation Date</label>
                            <input type="date" name="installation_date" class="form-control">
                        </div>
                        <div class="col-lg-4 form-group">
                            <label class="form-control-label">Contract End Date</label>
                            <input type="date" name="contract_end_date" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4 form-group">
                            <label class="form-control-label">IP Configuration</label>
                            <select name="ip_type" class="form-control">
                                <option value="Dynamic">Dynamic IP</option>
                                <option value="Static">Static IP</option>
                            </select>
                        </div>
                        <div class="col-lg-4 form-group">
                            <label class="form-control-label">Public IP Address</label>
                            <input type="text" name="public_ip" class="form-control" placeholder="x.x.x.x">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-control-label">Remarks</label>
                        <textarea name="remarks" rows="2" class="form-control"></textarea>
                    </div>
                </div>

                <hr class="my-4" />
                <h6 class="heading-small text-muted mb-4">Initial Speed Test Result (Optional)</h6>
                <div class="pl-lg-4 bg-white p-3 rounded border">
                    <div class="row">
                        <div class="col-lg-6 form-group">
                            <label class="form-control-label">Test Date</label>
                            <input type="date" name="init_test_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-lg-6 form-group">
                            <label class="form-control-label">Test Time</label>
                            <input type="time" name="init_test_time" class="form-control" value="{{ date('H:i') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4 form-group">
                            <label class="form-control-label">Download (Mbps)</label>
                            <input type="number" step="0.01" name="init_download" class="form-control" placeholder="0.00">
                        </div>
                        <div class="col-lg-4 form-group">
                            <label class="form-control-label">Upload (Mbps)</label>
                            <input type="number" step="0.01" name="init_upload" class="form-control" placeholder="0.00">
                        </div>
                        <div class="col-lg-4 form-group">
                            <label class="form-control-label">Ping (ms)</label>
                            <input type="number" name="init_ping" class="form-control" placeholder="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-control-label">Speed Test Remarks</label>
                        <textarea name="init_remarks_speed" class="form-control" rows="1" placeholder="e.g. Baseline test upon installation"></textarea>
                    </div>
                </div>

                <div class="text-right mt-4">
                    <button type="submit" class="btn btn-primary px-5">Save Connection</button>
                    <a href="{{ route('isp.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection