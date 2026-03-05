@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="card-header bg-transparent mb-4">
        <h2>DASHBOARD</h2>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card card-stats shadow mb-4">
                <div class="card-body">
                    <h5 class="card-title text-uppercase text-muted mb-0">ACTIVE PERSONNEL</h5>
                    <span class="h2 font-weight-bold mb-0">{{ number_format($activePersonnelCount) }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-stats shadow mb-4">
                <div class="card-body">
                    <h5 class="card-title text-uppercase text-muted mb-0">ACTIVE STATION</h5>
                    <span class="h2 font-weight-bold mb-0">{{ number_format($activeSchoolsCount) }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-stats shadow mb-4">
                <div class="card-body">
                    <h5 class="card-title text-uppercase text-muted mb-0">ASSIGN ≠ DEPLOYMENT</h5>
                    <span class="h2 font-weight-bold mb-0">{{ number_format($diffStationCount) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection