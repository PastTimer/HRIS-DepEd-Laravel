@extends('layouts.app')
@section('title', 'Edit Special Order')
@section('content')
<style>
    .scrollable-card { max-height: 600px; overflow-y: auto; }
</style>

<div class="container-fluid mt-4">
    <form method="POST" action="/specialorder/{{ $specialorder->id }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-xl-8">
                <div class="card shadow mb-4">
                    <div class="card-header border-0 bg-white">
                        <h3 class="mb-0 text-primary"><i class="ni ni-paper-diploma mr-2"></i> Edit Special Order</h3>
                    </div>
                    <div class="card-body bg-secondary">
                        
                        <div class="form-group mb-3">
                            <label class="form-control-label">Title / Description <span class="text-danger">*</span></label>
                            <textarea rows="3" class="form-control" name="title" required>{{ old('title', $specialorder->title) }}</textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label class="form-control-label">SO Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="so_no" value="{{ old('so_no', $specialorder->so_no) }}" required>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label class="form-control-label">Series Year <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="series_year" value="{{ old('series_year', $specialorder->series_year) }}" required>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-control-label">SO Type <span class="text-danger">*</span></label>
                            <div class="input-group">
                                @php 
                                    $isStandard = in_array($specialorder->type, ['VL', 'SL']);
                                @endphp
                                <select name="type" id="sotype" class="form-control" onchange="handleTypeChange(this)" required>
                                    <option value="VL" {{ old('type', $specialorder->type) == 'VL' ? 'selected' : '' }}>VL (Vacation Leave)</option>
                                    <option value="SL" {{ old('type', $specialorder->type) == 'SL' ? 'selected' : '' }}>SL (Sick Leave)</option>
                                    <option value="custom" {{ !$isStandard ? 'selected' : '' }}>Other / Custom</option>
                                </select>
                                <input type="text" name="custom_type" id="custom_type" class="form-control" placeholder="Enter custom type..." 
                                       value="{{ !$isStandard ? $specialorder->type : '' }}" 
                                       style="{{ !$isStandard ? 'display: block;' : 'display: none;' }}">
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-control-label">Selected Personnel</label>
                            <textarea rows="6" id="empn_display" class="form-control bg-white" readonly></textarea>
                        </div>

                        <div class="text-right">
                            <a href="/specialorder" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success"><i class="fas fa-save mr-2"></i> Update Special Order</button>
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
                                                   {{ $specialorder->employees->contains($emp->id) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="check_{{ $emp->id }}"></label>
                                        </div>
                                    </td>
                                    <td class="name-cell font-weight-bold">{{ $emp->last_name }}, {{ $emp->first_name }}</td>
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
    function handleTypeChange(select) {
        document.getElementById('custom_type').style.display = (select.value === 'custom') ? 'block' : 'none';
    }

    function filterNames() {
        let input = document.getElementById("searchInput").value.toUpperCase();
        let tr = document.getElementById("nameTable").getElementsByTagName("tr");
        for (let i = 1; i < tr.length; i++) {
            let td = tr[i].getElementsByClassName("name-cell")[0];
            if (td) {
                tr[i].style.display = td.textContent.toUpperCase().includes(input) ? "" : "none";
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.personnel-checkbox');
        const displayArea = document.getElementById('empn_display');

        function updateDisplay() {
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

        updateDisplay(); // Shows existing personnel on load
        checkboxes.forEach(cb => cb.addEventListener('change', updateDisplay));
    });
</script>
@endsection