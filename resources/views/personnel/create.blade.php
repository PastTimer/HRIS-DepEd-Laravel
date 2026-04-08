@extends('layouts.app')
@section('title', 'Add Personnel')
@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-xl-10 col-lg-12 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header border-0 bg-white">
                    <h2 class="mb-0 text-primary"><i class="ni ni-badge mr-2"></i> ADD PERSONNEL</h2>
                </div>
                
                <div class="card-body bg-secondary">
                    <form method="POST" action="{{ route('personnel.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white"><h5 class="mb-0 text-uppercase text-muted">Profile Photo</h5></div>
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-3 text-center">
                                        <img id="photo-preview" src="{{ asset('uploads/default/defaultpic.png') }}" alt="Preview" class="img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;">
                                    </div>
                                    <div class="col-md-9">
                                        <label for="photo" class="btn btn-warning">
                                            <i class="ni ni-camera-compact mr-2"></i> SELECT PHOTO
                                        </label>
                                        <input type="file" name="photo" id="photo" accept="image/*" onchange="previewPhoto();" style="display:none">
                                        <p class="text-muted small mt-2">Recommended: Square image, max 500KB (JPG, PNG, GIF)</p>
                                        @error('photo') <div class="text-danger small">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white"><h5 class="mb-0 text-uppercase text-muted">Personal Information</h5></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 form-group mb-3">
                                        <label class="form-control-label">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" required>
                                        @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label class="form-control-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required>
                                        @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-3 form-group mb-3">
                                        <label class="form-control-label">Middle Name</label>
                                        <input type="text" name="middle_name" class="form-control @error('middle_name') is-invalid @enderror" value="{{ old('middle_name') }}">
                                        @error('middle_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-2 form-group mb-3">
                                        <label class="form-control-label">Ext. Name</label>
                                        <input type="text" name="name_ext" class="form-control @error('name_ext') is-invalid @enderror" value="{{ old('name_ext') }}" placeholder="Jr, III">
                                        @error('name_ext') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3 form-group mb-3">
                                        <label class="form-control-label">Gender <span class="text-danger">*</span></label>
                                        <select name="gender" class="form-control @error('gender') is-invalid @enderror" required>
                                            <option value="" disabled {{ old('gender') === null ? 'selected' : '' }}>Select Gender</option>
                                            <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                            <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                        </select>
                                        @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-3 form-group mb-3">
                                        <label class="form-control-label">Date of Birth <span class="text-danger">*</span></label>
                                        <input type="date" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth') }}" required>
                                        @error('date_of_birth') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 form-group mb-3">
                                        <label class="form-control-label">Place of Birth</label>
                                        <input type="text" name="place_of_birth" class="form-control @error('place_of_birth') is-invalid @enderror" value="{{ old('place_of_birth') }}">
                                        @error('place_of_birth') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 form-group mb-3">
                                        <label class="form-control-label">Civil Status</label>
                                        <select name="civil_status" class="form-control @error('civil_status') is-invalid @enderror">
                                            <option value=""></option>
                                            <option value="Single" {{ old('civil_status') == 'Single' ? 'selected' : '' }}>Single</option>
                                            <option value="Married" {{ old('civil_status') == 'Married' ? 'selected' : '' }}>Married</option>
                                            <option value="Divorced" {{ old('civil_status') == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                                            <option value="Widowed" {{ old('civil_status') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                        </select>
                                        @error('civil_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label class="form-control-label">Blood Type</label>
                                        <select name="blood_type" class="form-control @error('blood_type') is-invalid @enderror">
                                            <option value=""></option>
                                            @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $blood)
                                                <option value="{{ $blood }}" {{ old('blood_type') == $blood ? 'selected' : '' }}>{{ $blood }}</option>
                                            @endforeach
                                        </select>
                                        @error('blood_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white"><h5 class="mb-0 text-uppercase text-muted">Employment Information</h5></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 form-group mb-3">
                                        <label class="form-control-label">Employee ID</label>
                                        <input type="text" name="employee_id" class="form-control @error('employee_id') is-invalid @enderror" value="{{ old('employee_id') }}" placeholder="Leave blank if N/A">
                                        @error('employee_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 form-group mb-3">
                                        <label class="form-control-label">Position <span class="text-danger">*</span></label>
                                        <select name="position_id" class="form-control @error('position_id') is-invalid @enderror" required>
                                            <option value="" disabled {{ old('position_id') === null ? 'selected' : '' }}>Select Position</option>
                                            @foreach($positions as $position)
                                                <option value="{{ $position->id }}" {{ old('position_id') == $position->id ? 'selected' : '' }}>
                                                    {{ $position->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('position_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-3 form-group mb-3">
                                        <label class="form-control-label">Item No.</label>
                                        <input type="text" name="item_no" class="form-control @error('item_no') is-invalid @enderror" value="{{ old('item_no') }}">
                                        @error('item_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3 form-group mb-3">
                                        <label class="form-control-label">Current Step <span class="text-danger">*</span></label>
                                        <input type="number" name="step" class="form-control @error('step') is-invalid @enderror" value="{{ old('step', 1) }}" required>
                                        @error('step') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-3 form-group mb-3">
                                        <label class="form-control-label">Last Step Increment <span class="text-danger">*</span></label>
                                        <input type="date" name="last_step" class="form-control @error('last_step') is-invalid @enderror" value="{{ old('last_step') }}" required>
                                        @error('last_step') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-3 form-group mb-3">
                                        <label class="form-control-label">Branch</label>
                                        <input type="text" name="branch" class="form-control @error('branch') is-invalid @enderror" value="{{ old('branch') }}">
                                        @error('branch') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-3 form-group mb-3">
                                        <label class="form-control-label">Employee Type <span class="text-danger">*</span></label>
                                        <select name="employee_type" class="form-control @error('employee_type') is-invalid @enderror" required>
                                            <option value="Regular" {{ old('employee_type') == 'Regular' ? 'selected' : '' }}>Regular</option>
                                            <option value="Contractual" {{ old('employee_type') == 'Contractual' ? 'selected' : '' }}>Contractual</option>
                                            <option value="Substitute" {{ old('employee_type') == 'Substitute' ? 'selected' : '' }}>Substitute</option>
                                        </select>
                                        @error('employee_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 form-group mb-3">
                                        <label class="form-control-label">Salary Grade</label>
                                        <input type="text" name="sg" class="form-control @error('sg') is-invalid @enderror" value="{{ old('sg') }}">
                                        @error('sg') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label class="form-control-label">Salary (Actual)</label>
                                        <input type="number" step="0.01" name="salary_actual" class="form-control @error('salary_actual') is-invalid @enderror" value="{{ old('salary_actual') }}">
                                        @error('salary_actual') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label class="form-control-label">Service Start Date</label>
                                        <input type="date" name="service_start_date" class="form-control @error('service_start_date') is-invalid @enderror" value="{{ old('service_start_date', now()->toDateString()) }}">
                                        @error('service_start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white"><h5 class="mb-0 text-uppercase text-muted">Station Assignment</h5></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 form-group mb-3">
                                        <label class="form-control-label">Assigned Station <span class="text-danger">*</span></label>
                                        <select name="assigned_school_id" class="form-control @error('assigned_school_id') is-invalid @enderror" required>
                                            <option value="" disabled {{ old('assigned_school_id') === null ? 'selected' : '' }}>Select Assigned Station</option>
                                            @foreach($schools as $school)
                                                <option value="{{ $school->id }}" {{ old('assigned_school_id') == $school->id ? 'selected' : '' }}>
                                                    {{ $school->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('assigned_school_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 form-group mb-3">
                                        <label class="form-control-label">Deployed Station</label>
                                        <select name="deployed_school_id" class="form-control @error('deployed_school_id') is-invalid @enderror">
                                            <option value="">Same as Assigned Station</option>
                                            @foreach($schools as $school)
                                                <option value="{{ $school->id }}" {{ old('deployed_school_id') == $school->id ? 'selected' : '' }}>
                                                    {{ $school->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('deployed_school_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white"><h5 class="mb-0 text-uppercase text-muted">Identification Numbers</h5></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 form-group mb-3">
                                        <label class="form-control-label">GSIS No.</label>
                                        <input type="text" name="gsis_no" class="form-control @error('gsis_no') is-invalid @enderror" value="{{ old('gsis_no') }}">
                                        @error('gsis_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label class="form-control-label">Pag-ibig No.</label>
                                        <input type="text" name="pagibig_no" class="form-control @error('pagibig_no') is-invalid @enderror" value="{{ old('pagibig_no') }}">
                                        @error('pagibig_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label class="form-control-label">Philhealth No.</label>
                                        <input type="text" name="philhealth_no" class="form-control @error('philhealth_no') is-invalid @enderror" value="{{ old('philhealth_no') }}">
                                        @error('philhealth_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 form-group mb-3">
                                        <label class="form-control-label">SSS No.</label>
                                        <input type="text" name="sss_no" class="form-control @error('sss_no') is-invalid @enderror" value="{{ old('sss_no') }}">
                                        @error('sss_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label class="form-control-label">TIN No.</label>
                                        <input type="text" name="tin_no" class="form-control @error('tin_no') is-invalid @enderror" value="{{ old('tin_no') }}">
                                        @error('tin_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white"><h5 class="mb-0 text-uppercase text-muted">Contact Details</h5></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 form-group mb-3">
                                        <label class="form-control-label">Contact No.</label>
                                        <input type="text" name="contact_no" class="form-control @error('contact_no') is-invalid @enderror" value="{{ old('contact_no') }}">
                                        @error('contact_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-8 form-group mb-3">
                                        <label class="form-control-label">Email Address</label>
                                        <input type="email" name="email_address" class="form-control @error('email_address') is-invalid @enderror" value="{{ old('email_address') }}">
                                        @error('email_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 form-group mb-3">
                                        <label class="form-control-label">Residential Address</label>
                                        <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address') }}</textarea>
                                        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6 form-group">
                                        <label class="form-control-label">Account Status</label>
                                        <select name="is_active" class="form-control @error('is_active') is-invalid @enderror">
                                            <option value="1" {{ old('is_active') == '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('is_active') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4 mb-3 px-3">
                            <a href="{{ route('personnel.index') }}" class="btn btn-secondary px-5">Cancel</a>
                            <button type="submit" class="btn btn-success px-5"><i class="ni ni-check-bold mr-2"></i> Save Personnel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function previewPhoto() {
        var preview = document.querySelector('#photo-preview');
        var file    = document.querySelector('#photo').files[0];
        var reader  = new FileReader();

        reader.onloadend = function () {
            preview.src = reader.result;
        }

        if (file) {
            reader.readAsDataURL(file);
        } else {
            preview.src = "{{ asset('uploads/default/defaultpic.png') }}";
        }
    }
</script>
@endsection