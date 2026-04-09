@extends('layouts.app')
@section('title', 'Create Order Type')
@section('content')
<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-xl-6">
            <div class="card shadow">
                <div class="card-header border-0 bg-white">
                    <h3 class="mb-0 text-primary"><i class="fas fa-plus mr-2"></i> Create Order Type</h3>
                </div>
                <div class="card-body bg-secondary">
                    <form method="POST" action="{{ route('specialorder.types.store') }}">
                        @csrf
                        <div class="form-group mb-3">
                            <label class="form-control-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-control-label">Value <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="value" value="{{ old('value', 1) }}" class="form-control @error('value') is-invalid @enderror" required>
                            @error('value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="text-right">
                            <a href="{{ route('specialorder.types.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Type</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
