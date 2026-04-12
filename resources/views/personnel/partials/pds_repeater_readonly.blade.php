@php
    $renderRows = $rows ?? [];
@endphp

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">{{ $title }}</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="thead-light">
                    <tr>
                        @foreach($fields as $field)
                            <th>{{ $field['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($renderRows as $row)
                        <tr>
                            @foreach($fields as $field)
                                @php
                                    $fieldName = $field['name'];
                                    $fieldType = $field['type'] ?? 'text';
                                    $value = $row[$fieldName] ?? null;
                                @endphp
                                <td>
                                    @if($fieldType === 'boolean')
                                        @if($value === null || $value === '')
                                            <span class="text-muted">--</span>
                                        @else
                                            {{ (string) $value === '1' || $value === true ? 'Yes' : 'No' }}
                                        @endif
                                    @else
                                        {{ $value !== null && $value !== '' ? $value : '--' }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($fields) }}" class="text-center text-muted py-4">No records.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
