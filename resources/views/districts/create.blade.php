@extends('layouts.app')
@section('title', 'Add District')
@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-white"><h4 class="mb-0">Add New District</h4></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('districts.store') }}">
                        @csrf
                        <div class="form-group">
                            <label for="name">District Name</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required autofocus>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mt-3 d-flex justify-content-between">
                            <a href="{{ route('districts.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
