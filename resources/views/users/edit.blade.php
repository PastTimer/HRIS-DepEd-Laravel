@extends('layouts.app')
@section('title', 'Edit User Account')
@section('content')
@php
    $selectedRole = $currentRole ?? $user->getRoleNames()->first();
    $isSelfAccount = $isSelfAccount ?? false;
    $cancelRoute = $cancelRoute ?? 'users.index';
@endphp
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-xl-8 col-lg-10 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header border-0 bg-white">
                    <h2 class="mb-0 text-primary"><i class="ni ni-single-02 mr-2"></i> EDIT USER ACCOUNT</h2>
                </div>

                <div class="card-body bg-secondary">
                    <form method="POST" action="{{ $isSelfAccount ? route('users.account.update') : route('users.update', $user->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white"><h5 class="mb-0 text-muted">Account Settings</h5></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 form-group mb-3">
                                        <label class="form-control-label">Username <span class="text-danger">*</span></label>
                                        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $user->username) }}" required>
                                        @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 form-group mb-3">
                                        <label class="form-control-label">Email Address</label>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}">
                                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group mb-3">
                                        <label class="form-control-label">System Role</label>
                                        <input type="text" class="form-control" value="{{ $selectedRole === 'encoding_officer' ? 'ENCODING OFFICER' : strtoupper(str_replace('_', ' ', $selectedRole ?? 'N/A')) }}" readonly>
                                        <small class="form-text text-muted">Role cannot be changed in edit mode.</small>
                                    </div>
                                </div>

                                @if(!$isSelfAccount)
                                    @if(in_array($selectedRole, ['school', 'encoding_officer'], true))
                                        <div class="row">
                                            <div class="col-md-6 form-group mb-3">
                                                <label class="form-control-label">Linked School</label>
                                                <select id="school_id" name="school_id" class="form-control @error('school_id') is-invalid @enderror">
                                                    <option value="">-- Leave Blank to Auto-Create/Assign --</option>
                                                    @foreach($schoolOptions as $school)
                                                        @php
                                                            $allowSchool = (bool) ($school['allow_school'] ?? false);
                                                            $allowEo = (bool) ($school['allow_eo'] ?? false);
                                                            $showOption = ($selectedRole === 'school' && $allowSchool)
                                                                || ($selectedRole === 'encoding_officer' && $allowEo);
                                                        @endphp
                                                        @if($showOption)
                                                            <option value="{{ $school['id'] }}" {{ (string) old('school_id', $user->school_id) === (string) $school['id'] ? 'selected' : '' }}>
                                                                {{ $school['name'] }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                <small class="form-text text-muted">
                                                    @if($selectedRole === 'school')
                                                        If blank: a placeholder school record is created and linked.
                                                    @else
                                                        If blank: EO is linked to HQ.
                                                    @endif
                                                </small>
                                                @error('school_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                    @endif

                                    @if($selectedRole === 'personnel')
                                        <div class="row">
                                            <div class="col-md-6 form-group mb-3">
                                                <label class="form-control-label">Linked Personnel</label>
                                                <select id="personnel_id" name="personnel_id" class="form-control @error('personnel_id') is-invalid @enderror">
                                                    <option value="">-- Leave Blank to Auto-Create --</option>
                                                    @foreach($personnelList as $personnel)
                                                        @php($profile = $personnel->pdsMain)
                                                        <option value="{{ $personnel->id }}" {{ (string) old('personnel_id', $user->personnel_id) === (string) $personnel->id ? 'selected' : '' }}>
                                                            {{ ($profile->last_name ?? 'N/A') . ', ' . ($profile->first_name ?? '') }}{{ $personnel->emp_id ? ' (' . $personnel->emp_id . ')' : '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <small class="form-text text-muted">If blank: a placeholder personnel record is created and linked.</small>
                                                @error('personnel_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white"><h5 class="mb-0 text-muted">Security</h5></div>
                            <div class="card-body">
                                <div class="alert alert-info text-sm mb-4">
                                    <i class="ni ni-bulb-61 mr-2"></i> Leave the password fields blank if you do not want to change the current password.
                                </div>
                                <div class="row">
                                    <div class="col-md-6 form-group mb-3">
                                        <label class="form-control-label">New Password</label>
                                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 form-group mb-3">
                                        <label class="form-control-label">Confirm New Password</label>
                                        <input type="password" name="password_confirmation" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4 mb-3 px-3">
                            <a href="{{ route($cancelRoute) }}" class="btn btn-secondary px-5">Cancel</a>
                            <button type="submit" class="btn btn-success px-5"><i class="ni ni-check-bold mr-2"></i> {{ $isSelfAccount ? 'Update My Account' : 'Update User' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
