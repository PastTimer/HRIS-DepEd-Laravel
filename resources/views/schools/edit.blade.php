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
                            $currentDistrictId = old('district_id', $school->district_id);
                        @endphp

                        <div class="form-group mb-3">
                            <label for="district_id" class="form-control-label">District</label>
                            <select id="district_id" 
                                    name="district_id" 
                                    class="form-control @error('district_id') is-invalid @enderror" 
                                    required>
                                <option value="" disabled {{ empty($currentDistrictId) ? 'selected' : '' }}>Select District</option>
                                @foreach($districts as $d)
                                    <option value="{{ $d->id }}" {{ $currentDistrictId == $d->id ? 'selected' : '' }}>
                                        {{ $d->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('district_id')
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

</script>
@endsection