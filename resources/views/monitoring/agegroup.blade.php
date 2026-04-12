@extends('layouts.app')
@section('content')
<div class="container-fluid px-4">
    <div class="card-header bg-transparent">
        <div class="row">
            <div class="col-md-6">
                <h2>PERSONNEL'S AGE</h2>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-info text-center" style="background: linear-gradient(87deg, #11cdef 0, #1171ef 100%); border: none;">
                    <h3 class="text-white mb-0">
                        AGE - {{ $age ?? 'All' }} {{ $age !== null ? 'Years' : '' }} ({{ strtoupper($mode) }})
                    </h3>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card card-stats shadow mb-4 mb-xl-0 h-100">
                    <div class="card-body">
                        <h5 class="card-title">Active Personnel</h5>
                        <h2 class="mb-0">{{ $rows->count() }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow mb-4 mb-xl-0 h-100">
                    <div class="card-header bg-transparent py-2">
                        <h6 class="text-uppercase text-muted ls-1 mb-0">Employment Type</h6>
                    </div>
                    <div class="card-body p-2">
                        <div id="piechart" style="height: 100px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow mb-4 mb-xl-0 h-100">
                    <div class="card-header bg-transparent py-2">
                        <h6 class="text-uppercase text-muted ls-1 mb-0">Gender Distribution</h6>
                    </div>
                    <div class="card-body p-2">
                        <div id="gender_piechart" style="height: 100px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <a href="{{ $mode === 'year' ? route('monitoring.age.year') : route('monitoring.age.actual') }}" class="btn btn-secondary btn-sm">
                Back to {{ $mode === 'year' ? 'Year View' : 'Actual View' }}
            </a>
        </div>

        <div class="card ppc-card shadow-sm mb-4 mt-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-items-center table-flush" id="nameTable">
                        <thead class="thead-light">
                            <tr>
                                <th>ID No.</th>
                                <th>Name</th>
                                <th>Birthdate</th>
                                <th>Gender</th>
                                <th>Position</th>
                                <th>Employment Type</th>
                                <th>Station</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                <tr>
                                    <td>{{ $row['emp_id'] }}</td>
                                    <td>{{ $row['name'] }}</td>
                            <td>{{ $row['birth_date'] }}</td>
                            <td>{{ $row['gender'] }}</td>
                            <td>{{ $row['position'] }}</td>
                            <td>{{ $row['employee_type'] }}</td>
                            <td>{{ $row['station'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @php
                $total = isset($total) ? $total : (isset($rows) ? $rows->count() : 0);
                $perPage = isset($perPage) ? $perPage : 10;
                $currentPage = isset($currentPage) ? $currentPage : (request('page', 1));
                $totalPages = ceil($total / $perPage);
            @endphp
            @if($totalPages > 1)
            <div class="d-flex justify-content-center mt-3">
                <nav>
                    <ul class="pagination">
                        @for ($i = 1; $i <= $totalPages; $i++)
                            <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                <a class="page-link" href="?page={{ $i }}">{{ $i }}</a>
                            </li>
                        @endfor
                    </ul>
                </nav>
                <span class="text-muted ms-3">Page {{ $currentPage }} of {{ $totalPages }} (showing 10 per page)</span>
            </div>
            @endif
        </div>
    </div>
</div>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script>
    google.charts.load('current', {'packages': ['corechart']});
    google.charts.setOnLoadCallback(drawCharts);

    function drawCharts() {
        var etypeData = google.visualization.arrayToDataTable({!! $etypeChart !!});
        var genderData = google.visualization.arrayToDataTable({!! $genderChart !!});
        var options = {
            legend: { position: 'right', textStyle: { fontSize: 9 } },
            chartArea: { width: '100%', height: '100%' },
            height: 100
        };

        var piechart = new google.visualization.PieChart(document.getElementById('piechart'));
        piechart.draw(etypeData, options);

        var genderPie = new google.visualization.PieChart(document.getElementById('gender_piechart'));
        genderPie.draw(genderData, options);
    }

    $(document).ready(function() {
        $('#nameTable').DataTable({
            paging: false,
            info: false,
            lengthChange: false,
            searching: true,
            order: [[1, 'asc']],
            language: {
                search: 'Quick Search:',
                lengthMenu: '_MENU_ records'
            },
            deferRender: true,
            autoWidth: false,
            scrollX: true
        });
    });
</script>
@endsection
