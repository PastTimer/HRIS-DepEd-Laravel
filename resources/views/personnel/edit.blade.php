@extends('layouts.app')
@section('title', 'Edit Personnel Details')
@section('content')
@php
    $isPersonnelUser = auth()->user()?->hasRole('personnel') ?? false;
@endphp
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-xl-10 col-lg-12 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
                    <h2 class="mb-0 text-primary"><i class="ni ni-badge mr-2"></i> EDIT PERSONNEL DETAILS</h2>
                    <div>
                        <a href="{{ route('personnel.pds.edit', $personnel) }}" class="btn btn-sm btn-info mr-2">
                            <i class="fas fa-id-card mr-1"></i> Edit PDS
                        </a>
                        <a href="{{ route('personnel.show', $personnel) }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Profile
                        </a>
                    </div>
                </div>

                <div class="card-body bg-secondary">
                    @if($isPersonnelUser)
                        <div class="alert alert-warning">
                            Changing your assigned station is allowed, but it may affect which records you can access.
                        </div>
                    @endif

                    <form id="personnel-details-form" method="POST" action="{{ route('personnel.update', $personnel->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white"><h5 class="mb-0 text-uppercase text-muted">Employment Information</h5></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 form-group mb-3">
                                        <label class="form-control-label">Employee ID</label>
                                        <input type="text" name="employee_id" class="form-control @error('employee_id') is-invalid @enderror" value="{{ old('employee_id', $personnel->emp_id) }}">
                                        @error('employee_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 form-group mb-3">
                                        <label class="form-control-label">Position <span class="text-danger">*</span></label>
                                        <select name="position_id" class="form-control @error('position_id') is-invalid @enderror" required>
                                            @foreach($positions as $position)
                                                <option value="{{ $position->id }}" {{ old('position_id', $personnel->position_id) == $position->id ? 'selected' : '' }}>{{ $position->title }}</option>
                                            @endforeach
                                        </select>
                                        @error('position_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-3 form-group mb-3">
                                        <label class="form-control-label">Item No.</label>
                                        <input type="text" name="item_no" class="form-control @error('item_no') is-invalid @enderror" value="{{ old('item_no', $personnel->item_number) }}">
                                        @error('item_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3 form-group mb-3">
                                        <label class="form-control-label">Current Step <span class="text-danger">*</span></label>
                                        <input type="number" name="step" class="form-control @error('step') is-invalid @enderror" value="{{ old('step', $personnel->current_step) }}" min="1" required>
                                        @error('step') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-3 form-group mb-3">
                                        <label class="form-control-label">Last Step Increment <span class="text-danger">*</span></label>
                                        <input type="date" name="last_step" class="form-control @error('last_step') is-invalid @enderror" value="{{ old('last_step', $personnel->last_step_increment_date) }}" required>
                                        @error('last_step') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-3 form-group mb-3">
                                        <label class="form-control-label">Salary Grade</label>
                                        <input type="text" name="sg" class="form-control @error('sg') is-invalid @enderror" value="{{ old('sg', $personnel->salary_grade) }}">
                                        @error('sg') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-3 form-group mb-3">
                                        <label class="form-control-label">Salary (Actual)</label>
                                        <input type="number" step="0.01" name="salary_actual" class="form-control @error('salary_actual') is-invalid @enderror" value="{{ old('salary_actual', $personnel->salary_actual) }}">
                                        @error('salary_actual') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 form-group mb-3">
                                        <label class="form-control-label">Branch</label>
                                        <input type="text" name="branch" class="form-control @error('branch') is-invalid @enderror" value="{{ old('branch', $personnel->branch) }}">
                                        @error('branch') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label class="form-control-label">Employee Type <span class="text-danger">*</span></label>
                                        <select name="employee_type" class="form-control @error('employee_type') is-invalid @enderror" required>
                                            @foreach(['Regular', 'Contractual', 'Substitute'] as $type)
                                                <option value="{{ $type }}" {{ old('employee_type', $personnel->employee_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                                            @endforeach
                                        </select>
                                        @error('employee_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label class="form-control-label">Service Effective Date</label>
                                        <input type="date" name="service_effective_date" class="form-control @error('service_effective_date') is-invalid @enderror" value="{{ old('service_effective_date', now()->toDateString()) }}">
                                        @error('service_effective_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white"><h5 class="mb-0 text-uppercase text-muted">Station Assignment</h5></div>
                            <div class="card-body">
                                <div class="alert alert-warning">
                                    <strong>Note:</strong> If you move this personnel to a different school, you will no longer be able to view or edit their information after saving, as they will be assigned to another school.
                                </div>
                                <div class="row">
                                    @php
                                        $user = Auth::user();
                                        $isSchoolUser = $user && $user->hasRole('school') && $user->school;
                                        $userSchoolId = $isSchoolUser ? $user->school->id : null;
                                    @endphp
                                    <div class="col-md-6 form-group mb-3">
                                        <label class="form-control-label">Assigned Station <span class="text-danger">*</span></label>
                                        <select id="assigned_school_id" name="assigned_school_id" class="form-control @error('assigned_school_id') is-invalid @enderror" required>
                                            @foreach($schools as $school)
                                                <option value="{{ $school->id }}"
                                                    @if(old('assigned_school_id', $personnel->assigned_school_id))
                                                        {{ old('assigned_school_id', $personnel->assigned_school_id) == $school->id ? 'selected' : '' }}
                                                    @elseif($isSchoolUser && $userSchoolId == $school->id)
                                                        selected
                                                    @endif
                                                >{{ $school->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('assigned_school_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 form-group mb-3">
                                        <label class="form-control-label">Deployed Station</label>
                                        <select name="deployed_school_id" class="form-control @error('deployed_school_id') is-invalid @enderror">
                                            <option value="">Same as Assigned Station</option>
                                            @foreach($schools as $school)
                                                <option value="{{ $school->id }}"
                                                    @if(old('deployed_school_id', $personnel->deployed_school_id))
                                                        {{ old('deployed_school_id', $personnel->deployed_school_id) == $school->id ? 'selected' : '' }}
                                                    @elseif($isSchoolUser && $userSchoolId == $school->id)
                                                        selected
                                                    @endif
                                                >{{ $school->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('deployed_school_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
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
                                            @foreach([1 => 'Active', 0 => 'Inactive'] as $val => $label)
                                                <option value="{{ $val }}" {{ old('is_active', (string) $personnel->is_active) == (string) $val ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('is_active') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4 mb-3 px-3">
                            <a href="{{ route('personnel.show', $personnel) }}" class="btn btn-secondary px-5">Cancel</a>
                            <button type="submit" class="btn btn-success px-5"><i class="ni ni-check-bold mr-2"></i> Update Personnel Details</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@if($isPersonnelUser)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('personnel-details-form');
        const stationSelect = document.getElementById('assigned_school_id');
        const originalStation = '{{ (string) $personnel->assigned_school_id }}';

        if (!form || !stationSelect) {
            return;
        }

        form.addEventListener('submit', function (e) {
            if ((stationSelect.value || '') !== originalStation) {
                const confirmed = window.confirm('You are changing your assigned station. Continue?');
                if (!confirmed) {
                    e.preventDefault();
                }
            }
        });
    });
</script>
@endif
@endsection
