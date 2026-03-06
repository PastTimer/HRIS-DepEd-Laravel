@extends('layouts.app')
@section('title', 'Edit Designation')
@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-xl-8 col-lg-10 mx-auto">
            <div class="card shadow">
                <div class="card-header border-0">
                    <h3 class="mb-0">Edit Designation</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="/designations/{{ $designation->id }}">
                        @csrf
                        @method('PUT') 

                        <div class="form-group mb-3">
                            <label for="title" class="form-control-label">Title</label>
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   value="{{ old('title', $designation->title) }}" 
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="type" class="form-control-label">Type</label>
                            <select id="type" 
                                    name="type" 
                                    class="form-control @error('type') is-invalid @enderror" 
                                    required>
                                <option value="" disabled>Select Type</option>
                                <option value="teaching" {{ old('type', $designation->type) === 'teaching' ? 'selected' : '' }}>Teaching</option>
                                <option value="nonteaching" {{ old('type', $designation->type) === 'nonteaching' ? 'selected' : '' }}>Non-Teaching</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label for="description" class="form-control-label">Description</label>
                            <textarea id="description" name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $designation->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="/designations" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success">Update Designation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection