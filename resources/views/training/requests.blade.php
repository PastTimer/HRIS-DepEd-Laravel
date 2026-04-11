@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow border-0">
        <div class="card-header bg-white">
            <h3 class="mb-0"><i class="fas fa-tasks mr-2 text-warning"></i> Training Requests (Pending)</h3>
        </div>
        <div class="table-responsive">
            <table class="table align-items-center table-flush table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>Personnel</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Sponsor</th>
                        <th>Total Hours</th>
                        <th>Start Date</th>
                        <th>End Date</th>
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
                        <td>{{ $tr->title }}</td>
                        <td>{{ $tr->type }}</td>
                        <td>{{ $tr->sponsor }}</td>
                        <td>{{ $tr->hours }}</td>
                        <td>{{ $tr->start_date ? \Carbon\Carbon::parse($tr->start_date)->format('M d, Y') : '' }}</td>
                        <td>{{ $tr->end_date ? \Carbon\Carbon::parse($tr->end_date)->format('M d, Y') : '' }}</td>
                        <td>
                            <form action="{{ route('training.requests.approve', $tr->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">Approve</button>
                            </form>
                            <form action="{{ route('training.requests.reject', $tr->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                <input type="text" name="rejection_reason" class="form-control form-control-sm mb-1" placeholder="Reason (optional)">
                                <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                            </form>
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
@endsection
