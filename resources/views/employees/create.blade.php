@extends('layouts.app')
@section('title', 'Add New Employee')
@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-xl-10 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-white border-0">
                    <div class="row align-items-center">
                        <div class="col-8"><h3 class="mb-0">Complete Employee Profile</h3></div>
                        <div class="col-4 text-right"><a href="/employees" class="btn btn-sm btn-primary">Back to List</a></div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="/employees">
                        @csrf
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
                            </div>
                        @endif

                        <h6 class="heading-small text-muted mb-4">Personal Information</h6>
                        <div class="pl-lg-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-control-label">First Name *</label>
                                        <input type="text" name="first_name" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Middle Name</label>
                                        <input type="text" name="middle_name" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Last Name *</label>
                                        <input type="text" name="last_name" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Name Ext. (Jr, Sr)</label>
                                        <input type="text" name="name_ext" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Date of Birth</label>
                                        <input type="date" name="date_of_birth" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Gender</label>
                                        <select name="gender" class="form-control">
                                            <option value="">Select...</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Civil Status</label>
                                        <select name="civil_status" class="form-control">
                                            <option value="">Select...</option>
                                            <option value="Single">Single</option>
                                            <option value="Married">Married</option>
                                            <option value="Widowed">Widowed</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Blood Type</label>
                                        <input type="text" name="blood_type" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4" />
                        
                        <h6 class="heading-small text-muted mb-4">Employment & Assignment Details</h6>
                        <div class="pl-lg-4">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Employee ID Number *</label>
                                        <input type="text" name="employee_id" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Official Station (School)</label>
                                        <select name="school_id" class="form-control">
                                            <option value="">Unassigned</option>
                                            @foreach($schools as $school)
                                                <option value="{{ $school->id }}">{{ $school->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Designation / Position</label>
                                        <select name="designation_id" class="form-control">
                                            <option value="">Select Position</option>
                                            @foreach($designations as $designation)
                                                <option value="{{ $designation->id }}">{{ $designation->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Item Number</label>
                                        <input type="text" name="item_no" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Salary Grade</label>
                                        <input type="text" name="salary_grade" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Step</label>
                                        <input type="text" name="step" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4" />

                        <h6 class="heading-small text-muted mb-4">Government ID Numbers</h6>
                        <div class="pl-lg-4">
                            <div class="row">
                                <div class="col-md-4"><div class="form-group"><label>GSIS No.</label><input type="text" name="gsis_no" class="form-control"></div></div>
                                <div class="col-md-4"><div class="form-group"><label>PAG-IBIG No.</label><input type="text" name="pagibig_no" class="form-control"></div></div>
                                <div class="col-md-4"><div class="form-group"><label>PhilHealth No.</label><input type="text" name="philhealth_no" class="form-control"></div></div>
                            </div>
                            <div class="row">
                                <div class="col-md-4"><div class="form-group"><label>SSS No.</label><input type="text" name="sss_no" class="form-control"></div></div>
                                <div class="col-md-4"><div class="form-group"><label>TIN No.</label><input type="text" name="tin_no" class="form-control"></div></div>
                            </div>
                        </div>

                        <hr class="my-4" />

                        <h6 class="heading-small text-muted mb-4">Contact Information</h6>
                        <div class="pl-lg-4">
                            <div class="row">
                                <div class="col-md-6"><div class="form-group"><label>Email Address</label><input type="email" name="email" class="form-control"></div></div>
                                <div class="col-md-6"><div class="form-group"><label>Contact Number</label><input type="text" name="contact_no" class="form-control"></div></div>
                            </div>
                            <div class="row">
                                <div class="col-md-12"><div class="form-group"><label>Home Address</label><input type="text" name="address" class="form-control"></div></div>
                            </div>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn btn-success mt-4">Save Complete Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection