@extends('layouts.app')
@section('title', 'ICT Inventory Report')
@section('content')
<div class="container mt-4">
	<div class="row justify-content-center">
		<div class="col-lg-8">
			<div class="card shadow">
				<div class="card-header bg-transparent">
					<h2><i class="ni ni-chart-bar-32"></i> ICT Inventory Report</h2>
				</div>
				<div class="card-body">
					<div class="report-section text-center">
						<div class="report-icon mb-3">
							<i class="fas fa-file-excel fa-3x text-success"></i>
						</div>
						<div class="report-title mb-2" style="font-size: 1.5rem; font-weight: 700; color: #32325d;">
							Generate ICT Inventory Report
						</div>
						<div class="report-description mb-4" style="color: #525f7f;">
							Download a comprehensive Excel report of all ICT equipment inventory including property numbers, item details, serial numbers, acquisition costs, conditions, and assigned personnel.
							@if(auth()->user()->hasRole('school'))
								<br><br>
								<strong>School:</strong> {{ auth()->user()->school->name ?? '' }}
							@endif
						</div>
						@if(auth()->user()->hasRole('admin'))
						<form id="reportForm" method="GET" action="{{ route('report.generate') }}" class="school-filter mb-4" style="max-width:400px;margin:0 auto;">
							<label for="school_id" class="font-weight-bold mb-2">
								<i class="ni ni-square-pin"></i> Filter by School (Optional)
							</label>
							<select id="school_id" name="school_id" class="form-control mb-3">
								<option value="">All Schools</option>
								@foreach($schools as $school)
									<option value="{{ $school->id }}">{{ $school->name }}</option>
								@endforeach
							</select>
							<button type="submit" class="btn btn-success btn-generate">
								<i class="fas fa-download"></i> Generate & Download Report
							</button>
						</form>
						@else
						<form id="reportForm" method="GET" action="{{ route('report.generate') }}">
							<button type="submit" class="btn btn-success btn-generate">
								<i class="fas fa-download"></i> Generate & Download Report
							</button>
						</form>
						@endif
						<div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;">
							<p style="font-size: 14px; color: #8898aa; margin: 0;">
								<i class="fas fa-info-circle"></i> The report will be downloaded as an Excel file (.xlsx)
								that you can open with Microsoft Excel or similar applications.
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
