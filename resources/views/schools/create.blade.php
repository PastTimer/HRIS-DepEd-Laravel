@extends('layouts.app')
@section('title', 'Add School')
@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-xl-8 col-lg-10 mx-auto">
            <div class="card shadow">
                <div class="card-header border-0">
                    <h3 class="mb-0">Add New School</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="/schools">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="school_id" class="form-control-label">School ID</label>
                            <input type="text" 
                                   id="school_id" 
                                   name="school_id" 
                                   class="form-control @error('school_id') is-invalid @enderror" 
                                   value="{{ old('school_id') }}" 
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
                                   value="{{ old('name') }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        <div class="form-group mb-3">
                            <label for="district_id" class="form-control-label">District</label>
                            <select id="district_id" 
                                    name="district_id" 
                                    class="form-control @error('district_id') is-invalid @enderror" 
                                    required>
                                <option value="" disabled {{ old('district_id') === null ? 'selected' : '' }}>Select District</option>
                                @foreach($districts as $d)
                                    <option value="{{ $d->id }}" {{ old('district_id') == $d->id ? 'selected' : '' }}>
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
                            <button type="submit" class="btn btn-primary">Save School</button>
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