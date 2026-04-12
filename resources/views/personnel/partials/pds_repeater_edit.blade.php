@php
    $renderRows = old($key, $rows ?? []);
    if (empty($renderRows)) {
        $renderRows = [[]];
    }
@endphp

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ $title }}</h5>
        <button type="button" class="btn btn-sm btn-success" onclick="addPdsRow('{{ $key }}')">
            <i class="fas fa-plus mr-1"></i> Add Row
        </button>
    </div>
    <div class="card-body">
        <div id="rows-{{ $key }}">
            @foreach($renderRows as $index => $row)
                <div class="border rounded p-3 mb-3 pds-row">
                    <div class="d-flex justify-content-end mb-2">
                        <button type="button" class="btn btn-sm btn-outline-danger" data-remove-row>&times;</button>
                    </div>
                    <div class="row">
                        @foreach($fields as $field)
                            @php
                                $fieldName = $field['name'];
                                $fieldType = $field['type'] ?? 'text';
                                $value = $row[$fieldName] ?? '';
                            @endphp
                            <div class="{{ $fieldType === 'textarea' ? 'col-md-12' : 'col-md-4' }} mb-3">
                                <label class="form-control-label">{{ $field['label'] }}</label>
                                @if($fieldType === 'textarea')
                                    <textarea class="form-control" name="{{ $key }}[{{ $index }}][{{ $fieldName }}]" rows="2">{{ $value }}</textarea>
                                @elseif($fieldType === 'boolean')
                                    <select class="form-control" name="{{ $key }}[{{ $index }}][{{ $fieldName }}]">
                                        <option value="" {{ $value === '' || $value === null ? 'selected' : '' }}>-</option>
                                        <option value="1" {{ (string) $value === '1' ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{ (string) $value === '0' ? 'selected' : '' }}>No</option>
                                    </select>
                                @else
                                    <input type="{{ $fieldType }}" class="form-control" name="{{ $key }}[{{ $index }}][{{ $fieldName }}]" value="{{ $value }}">
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<script type="text/template" id="tpl-{{ $key }}">
    <div class="border rounded p-3 mb-3 pds-row">
        <div class="d-flex justify-content-end mb-2">
            <button type="button" class="btn btn-sm btn-outline-danger" data-remove-row>&times;</button>
        </div>
        <div class="row">
            @foreach($fields as $field)
                @php
                    $fieldName = $field['name'];
                    $fieldType = $field['type'] ?? 'text';
                @endphp
                <div class="{{ $fieldType === 'textarea' ? 'col-md-12' : 'col-md-4' }} mb-3">
                    <label class="form-control-label">{{ $field['label'] }}</label>
                    @if($fieldType === 'textarea')
                        <textarea class="form-control" name="{{ $key }}[__INDEX__][{{ $fieldName }}]" rows="2"></textarea>
                    @elseif($fieldType === 'boolean')
                        <select class="form-control" name="{{ $key }}[__INDEX__][{{ $fieldName }}]">
                            <option value="" selected>-</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    @else
                        <input type="{{ $fieldType }}" class="form-control" name="{{ $key }}[__INDEX__][{{ $fieldName }}]" value="">
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</script>
