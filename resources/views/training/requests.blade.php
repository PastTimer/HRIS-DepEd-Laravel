@extends('layouts.app')
@section('title', 'Training Requests')

@section('content')
<div class="container-fluid mt-4" data-ajax-content>
    <div class="row">
        <div class="col">
            <div class="card shadow border-0">

                <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
                    <h3 class="mb-0"><i class="fas fa-tasks mr-2 text-warning"></i> Requests</h3>
                    <div class="d-flex align-items-center">
                        <form action="{{ route('training.requests') }}" method="GET" class="mr-3 mb-0" id="ajax-search-form">
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" style="min-width: 220px;" placeholder="Search title, personnel, type..." value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    @if(request('search'))
                                        <a href="{{ route('training.requests') }}" class="btn btn-outline-danger" title="Clear Search">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                        <a href="{{ route('training.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Training
                        </a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success m-3 alert-dismissible fade show" role="alert">
                        <strong>Success!</strong> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger m-3 alert-dismissible fade show" role="alert">
                        <strong>Error:</strong> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="table-responsive" id="ajax-table-content">
                    <table class="table align-items-center table-flush table-hover">
                        @push('scripts')
                        <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const form = document.getElementById('ajax-search-form');
                            if (form) {
                                form.addEventListener('submit', function (e) {
                                    e.preventDefault();
                                    const url = form.action + '?' + new URLSearchParams(new FormData(form)).toString();
                                    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                                        .then(response => response.text())
                                        .then(html => {
                                            // Try to extract just the table content if possible
                                            const parser = new DOMParser();
                                            const doc = parser.parseFromString(html, 'text/html');
                                            const newTable = doc.getElementById('ajax-table-content');
                                            if (newTable) {
                                                document.getElementById('ajax-table-content').innerHTML = newTable.innerHTML;
                                            } else {
                                                // fallback: replace whole content
                                                document.getElementById('ajax-table-content').innerHTML = html;
                                            }
                                        });
                                });
                            }
                        });
                        </script>
                        @endpush
                        <thead class="thead-light">
                            <tr class="text-uppercase">
                                <th>Personnel</th>
                                <th>Title</th>
                                <th class="text-center">Type</th>
                                <th class="text-center">Sponsor</th>
                                <th class="text-center">Total Hours</th>
                                <th class="text-center">Start Date</th>
                                <th class="text-center">End Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingTrainings as $tr)
                            <tr>
                                <td>
                                    @if($tr->personnel && $tr->personnel->pdsMain)
                                        <span class="font-weight-bold text-dark">
                                            {{ $tr->personnel->pdsMain->last_name }}, {{ $tr->personnel->pdsMain->first_name }}
                                        </span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td class="font-weight-bold">{{ $tr->title }}</td>
                                <td class="text-center">{{ $tr->type }}</td>
                                <td class="text-center">{{ $tr->sponsor }}</td>
                                <td class="text-center">{{ $tr->hours }}</td>
                                <td class="text-center">{{ $tr->start_date ? \Carbon\Carbon::parse($tr->start_date)->format('M d, Y') : '' }}</td>
                                <td class="text-center">{{ $tr->end_date ? \Carbon\Carbon::parse($tr->end_date)->format('M d, Y') : '' }}</td>
                                <td>
                                    <div class="d-flex align-items-center" style="gap: 8px; min-width: 360px;">
                                        <form action="{{ route('training.requests.approve', $tr->id) }}" method="POST" class="mb-0">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                        </form>
                                        <form action="{{ route('training.requests.reject', $tr->id) }}" method="POST" class="mb-0 d-flex align-items-center" style="gap: 8px;">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                            <input type="text" name="rejection_reason" class="form-control form-control-sm" placeholder="Reason (optional)" style="min-width: 180px;">
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                        <h4 class="text-muted">No pending training requests</h4>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
