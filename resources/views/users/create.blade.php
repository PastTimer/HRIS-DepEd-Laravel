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
                    <form method="POST" action="/users">
                        @csrf

                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white"><h5 class="mb-0 text-muted">Personal Details</h5></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 form-group mb-3">
                                        <label class="form-control-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required>
                                        @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 form-group mb-3">
                                        <label class="form-control-label">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" required>
                                        @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 form-group mb-3">
                                        <label class="form-control-label">Office / Assignment <span class="text-danger">*</span></label>
                                        <select name="office" class="form-control @error('office') is-invalid @enderror" required>
                                            <option value="" disabled {{ old('office') === null ? 'selected' : '' }}>-- Select Office --</option>
                                            @foreach(['SDO', 'ASDS', 'SDS', 'SCHOOL', 'PERSONNEL', 'CID', 'LEGAL', 'ACCTG', 'ITO', 'SGOD', 'CASH', 'BUDGET', 'SUPPLY', 'RECORDS', 'BAC'] as $officeOption)
                                                <option value="{{ $officeOption }}" {{ old('office') == $officeOption ? 'selected' : '' }}>{{ $officeOption }}</option>
                                            @endforeach
                                        </select>
                                        @error('office') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

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
                                        <select id="role" name="role" class="form-control @error('role') is-invalid @enderror" onchange="updateAccessLevel()" required>
                                            <option value="" disabled {{ old('role') === null ? 'selected' : '' }}>-- Select User Type --</option>
                                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>System Admin</option>
                                            <option value="school" {{ old('role') == 'school' ? 'selected' : '' }}>School User</option>
                                            <option value="personnel" {{ old('role') == 'personnel' ? 'selected' : '' }}>Personnel</option>
                                        </select>
                                        <small class="form-text text-muted">Determines system privileges.</small>
                                        @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 form-group mb-3">
                                        <label class="form-control-label">Access Level <span class="text-danger">*</span></label>
                                        <select id="access_level" name="access_level" class="form-control @error('access_level') is-invalid @enderror" required>
                                            <option value="">-- Select User Type First --</option>
                                        </select>
                                        <small id="accessHelp" class="form-text text-muted">Select user type to see available options.</small>
                                        @error('access_level') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 form-group mb-3">
                                        <label class="form-control-label">Account Status <span class="text-danger">*</span></label>
                                        <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                            <a href="/users" class="btn btn-secondary px-5">Cancel</a>
                            <button type="submit" class="btn btn-primary px-5"><i class="ni ni-check-bold mr-2"></i> Create User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // We pass the data from the Controller directly into JavaScript!
    const schoolsData = @json($schools);
    const employeesData = @json($employees);
    const oldAccessLevel = "{{ old('access_level') }}";

    function updateAccessLevel() {
        const role = document.getElementById('role').value;
        const accessSelect = document.getElementById('access_level');
        const helpText = document.getElementById('accessHelp');
        
        // Reset dropdown
        accessSelect.innerHTML = '<option value="" disabled selected>-- Select Access Level --</option>';

        if (role === 'admin') {
            helpText.innerText = "Full system access granted.";
            accessSelect.innerHTML += `<option value="All Schools" ${oldAccessLevel === 'All Schools' ? 'selected' : ''}>All Schools / Full Access</option>`;
        } 
        else if (role === 'school') {
            helpText.innerText = "Select the school this user will manage.";
            schoolsData.forEach(school => {
                const isSelected = oldAccessLevel == school.name ? 'selected' : '';
                accessSelect.innerHTML += `<option value="${school.name}" ${isSelected}>${school.name}</option>`;
            });
        } 
        else if (role === 'personnel') {
            helpText.innerText = "Select the employee record this account belongs to.";
            employeesData.forEach(emp => {
                // You can save either the Employee ID or Name here. Saving Name for visual clarity based on your old system.
                const empName = `${emp.last_name}, ${emp.first_name}`;
                const isSelected = oldAccessLevel == empName ? 'selected' : '';
                accessSelect.innerHTML += `<option value="${empName}" ${isSelected}>${empName}</option>`;
            });
        }
    }

    // Run once on page load to handle validation errors seamlessly
    document.addEventListener('DOMContentLoaded', function() {
        if(document.getElementById('role').value !== "") {
            updateAccessLevel();
        }
    });
</script>
@endsection