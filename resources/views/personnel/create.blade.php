@extends('layouts.app')
@section('title', 'Add Personnel Details')
@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-xl-10 col-lg-12 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header border-0 bg-white">
                    <h2 class="mb-0 text-primary"><i class="ni ni-badge mr-2"></i> ADD PERSONNEL DETAILS</h2>
                </div>

                <div class="card-body bg-secondary">
                    <form method="POST" action="{{ route('personnel.store') }}">
                        @csrf

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
                                                <option value="{{ $position->id }}" {{ old('position_id') == $position->id ? 'selected' : '' }}>{{ $position->title }}</option>
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
                                        <input type="number" name="step" class="form-control @error('step') is-invalid @enderror" value="{{ old('step', 1) }}" min="1" required>
                                        @error('step') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-3 form-group mb-3">
                                        <label class="form-control-label">Last Step Increment <span class="text-danger">*</span></label>
                                        <input type="date" name="last_step" class="form-control @error('last_step') is-invalid @enderror" value="{{ old('last_step', now()->toDateString()) }}" required>
                                        @error('last_step') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-3 form-group mb-3">
                                        <label class="form-control-label">Salary Grade</label>
                                        <input type="text" name="sg" class="form-control @error('sg') is-invalid @enderror" value="{{ old('sg') }}">
                                        @error('sg') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-3 form-group mb-3">
                                        <label class="form-control-label">Salary (Actual)</label>
                                        <input type="number" step="0.01" name="salary_actual" class="form-control @error('salary_actual') is-invalid @enderror" value="{{ old('salary_actual') }}">
                                        @error('salary_actual') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 form-group mb-3">
                                        <label class="form-control-label">Branch</label>
                                        <input type="text" name="branch" class="form-control @error('branch') is-invalid @enderror" value="{{ old('branch') }}">
                                        @error('branch') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label class="form-control-label">Employee Type <span class="text-danger">*</span></label>
                                        <select name="employee_type" class="form-control @error('employee_type') is-invalid @enderror" required>
                                            <option value="Regular" {{ old('employee_type') == 'Regular' ? 'selected' : '' }}>Regular</option>
                                            <option value="Contractual" {{ old('employee_type') == 'Contractual' ? 'selected' : '' }}>Contractual</option>
                                            <option value="Substitute" {{ old('employee_type') == 'Substitute' ? 'selected' : '' }}>Substitute</option>
                                        </select>
                                        @error('employee_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                    @if(Auth::user()->hasRole('school') && Auth::user()->school)
                                        <div class="col-md-6 form-group mb-3">
                                            <label class="form-control-label">Assigned Station <span class="text-danger">*</span></label>
                                            <input type="hidden" name="assigned_school_id" value="{{ Auth::user()->school->id }}">
                                            <input type="text" class="form-control" value="{{ Auth::user()->school->name }}" readonly>
                                        </div>
                                        <div class="col-md-6 form-group mb-3">
                                            <label class="form-control-label">Deployed Station</label>
                                            <input type="hidden" name="deployed_school_id" value="{{ Auth::user()->school->id }}">
                                            <input type="text" class="form-control" value="{{ Auth::user()->school->name }}" readonly>
                                        </div>
                                    @else
                                        <div class="col-md-6 form-group mb-3">
                                            <label class="form-control-label">Assigned Station <span class="text-danger">*</span></label>
                                            <select name="assigned_school_id" class="form-control @error('assigned_school_id') is-invalid @enderror" required>
                                                <option value="" disabled {{ old('assigned_school_id') === null ? 'selected' : '' }}>Select Assigned Station</option>
                                                @foreach($schools as $school)
                                                    <option value="{{ $school->id }}" {{ old('assigned_school_id') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('assigned_school_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6 form-group mb-3">
                                            <label class="form-control-label">Deployed Station</label>
                                            <select name="deployed_school_id" class="form-control @error('deployed_school_id') is-invalid @enderror">
                                                <option value="">Same as Assigned Station</option>
                                                @foreach($schools as $school)
                                                    <option value="{{ $school->id }}" {{ old('deployed_school_id') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('deployed_school_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white"><h5 class="mb-0 text-uppercase text-muted">Account Status</h5></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 form-group mb-0">
                                        <label class="form-control-label">Status</label>
                                        <select name="is_active" class="form-control @error('is_active') is-invalid @enderror">
                                            <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('is_active') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4 mb-3 px-3">
                            <a href="{{ route('personnel.index') }}" class="btn btn-secondary px-5">Cancel</a>
                            <button type="submit" class="btn btn-success px-5"><i class="ni ni-check-bold mr-2"></i> Save Personnel Details</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
