@extends('layouts.app')
@section('title', 'Add User Account')
@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-xl-8 col-lg-10 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header border-0 bg-white">
                    <h2 class="mb-0 text-primary"><i class="ni ni-single-02 mr-2"></i> ADD USER ACCOUNT</h2>
                </div>

                <div class="card-body bg-secondary">
                    <form method="POST" action="{{ route('users.store') }}">
                        @csrf

                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white"><h5 class="mb-0 text-muted">Account Settings</h5></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 form-group mb-3">
                                        <label class="form-control-label">Username <span class="text-danger">*</span></label>
                                        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" required>
                                        @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 form-group mb-3">
                                        <label class="form-control-label">Email Address</label>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group mb-3">
                                        <label class="form-control-label">User Type (Role) <span class="text-danger">*</span></label>
                                        <select id="role" name="role" class="form-control @error('role') is-invalid @enderror" onchange="updateLinkTargets()" required>
                                            <option value="" disabled {{ old('role') === null ? 'selected' : '' }}>-- Select User Type --</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>
                                                    {{ $role === 'encoding_officer' ? 'ENCODING OFFICER' : strtoupper(str_replace('_', ' ', $role)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">Role is fixed on create. Editing does not allow role changes.</small>
                                        @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    @php
                                        $schoolOptions = [];
                                        foreach ($availableSchoolUsers as $school) {
                                            $schoolOptions[$school->id] = [
                                                'id' => $school->id,
                                                'name' => $school->name,
                                                'allow_school' => true,
                                                'allow_eo' => false,
                                            ];
                                        }
                                        foreach ($availableEncodingOfficerSchools as $school) {
                                            if (!isset($schoolOptions[$school->id])) {
                                                $schoolOptions[$school->id] = [
                                                    'id' => $school->id,
                                                    'name' => $school->name,
                                                    'allow_school' => false,
                                                    'allow_eo' => true,
                                                ];
                                            } else {
                                                $schoolOptions[$school->id]['allow_eo'] = true;
                                            }
                                        }
                                    @endphp
                                    <div class="col-md-6 form-group mb-3" id="schoolLinkWrap" style="display:none;">
                                        <label class="form-control-label">Linked School</label>
                                        <select id="school_id" name="school_id" class="form-control @error('school_id') is-invalid @enderror">
                                            <option value="">-- Leave Blank to Auto-Create/Assign --</option>
                                            @foreach($schoolOptions as $school)
                                                <option
                                                    value="{{ $school['id'] }}"
                                                    data-allow-school="{{ $school['allow_school'] ? '1' : '0' }}"
                                                    data-allow-eo="{{ $school['allow_eo'] ? '1' : '0' }}"
                                                    {{ old('school_id') == $school['id'] ? 'selected' : '' }}
                                                >{{ $school['name'] }}</option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">If blank: School role gets a blank school; EO defaults to HQ.</small>
                                        @error('school_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6 form-group mb-3" id="personnelLinkWrap" style="display:none;">
                                        <label class="form-control-label">Linked Personnel</label>
                                        <select id="personnel_id" name="personnel_id" class="form-control @error('personnel_id') is-invalid @enderror">
                                            <option value="">-- Leave Blank to Auto-Create --</option>
                                            @foreach($personnelList as $personnel)
                                                @php($profile = $personnel->pdsMain)
                                                <option value="{{ $personnel->id }}" {{ old('personnel_id') == $personnel->id ? 'selected' : '' }}>
                                                    {{ ($profile->last_name ?? 'N/A') . ', ' . ($profile->first_name ?? '') }}{{ $personnel->emp_id ? ' (' . $personnel->emp_id . ')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">If blank: a placeholder personnel record is auto-created.</small>
                                        @error('personnel_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12 form-group mb-0">
                                        <div class="alert alert-light border mb-0">
                                            Admin accounts are linked to HQ for structure consistency and remain globally scoped.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white"><h5 class="mb-0 text-muted">Security</h5></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 form-group mb-3">
                                        <label class="form-control-label">Password <span class="text-danger">*</span></label>
                                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 form-group mb-3">
                                        <label class="form-control-label">Confirm Password <span class="text-danger">*</span></label>
                                        <input type="password" name="password_confirmation" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4 mb-3 px-3">
                            <a href="{{ route('users.index') }}" class="btn btn-secondary px-5">Cancel</a>
                            <button type="submit" class="btn btn-primary px-5"><i class="ni ni-check-bold mr-2"></i> Create User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function updateLinkTargets() {
        const role = document.getElementById('role').value;
        const schoolWrap = document.getElementById('schoolLinkWrap');
        const personnelWrap = document.getElementById('personnelLinkWrap');
        const schoolSelect = document.getElementById('school_id');
        const personnelSelect = document.getElementById('personnel_id');

        const isSchool = role === 'school';
        const isEncodingOfficer = role === 'encoding_officer';
        const isPersonnel = role === 'personnel';

        schoolWrap.style.display = (isSchool || isEncodingOfficer) ? '' : 'none';
        personnelWrap.style.display = isPersonnel ? '' : 'none';

        Array.from(schoolSelect.options).forEach(opt => {
            if (!opt.value) {
                opt.style.display = '';
                return;
            }

            const allowSchool = opt.getAttribute('data-allow-school') === '1';
            const allowEo = opt.getAttribute('data-allow-eo') === '1';
            const showOption = (isSchool && allowSchool) || (isEncodingOfficer && allowEo);

            opt.style.display = showOption ? '' : 'none';
        });

        if (schoolSelect.selectedIndex > 0 && schoolSelect.options[schoolSelect.selectedIndex].style.display === 'none') {
            schoolSelect.selectedIndex = 0;
        }

        if (!(isSchool || isEncodingOfficer)) {
            schoolSelect.value = '';
        }

        if (!isPersonnel) {
            personnelSelect.value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateLinkTargets();
    });
</script>
@endsection
