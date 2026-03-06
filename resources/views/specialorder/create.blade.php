@extends('layouts.app')
@section('title', 'Add Special Order')
@section('content')
<style>
    .scrollable-card { max-height: 600px; overflow-y: auto; }
</style>

<div class="container-fluid mt-4">
    <form method="POST" action="/specialorder" enctype="multipart/form-data">
        @csrf
        <div class="row">
            
            <div class="col-xl-8">
                <div class="card shadow mb-4">
                    <div class="card-header border-0 bg-white">
                        <h3 class="mb-0 text-primary"><i class="ni ni-paper-diploma mr-2"></i> Add Special Order</h3>
                    </div>
                    <div class="card-body bg-secondary">
                        
                        <div class="form-group mb-3">
                            <label class="form-control-label">Title / Description <span class="text-danger">*</span></label>
                            <textarea rows="3" class="form-control @error('title') is-invalid @enderror" name="title" required>{{ old('title') }}</textarea>
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label class="form-control-label">SO Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('so_no') is-invalid @enderror" name="so_no" value="{{ old('so_no') }}" placeholder="e.g. 123" required>
                                @error('so_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label class="form-control-label">Series Year <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="series_year" value="{{ old('series_year', date('Y')) }}" required>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-control-label">SO Type <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select name="type" id="sotype" class="form-control @error('type') is-invalid @enderror" onchange="handleTypeChange(this)" required>
                                    <option value="" disabled {{ old('type') === null ? 'selected' : '' }}>--- Select Type ---</option>
                                    <option value="VL" {{ old('type') == 'VL' ? 'selected' : '' }}>VL (Vacation Leave)</option>
                                    <option value="SL" {{ old('type') == 'SL' ? 'selected' : '' }}>SL (Sick Leave)</option>
                                    <option value="custom" {{ old('type') == 'custom' ? 'selected' : '' }}>Other / Custom</option>
                                </select>
                                <input type="text" name="custom_type" id="custom_type" class="form-control" placeholder="Enter custom SO type..." value="{{ old('custom_type') }}" style="{{ old('type') == 'custom' ? 'display: block;' : 'display: none;' }}">
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-control-label">Selected Personnel <span class="text-danger">*</span></label>
                            <textarea rows="6" id="empn_display" class="form-control bg-white" readonly placeholder="Select personnel from the list on the right..."></textarea>
                            @error('employee_ids') <small class="text-danger font-weight-bold mt-2 d-block">Please select at least one employee from the list.</small> @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-control-label">Attachment (PDF/Image)</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="customFile" name="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                <label class="custom-file-label" for="customFile">Choose file</label>
                            </div>
                        </div>

                        <div class="text-right">
                            <a href="/specialorder" class="btn btn-secondary px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save mr-2"></i> Save Special Order</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card shadow">
                    <div class="card-header border-0 pb-2">
                        <h3 class="mb-2">Select Personnel</h3>
                        <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search name..." onkeyup="filterNames()">
                    </div>
                    
                    <div class="table-responsive scrollable-card">
                        <table class="table align-items-center table-flush table-hover" id="nameTable">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center" style="width: 50px;">Select</th>
                                    <th>Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employees as $emp)
                                <tr>
                                    <td class="text-center">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="employee_ids[]" value="{{ $emp->id }}" 
                                                   class="custom-control-input personnel-checkbox" 
                                                   id="check_{{ $emp->id }}" 
                                                   data-name="{{ $emp->last_name }}, {{ $emp->first_name }}"
                                                   {{ (is_array(old('employee_ids')) && in_array($emp->id, old('employee_ids'))) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="check_{{ $emp->id }}"></label>
                                        </div>
                                    </td>
                                    <td class="name-cell font-weight-bold">
                                        {{ $emp->last_name }}, {{ $emp->first_name }}
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
    // 1. Filter Names in the table
    function filterNames() {
        let input = document.getElementById("searchInput").value.toUpperCase();
        let tr = document.getElementById("nameTable").getElementsByTagName("tr");

        for (let i = 1; i < tr.length; i++) { 
            let td = tr[i].getElementsByClassName("name-cell")[0];
            if (td) {
                let txtValue = td.textContent || td.innerText;
                tr[i].style.display = txtValue.toUpperCase().indexOf(input) > -1 ? "" : "none";
            }
        }
    }

    // 2. Toggle custom SO type input
    function handleTypeChange(select) {
        let customInput = document.getElementById('custom_type');
        if (select.value === 'custom') {
            customInput.style.display = 'block';
            customInput.required = true;
            customInput.focus();
        } else {
            customInput.style.display = 'none';
            customInput.required = false;
        }
    }

    // 3. Update Textarea visual feedback
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.personnel-checkbox');
        const displayArea = document.getElementById('empn_display');

        function updateDisplay() {
            let selectedNames = [];
            let counter = 1;
            checkboxes.forEach(cb => {
                if(cb.checked) {
                    selectedNames.push(counter + ". " + cb.getAttribute('data-name'));
                    counter++;
                }
            });
            displayArea.value = selectedNames.join('\n');
        }

        // Run on load to handle validation redirects properly
        updateDisplay(); 

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateDisplay);
        });

        // Update file input label dynamically
        document.querySelector('.custom-file-input').addEventListener('change', function(e) {
            let fileName = e.target.files[0].name;
            let nextSibling = e.target.nextElementSibling;
            nextSibling.innerText = fileName;
        });
    });
</script>
@endsection