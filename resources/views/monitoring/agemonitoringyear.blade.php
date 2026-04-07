@extends('layouts.app')
@section('content')
<div class="container-fluid px-4">
    <div class="card-header bg-transparent">
        <div class="row">
            <div class="col-md-6">
                <h2><img src="{{ asset('assets/img/brand/stepinc.png') }}" width="50" height="50"> AGE MONITORING</h2>
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
                            <a class="btn btn-secondary" href="{{ route('monitoring.step.year') }}">
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
                            <a class="btn btn-primary" style="background-color: #0473B4; border: none;" href="{{ route('monitoring.age.year') }}">
                                <i class="fas fa-calendar-check"></i> YEAR - {{ $currentYear }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-transparent py-2">
                        <h6 class="text-uppercase text-muted ls-1 mb-0">Personnel Age Distribution (Year {{ $currentYear }})</h6>
                    </div>
                    <div class="card-body p-2">
                        <div id="age_barchart" style="height: 350px;"></div>
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
                        <th class="text-center">Birthdate</th>
                        <th class="text-center">Age</th>
                        <th class="text-center">Position</th>
                        <th>Station</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr>
                            <td>{{ $row['emp_id'] }}</td>
                            <td>{{ $row['name'] }}</td>
                            <td class="text-center">{{ $row['birth_date'] }}</td>
                            <td class="text-center {{ $row['age'] >= 55 ? 'bg-retirement-age' : '' }}">{{ $row['age'] }}</td>
                            <td class="text-center">{{ $row['position'] }}</td>
                            <td>{{ $row['station'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-center mt-3">
                @php
                    $totalPages = ceil($total / $perPage);
                @endphp
                <nav>
                    <ul class="pagination">
                        @for ($i = 1; $i <= $totalPages; $i++)
                            <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                <a class="page-link" href="?page={{ $i }}">{{ $i }}</a>
                            </li>
                        @endfor
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script>
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawAgeChart);

    function drawAgeChart() {
        var data = google.visualization.arrayToDataTable({!! $ageChart !!});
        var options = {
            legend: { position: 'none' },
            chartArea: { width: '95%', height: '80%' },
            orientation: 'horizontal',
            height: 350
        };

        var chart = new google.visualization.BarChart(document.getElementById('age_barchart'));
        google.visualization.events.addListener(chart, 'select', function() {
            var selected = chart.getSelection()[0];
            if (!selected) {
                return;
            }

            var age = data.getValue(selected.row, 0);
            if (age !== null) {
                window.location.href = "{{ route('monitoring.age.group') }}?mode=year&age=" + encodeURIComponent(age);
            }
        });

        chart.draw(data, options);
    }

    $(document).ready(function() {
        $('#nameTable').DataTable({
            order: [[1, 'asc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, 250, -1], [10, 25, 50, 100, 250, 'All']],
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
<style>
    .btn-group-custom .btn {
        margin: 0 5px;
    }

    .bg-retirement-age {
        background-color: #ffd700 !important;
    }
</style>
@endsection
