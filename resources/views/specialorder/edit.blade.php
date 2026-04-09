@extends('layouts.app')
@section('title', 'Edit Special Order')
@section('content')
<style>
    .scrollable-card { max-height: 600px; overflow-y: auto; }
</style>

<div class="container-fluid mt-4">
    @php
        $selectedPersonnelIds = old('employee_ids', $specialorder->personnel->pluck('id')->all());
        $defaultUnits = old('units', optional($specialorder->personnel->first()?->pivot)->units ?? ($specialorder->type->value ?? null));
    @endphp

    <form method="POST" action="{{ route('specialorder.update', $specialorder) }}">
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
                            <label class="form-control-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', $specialorder->title) }}" required>
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-control-label">Description</label>
                            <textarea rows="3" class="form-control @error('description') is-invalid @enderror" name="description">{{ old('description', $specialorder->description) }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label class="form-control-label">SO Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('so_number') is-invalid @enderror" name="so_number" value="{{ old('so_number', $specialorder->so_number) }}" required>
                                @error('so_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label class="form-control-label">Series Year <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('series_year') is-invalid @enderror" name="series_year" value="{{ old('series_year', $specialorder->series_year) }}" required>
                                @error('series_year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 form-group mb-3">
                                <label class="form-control-label">SO Type <span class="text-danger">*</span></label>
                                <select name="type_id" id="type_id" class="form-control @error('type_id') is-invalid @enderror" required>
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}" data-default-value="{{ $type->value }}" {{ (string) old('type_id', $specialorder->type_id) === (string) $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-control-label">Selected Personnel <span class="text-danger">*</span></label>
                            <textarea rows="6" id="empn_display" class="form-control bg-white" readonly></textarea>
                            @error('employee_ids') <small class="text-danger font-weight-bold mt-2 d-block">Please select at least one personnel entry from the list.</small> @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-control-label">Selected Personnel & Units <span class="text-danger">*</span></label>
                            <div id="selected-personnel-list">
                                <p class="text-muted">Select personnel from the list on the right. You can set custom units for each below.</p>
                                <table class="table table-bordered table-sm" style="background:#fff;">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th style="width:120px;">Units</th>
                                        </tr>
                                    </thead>
                                    <tbody id="selected-personnel-table-body">
                                        <!-- JS will populate rows here -->
                                    </tbody>
                                </table>
                            </div>
                            @error('employee_ids') <small class="text-danger font-weight-bold mt-2 d-block">Please select at least one personnel entry from the list.</small> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="set_units_for_all" name="set_units_for_all" checked>
                                <label class="form-check-label" for="set_units_for_all">
                                    Set units for all selected personnel
                                </label>
                            </div>
                            <label class="form-control-label mt-2">Units</label>
                            <input type="number" step="0.01" id="units" name="units" class="form-control @error('units') is-invalid @enderror" value="{{ $defaultUnits }}" placeholder="Defaults to selected type value">
                            @error('units') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-control-label">Status</label>
                            <div>
                                @if($specialorder->status === 'Approved')
                                    <span class="badge badge-success">Approved</span>
                                @elseif($specialorder->status === 'Rejected')
                                    <span class="badge badge-danger">Rejected</span>
                                @else
                                    <span class="badge badge-warning">Pending</span>
                                @endif
                            </div>
                        </div>

                        <div class="text-right">
                            <a href="{{ route('specialorder.index') }}" class="btn btn-secondary">Cancel</a>
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
                                @foreach($employees as $personnel)
                                @php($pds = $personnel->pdsMain)
                                <tr>
                                    <td class="text-center">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="employee_ids[]" value="{{ $personnel->id }}" 
                                                   class="custom-control-input personnel-checkbox" 
                                                   id="check_{{ $personnel->id }}" 
                                                   data-name="{{ $pds->last_name ?? 'N/A' }}, {{ $pds->first_name ?? '' }}"
                                                   {{ in_array($personnel->id, $selectedPersonnelIds) ? 'checked' : '' }}>
                                              <label class="custom-control-label" for="check_{{ $personnel->id }}"></label>
                                            <label class="custom-control-label" for="check_{{ $personnel->id }}"></label>
                                        </div>
                                    </td>
                                    <td class="name-cell font-weight-bold">{{ $pds->last_name ?? 'N/A' }}, {{ $pds->first_name ?? '' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form>
        </form>

        <script>
        // Collect personnel data for JS
        const allPersonnel = [
            @foreach($employees as $personnel)
            {
                id: {{ $personnel->id }},
                name: @json(($personnel->pdsMain->last_name ?? 'N/A') . ', ' . ($personnel->pdsMain->first_name ?? '')),
                defaultUnits: {{ (float) ($personnel->pivot->units ?? $defaultUnits) }}
            },
            @endforeach
        ];

        function getDefaultUnits() {
            let sel = document.getElementById('type_id');
            let opt = sel.options[sel.selectedIndex];
            return parseFloat(opt.getAttribute('data-default-value')) || 1;
        }

        function updateSelectedPersonnelTable() {
            const checked = document.querySelectorAll('.personnel-checkbox:checked');
            const tbody = document.getElementById('selected-personnel-table-body');
            tbody.innerHTML = '';
            let defaultUnits = getDefaultUnits();
            let setForAll = document.getElementById('set_units_for_all').checked;
            let unitsVal = document.getElementById('units').value || defaultUnits;
            let oldVal = @json(old('units_per_personnel', []));
            checked.forEach(cb => {
                let pid = cb.value;
                let person = allPersonnel.find(p => p.id == pid);
                let name = person ? person.name : 'Unknown';
                let val = setForAll ? unitsVal : (oldVal && oldVal[pid] ? oldVal[pid] : defaultUnits);
                tbody.innerHTML += `<tr><td>${name}<input type="hidden" name="employee_ids[]" value="${pid}"></td><td><input type="number" step="0.01" min="0" class="form-control form-control-sm units-input" name="units_per_personnel[${pid}]" value="${val}" ${setForAll ? 'readonly' : ''}></td></tr>`;
            });
        }

        document.querySelectorAll('.personnel-checkbox').forEach(cb => {
            cb.addEventListener('change', updateSelectedPersonnelTable);
        });
        document.getElementById('type_id').addEventListener('change', updateSelectedPersonnelTable);
        document.getElementById('units').addEventListener('input', updateSelectedPersonnelTable);
        document.getElementById('set_units_for_all').addEventListener('change', function() {
            updateSelectedPersonnelTable();
            document.getElementById('units').disabled = !this.checked;
        });
        window.addEventListener('DOMContentLoaded', function() {
            document.getElementById('units').disabled = !document.getElementById('set_units_for_all').checked;
            updateSelectedPersonnelTable();
        });
        </script>
</div>

<script>
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
        const typeSelect = document.getElementById('type_id');
        const unitsInput = document.getElementById('units');
        let unitsTouched = unitsInput.value !== '';

        function applyDefaultUnitsFromType() {
            const selected = typeSelect.options[typeSelect.selectedIndex];
            if (!selected) {
                return;
            }

            const defaultValue = selected.getAttribute('data-default-value');
            if (!unitsTouched || unitsInput.value === '') {
                unitsInput.value = defaultValue ?? '';
            }
        }

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

        updateDisplay();
        applyDefaultUnitsFromType();
        checkboxes.forEach(cb => cb.addEventListener('change', updateDisplay));

        unitsInput.addEventListener('input', function () {
            unitsTouched = true;
        });

        typeSelect.addEventListener('change', function () {
            applyDefaultUnitsFromType();
        });
    });
</script>
@endsection