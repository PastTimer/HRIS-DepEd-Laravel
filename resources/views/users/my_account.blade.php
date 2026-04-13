@extends('layouts.app')
@section('title', 'My Account')

@section('content')
@php
    $roleName = $user->getRoleNames()->first();
@endphp
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-xl-8 col-lg-10 mx-auto">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="card shadow mb-4">
                <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
                    <h3 class="mb-0 text-primary"><i class="ni ni-circle-08 mr-2"></i> My Account</h3>
                    <a href="{{ route('users.account.edit') }}" class="btn btn-sm btn-info">
                        <i class="fas fa-edit mr-1"></i> Edit Account
                    </a>
                </div>
                <div class="card-body bg-secondary">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-control-label text-muted">Username</label>
                            <div class="font-weight-bold">{{ $user->username }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-control-label text-muted">Email</label>
                            <div class="font-weight-bold">{{ $user->email ?: 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-control-label text-muted">Role</label>
                            <div class="font-weight-bold">{{ $roleName === 'encoding_officer' ? 'ENCODING OFFICER' : strtoupper(str_replace('_', ' ', $roleName ?? 'N/A')) }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-control-label text-muted">Status</label>
                            <div class="font-weight-bold">{{ strtoupper($user->status ?? 'N/A') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
