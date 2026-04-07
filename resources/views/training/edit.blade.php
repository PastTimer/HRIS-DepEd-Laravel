@extends('layouts.app')
@section('title', 'Edit Training')

@section('content')
<div class="container-fluid mt-4">
    <form method="POST" action="/training/{{ $training->id }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-xl-8">
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
                                <label class="form-control-label">Date From <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="date_from" value="{{ old('date_from', $training->date_from) }}" required>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label class="form-control-label">Date To <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="date_to" value="{{ old('date_to', $training->date_to) }}" required>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-control-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="pending" {{ old('status', $training->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ old('status', $training->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="denied" {{ old('status', $training->status) == 'denied' ? 'selected' : '' }}>Denied</option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-control-label">Currently Included Personnel</label>
                            <textarea id="selected_names" class="form-control bg-white" rows="6" readonly></textarea>
                            <small class="text-muted">Use the list on the right to add or remove people from this training.</small>
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-control-label">Update Attachment (Optional)</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="customFile" name="file">
                                <label class="custom-file-label" for="customFile">Choose new file...</label>
                            </div>
                            @if($training->file_path)
                                <div class="mt-3 p-2 bg-white rounded border d-flex align-items-center justify-content-between">
                                    <span>
                                        <i class="fas fa-file-pdf text-danger mr-2"></i> 
                                        <strong>Current:</strong> {{ basename($training->file_path) }}
                                    </span>
                                    <a href="{{ asset('storage/' . $training->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">View File</a>
                                </div>
                            @endif
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

            <div class="col-xl-4">
                <div class="card shadow">
                    <div class="card-header border-0 pb-2">
                        <h4 class="mb-2">Select Personnel</h4>
                        <input type="text" id="empSearch" class="form-control form-control-sm" placeholder="Search name..." onkeyup="filterTable()">
                    </div>
                    <div style="max-height: 620px; overflow-y: auto;">
                        <table class="table align-items-center table-flush table-hover" id="empTable">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center" style="width: 50px;">Included</th>
                                    <th>Personnel Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employees as $personnel)
                                @php($pds = $personnel->pdsMain)
                                <tr>
                                    <td class="text-center">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="employee_ids[]" value="{{ $personnel->id }}" 
                                                   class="custom-control-input emp-check" 
                                                   id="check_{{ $personnel->id }}" 
                                                   data-name="{{ $pds->last_name ?? 'N/A' }}, {{ $pds->first_name ?? '' }}"
                                                   {{ $training->employees->contains($personnel->id) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="check_{{ $personnel->id }}"></label>
                                        </div>
                                    </td>
                                    <td class="name-cell font-weight-bold" style="font-size: 0.85rem;">
                                        {{ $pds->last_name ?? 'N/A' }}, {{ $pds->first_name ?? '' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    // Search filter for the personnel table
    function filterTable() {
        let val = document.getElementById('empSearch').value.toUpperCase();
        let rows = document.getElementById('empTable').getElementsByTagName('tr');
        for (let i = 1; i < rows.length; i++) {
            let td = rows[i].getElementsByClassName("name-cell")[0];
            if (td) {
                rows[i].style.display = td.textContent.toUpperCase().includes(val) ? "" : "none";
            }
        }
    }

    // Interactive name preview logic
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.emp-check');
        const displayArea = document.getElementById('selected_names');

        function updateList() {
            let names = [];
            let count = 1;
            checkboxes.forEach(cb => {
                if(cb.checked) {
                    names.push(count + ". " + cb.getAttribute('data-name'));
                    count++;
                }
            });
            displayArea.value = names.join('\n');
        }

        // Initialize display on load
        updateList(); 

        // Update on every checkbox change
        checkboxes.forEach(cb => cb.addEventListener('change', updateList));

        // File input dynamic label
        document.querySelector('.custom-file-input').addEventListener('change', function(e) {
            let fileName = e.target.files[0].name;
            e.target.nextElementSibling.innerText = fileName;
        });
    });
</script>
@endsection