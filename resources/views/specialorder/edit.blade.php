@extends('layouts.app')
@section('title', 'Edit Special Order')
@section('content')
<style>
    .scrollable-card { max-height: 600px; overflow-y: auto; }
</style>

<div class="container-fluid mt-4">
    @php
        $isPersonnel = Auth::user() && Auth::user()->hasRole('personnel');
        $selectedPersonnel = collect(old('employee_ids', $specialorder->personnel->pluck('id')->all()))
            ->map(fn ($id) => (int) $id)
            ->all();
    @endphp

    <form method="POST" action="{{ route('specialorder.update', $specialorder) }}">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="{{ $isPersonnel ? 'col-xl-12' : 'col-xl-8' }}">
                <div class="card shadow mb-4">
                    <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
                        <h3 class="mb-0 text-primary"><i class="ni ni-paper-diploma mr-2"></i>Edit Special Order</h3>
                        <div>
                            <a href="{{ route('specialorder.show', $specialorder) }}" class="btn btn-sm btn-outline-info">Back to Details</a>
                            <a href="{{ route('specialorder.index') }}" class="btn btn-sm btn-secondary">Cancel</a>
                        </div>
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

                        <div class="form-group mb-3">
                            <label class="form-control-label">SO Type <span class="text-danger">*</span></label>
                            <select name="type_id" id="type_id" class="form-control @error('type_id') is-invalid @enderror" required>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}" data-value="{{ $type->value }}" {{ (string) old('type_id', $specialorder->type_id) === (string) $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="form-text text-muted">
                                Selected type value: <span id="type-value-preview">--</span>
                            </small>
                        </div>

                        @if($isPersonnel)
                            @if(Auth::user()->personnel_id)
                                <input type="hidden" name="employee_ids[]" value="{{ Auth::user()->personnel_id }}">
                                <div class="alert alert-info mb-3">
                                    This order is linked to your personnel record.
                                </div>
                            @else
                                <div class="alert alert-danger mb-3">
                                    Your account is not linked to a personnel record. Please contact an administrator.
                                </div>
                            @endif
                        @else
                            <div class="form-group mb-3">
                                <label class="form-control-label">Selected Personnel <span class="text-danger">*</span></label>
                                <textarea rows="6" id="selected_personnel_display" class="form-control bg-white" readonly></textarea>
                                <small id="selected_personnel_count" class="form-text text-muted">0 selected</small>
                                @error('employee_ids')
                                    <small class="text-danger font-weight-bold mt-2 d-block">{{ $message }}</small>
                                @enderror
                            </div>
                        @endif

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
                            <a href="{{ route('specialorder.index') }}" class="btn btn-secondary px-4">Cancel</a>
                            <button type="submit" class="btn btn-success px-4"><i class="fas fa-save mr-2"></i>Update Special Order</button>
                        </div>
                    </div>
                </div>
            </div>

            @if(!$isPersonnel)
                <div class="col-xl-4">
                    <div class="card shadow">
                        <div class="card-header border-0 pb-2">
                            <h3 class="mb-2">Select Personnel</h3>
                            <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search name...">
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
                                                    <input
                                                        type="checkbox"
                                                        name="employee_ids[]"
                                                        value="{{ $personnel->id }}"
                                                        class="custom-control-input personnel-checkbox"
                                                        id="check_{{ $personnel->id }}"
                                                        data-name="{{ $pds->last_name ?? 'N/A' }}, {{ $pds->first_name ?? '' }}"
                                                        {{ in_array((int) $personnel->id, $selectedPersonnel, true) ? 'checked' : '' }}
                                                    >
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
            @endif
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('type_id');
    const typeValuePreview = document.getElementById('type-value-preview');

    function updateTypeValuePreview() {
        if (!typeSelect || !typeValuePreview) {
            return;
        }

        const selected = typeSelect.options[typeSelect.selectedIndex];
        const value = selected ? selected.getAttribute('data-value') : null;
        typeValuePreview.textContent = value !== null && value !== '' ? Number(value).toFixed(2) : '--';
    }

    updateTypeValuePreview();
    if (typeSelect) {
        typeSelect.addEventListener('change', updateTypeValuePreview);
    }

    const searchInput = document.getElementById('searchInput');
    const nameRows = document.querySelectorAll('#nameTable tbody tr');
    const checkboxes = document.querySelectorAll('.personnel-checkbox');
    const selectedDisplay = document.getElementById('selected_personnel_display');
    const selectedCount = document.getElementById('selected_personnel_count');

    function updateSelectedPersonnelDisplay() {
        if (!selectedDisplay || !selectedCount) {
            return;
        }

        const checked = Array.from(checkboxes).filter(cb => cb.checked);
        const names = checked.map((cb, index) => `${index + 1}. ${cb.getAttribute('data-name')}`);
        selectedDisplay.value = names.join('\n');
        selectedCount.textContent = `${checked.length} selected`;
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const term = this.value.toUpperCase();
            nameRows.forEach(row => {
                const nameCell = row.querySelector('.name-cell');
                const value = nameCell ? nameCell.textContent.toUpperCase() : '';
                row.style.display = value.includes(term) ? '' : 'none';
            });
        });
    }

    checkboxes.forEach(cb => cb.addEventListener('change', updateSelectedPersonnelDisplay));
    updateSelectedPersonnelDisplay();
});
</script>
@endsection
