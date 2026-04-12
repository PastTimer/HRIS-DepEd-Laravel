@extends('layouts.app')
@section('title', 'Edit Training')

@section('content')

<div class="container-fluid mt-4">
    <form method="POST" action="/training/{{ $training->id }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @php $isPersonnel = Auth::user() && Auth::user()->hasRole('personnel'); @endphp
        <div class="row">
            <div class="col-xl-8 mx-auto">
                <div class="card shadow mb-4">
                    <div class="card-header border-0 bg-white">
                        <h3 class="mb-0 text-primary">
                            <i class="ni ni-hat-3 mr-2"></i> Edit Training Record
                        </h3>
                    </div>
                    <div class="card-body bg-secondary">
                        @php $canEdit = !$isPersonnel || ($isPersonnel && $training->verification_status === 'pending'); @endphp
                        <div class="form-group mb-3">
                            <label class="form-control-label">Title / Seminar Name <span class="text-danger">*</span></label>
                            <textarea rows="3" class="form-control @error('title') is-invalid @enderror" name="title" required @if($isPersonnel && !$canEdit) disabled @endif>{{ old('title', $training->title) }}</textarea>
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-4 form-group mb-3">
                                <label class="form-control-label">Total Hours <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="hours" value="{{ old('hours', $training->hours) }}" required @if($isPersonnel && !$canEdit) disabled @endif>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label class="form-control-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="start_date" value="{{ old('start_date', $training->start_date) }}" required @if($isPersonnel && !$canEdit) disabled @endif>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label class="form-control-label">End Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="end_date" value="{{ old('end_date', $training->end_date) }}" required @if($isPersonnel && !$canEdit) disabled @endif>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label class="form-control-label">Type <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="type" value="{{ old('type', $training->type) }}" required @if($isPersonnel && !$canEdit) disabled @endif>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label class="form-control-label">Sponsor <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="sponsor" value="{{ old('sponsor', $training->sponsor) }}" required @if($isPersonnel && !$canEdit) disabled @endif>
                            </div>
                        </div>
                        @if($isPersonnel)
                            <input type="hidden" name="employee_ids[]" value="{{ Auth::user()->personnel_id }}">
                        @endif
                        <div class="text-right">
                            <a href="/training" class="btn btn-secondary px-4">Cancel</a>
                            @if($canEdit)
                            <button type="submit" class="btn btn-success px-5" onclick="return confirm('Are you sure you want to update this training record?')">
                                <i class="fas fa-save mr-2"></i> Update Training
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection