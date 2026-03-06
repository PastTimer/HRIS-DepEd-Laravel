@extends('layouts.app')
@section('title', 'Edit School')
@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-xl-8 col-lg-10 mx-auto">
            <div class="card shadow">
                <div class="card-header border-0">
                    <h3 class="mb-0">Edit School</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="/schools/{{ $school->id }}">
                        @csrf
                        @method('PUT') 

                        <div class="form-group mb-3">
                            <label for="school_id" class="form-control-label">School ID</label>
                            <input type="text" 
                                   id="school_id" 
                                   name="school_id" 
                                   class="form-control @error('school_id') is-invalid @enderror" 
                                   value="{{ old('school_id', $school->school_id) }}" 
                                   required>
                            @error('school_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="name" class="form-control-label">School Name</label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $school->name) }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @php
                            $currentDistrict = old('district', $school->district);
                            $isCustom = !$districts->contains('name', $currentDistrict) && !empty($currentDistrict);
                        @endphp

                        <div class="form-group mb-3">
                            <label for="district" class="form-control-label">District</label>
                            <select id="district" 
                                    name="district" 
                                    class="form-control @error('district') is-invalid @enderror" 
                                    onchange="toggleCustomDistrict()" 
                                    required>
                                <option value="" disabled {{ empty($currentDistrict) ? 'selected' : '' }}>Select District</option>
                                
                                @foreach($districts as $d)
                                    <option value="{{ $d->name }}" {{ $currentDistrict === $d->name ? 'selected' : '' }}>
                                        {{ $d->name }}
                                    </option>
                                @endforeach
                                
                                <option value="Other" {{ $isCustom || old('district') === 'Other' ? 'selected' : '' }}>Other / Custom</option>
                            </select>
                            @error('district')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3" id="custom_district_wrapper" style="display: {{ $isCustom || old('district') === 'Other' ? 'block' : 'none' }};">
                            <label for="custom_district" class="form-control-label">Specify Custom District</label>
                            <input type="text" 
                                   id="custom_district" 
                                   name="custom_district" 
                                   class="form-control @error('custom_district') is-invalid @enderror" 
                                   value="{{ old('custom_district', $isCustom ? $school->district : '') }}"
                                   placeholder="Enter new district name">
                            @error('custom_district')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label for="address" class="form-control-label">Address</label>
                            <textarea id="address" 
                                      name="address" 
                                      rows="3" 
                                      class="form-control @error('address') is-invalid @enderror">{{ old('address', $school->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="/schools" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success">Update School</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleCustomDistrict() {
        var dropdown = document.getElementById('district');
        var customWrapper = document.getElementById('custom_district_wrapper');
        var customInput = document.getElementById('custom_district');

        if (dropdown.value === 'Other') {
            customWrapper.style.display = 'block';
            customInput.setAttribute('required', 'required');
        } else {
            customWrapper.style.display = 'none';
            customInput.removeAttribute('required');
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        toggleCustomDistrict();
    });
</script>
@endsection