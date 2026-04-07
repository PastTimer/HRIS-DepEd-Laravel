@extends('layouts.app')
@section('content')
<div class="container-fluid mt-4">
    <form method="POST" action="/training" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-xl-8">
                <div class="card shadow">
                    <div class="card-header border-0"><h3>Add New Training</h3></div>
                    <div class="card-body bg-secondary">
                        <div class="form-group"><label>Title</label><textarea name="title" class="form-control" rows="3" required></textarea></div>
                        <div class="row">
                            <div class="col-md-4"><label>Total Hours</label><input type="number" name="hours" class="form-control" required></div>
                            <div class="col-md-4"><label>Date From</label><input type="date" name="date_from" class="form-control" required></div>
                            <div class="col-md-4"><label>Date To</label><input type="date" name="date_to" class="form-control" required></div>
                        </div>
                        <div class="form-group mt-3">
                            <label>Selected Personnel</label>
                            <textarea id="selected_names" class="form-control bg-white" rows="6" readonly placeholder="Select from the list on the right..."></textarea>
                        </div>
                        <div class="form-group">
                            <label>Attachment</label>
                            <input type="file" name="file" class="form-control h-auto">
                        </div>
                        <div class="text-right"><button type="submit" class="btn btn-primary px-5">Save Training</button></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card shadow">
                    <div class="card-header border-0"><input type="text" id="empSearch" class="form-control" placeholder="Search..." onkeyup="filterTable()"></div>
                    <div style="max-height: 600px; overflow-y: auto;">
                        <table class="table" id="empTable">
                            @foreach($employees as $personnel)
                            @php($pds = $personnel->pdsMain)
                            <tr>
                                <td width="40">
                                    <input type="checkbox" name="employee_ids[]" value="{{ $personnel->id }}" 
                                        class="emp-check" 
                                        data-name="{{ $pds->last_name ?? 'N/A' }}, {{ $pds->first_name ?? '' }}">
                                </td>
                                <td class="font-weight-bold">
                                    {{ $pds->last_name ?? 'N/A' }}, {{ $pds->first_name ?? '' }}
                                </td>
                            </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<script>
    function filterTable() {
        let val = document.getElementById('empSearch').value.toUpperCase();
        let rows = document.getElementById('empTable').getElementsByTagName('tr');
        for (let r of rows) r.style.display = r.innerText.toUpperCase().includes(val) ? "" : "none";
    }
    document.querySelectorAll('.emp-check').forEach(cb => cb.addEventListener('change', function() {
        let names = [];
        document.querySelectorAll('.emp-check:checked').forEach((c, i) => names.push((i+1) + ". " + c.dataset.name));
        document.getElementById('selected_names').value = names.join('\n');
    }));
</script>
@endsection