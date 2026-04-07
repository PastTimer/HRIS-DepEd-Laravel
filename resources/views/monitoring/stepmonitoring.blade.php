@extends('layouts.app')
@section('content')
<div class="container-fluid px-4">
    <div class="card-header bg-transparent">
        <div class="row">
            <div class="col-md-6">
                <h2><img src="{{ asset('assets/img/brand/stepinc.png') }}" width="50" height="50"> STEP MONITORING</h2>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-gradient-primary text-white py-2">
                        <h6 class="mb-0"><i class="fas fa-chart-line"></i> STEP Increment Monitoring</h6>
                    </div>
                    <div class="card-body text-center py-3">
                        <div class="btn-group-custom">
                            <a class="btn btn-primary" style="background-color: #0473B4; border: none;" href="{{ route('monitoring.step.year') }}">
                                <i class="fas fa-calendar-alt"></i> YEAR
                            </a>
                            <a class="btn btn-secondary" href="{{ route('monitoring.step.month') }}">
                                <i class="fas fa-calendar"></i> MONTH - {{ $currentYear }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-gradient-info text-white py-2">
                        <h6 class="mb-0"><i class="fas fa-birthday-cake"></i> Age Monitoring</h6>
                    </div>
                    <div class="card-body text-center py-3">
                        <div class="btn-group-custom">
                            <a class="btn btn-secondary" href="{{ route('monitoring.age.actual') }}">
                                <i class="fas fa-user-clock"></i> ACTUAL
                            </a>
                            <a class="btn btn-secondary" href="{{ route('monitoring.age.year') }}">
                                <i class="fas fa-calendar-check"></i> YEAR - {{ $currentYear }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table id="nameTable" class="table align-items-center table-flush table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>ID No.</th>
                        <th>Name</th>
                        <th class="text-center">Position</th>
                        <th class="text-center">Last Step</th>
                        @foreach($years as $year)
                            <th class="text-center {{ $year == $currentYear ? 'bg-current-year' : '' }}">{{ $year }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($tableData as $row)
                        <tr>
                            <td>{{ $row['emp_id'] }}</td>
                            <td>{{ $row['name'] }}</td>
                            <td class="text-center">{{ $row['position'] }}</td>
                            <td class="text-center">{{ $row['last_step'] }}</td>
                            @foreach($years as $year)
                                <td class="text-center">{{ $row['years'][$year] }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if(isset($personnel) && $personnel->lastPage() > 1)
            <div class="d-flex justify-content-center mt-3">
                <nav>
                    <ul class="pagination">
                        @for ($i = 1; $i <= $personnel->lastPage(); $i++)
                            <li class="page-item {{ $i == $personnel->currentPage() ? 'active' : '' }}">
                                <a class="page-link" href="?page={{ $i }}">{{ $i }}</a>
                            </li>
                        @endfor
                    </ul>
                </nav>
            </div>
            @endif
        </div>
    </div>
</div>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#nameTable').DataTable({
            "order": [[1, 'asc']],
            "paging": false,
            "info": false,
            "lengthChange": false,
            "searching": true,
            "deferRender": true,
            "autoWidth": false,
            "scrollX": true
        });
    });
</script>
<style>
    .bg-current-year {
        background-color: #ffc107 !important;
        color: #fff !important;
    }
</style>
@endsection
