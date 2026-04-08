@extends('layouts.app')
@section('title', 'Edit Training')

@section('content')
<div class="container-fluid mt-4">
    <form method="POST" action="/training/{{ $training->id }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-xl-11 mx-auto">
                <div class="card shadow mb-4">
                    <div class="card-header border-0 bg-white">
                        <h3 class="mb-0 text-primary">
                            <i class="ni ni-hat-3 mr-2"></i> Edit Training Record
                        </h3>
                    </div>
                    <div class="card-body bg-secondary">
                        
                        <div class="form-group mb-3">
                            <label class="form-control-label">Title / Seminar Name <span class="text-danger">*</span></label>
                            <textarea rows="3" class="form-control @error('title') is-invalid @enderror" name="title" required>{{ old('title', $training->title) }}</textarea>
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-4 form-group mb-3">
                                <label class="form-control-label">Total Hours <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="hours" value="{{ old('hours', $training->hours) }}" required>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label class="form-control-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="start_date" value="{{ old('start_date', $training->start_date) }}" required>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label class="form-control-label">End Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="end_date" value="{{ old('end_date', $training->end_date) }}" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label class="form-control-label">Type <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="type" value="{{ old('type', $training->type) }}" required>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label class="form-control-label">Sponsor <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="sponsor" value="{{ old('sponsor', $training->sponsor) }}" required>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-control-label">Currently Included Personnel</label>
                            <textarea id="selected_names" class="form-control bg-white" rows="6" readonly>{{ $training->personnel && $training->personnel->pdsMain ? ($training->personnel->pdsMain->last_name . ', ' . $training->personnel->pdsMain->first_name) : '' }}</textarea>
                            <small class="text-muted">To change personnel, create a new record for each person.</small>
                        </div>

                        <div class="text-right">
                            <a href="/training" class="btn btn-secondary px-4">Cancel</a>
                            <button type="submit" class="btn btn-success px-5" onclick="return confirm('Are you sure you want to update this training record?')">
                                <i class="fas fa-save mr-2"></i> Update Training
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personnel selection removed: each training record is for a single personnel only -->
        </div>
    </form>
</div>

<script>
    // Search filter for the personnel table
    function filterTable() {
        let val = document.getElementById('empSearch').value.toUpperCase();
        let rows = document.getElementById('empTable').getElementsByTagName('tr');
        for (let i = 0; i < rows.length; i++) {
            let td = rows[i].getElementsByClassName("name-cell")[0];
            if (td) {
                rows[i].style.display = td.textContent.toUpperCase().includes(val) ? "" : "none";
            }
        }
    }
</script>
@endsection