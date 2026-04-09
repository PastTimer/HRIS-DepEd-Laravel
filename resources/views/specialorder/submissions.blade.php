@extends('layouts.app')
@section('title', 'Special Order Submissions')
@section('content')
<div class="container-fluid mt-4" data-ajax-content>
    <div class="row">
        <div class="col">
            <div class="card shadow">
                <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
                    <h3 class="mb-0"><i class="fas fa-inbox mr-2 text-info"></i> Submissions</h3>

                    <div class="d-flex align-items-center">
                        <form action="{{ route('specialorder.submissions') }}" method="GET" class="mr-3 mb-0" data-ajax-search-form>
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" style="min-width: 280px;"
                                    placeholder="Search SO #, title, type, creator..." value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    @if(request('search'))
                                        <a href="{{ route('specialorder.submissions') }}" class="btn btn-outline-danger" title="Clear Search" data-ajax-clear-search>
                                            <i class="fas fa-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>

                        <a href="{{ route('specialorder.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Orders
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

                <div class="table-responsive">
                    <table class="table align-items-center table-flush table-hover">
                        <thead class="thead-light">
                            <tr class="text-uppercase">
                                <th class="text-center">SO Number</th>
                                <th>Title</th>
                                <th class="text-center">Type</th>
                                <th class="text-center">Created By</th>
                                <th class="text-center">Created At</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($submissions as $so)
                            <tr>
                                <td class="text-center">{{ $so->so_number }}</td>
                                <td class="font-weight-bold">{{ $so->title }}</td>
                                <td class="text-center">{{ $so->type->name ?? 'N/A' }}</td>
                                <td class="text-center">{{ $so->creator->username ?? 'N/A' }}</td>
                                <td class="text-center">{{ optional($so->created_at)->format('Y-m-d H:i') }}</td>
                                <td class="text-center">
                                    @if($so->status === 'Approved')
                                        <span class="badge badge-success">Approved</span>
                                    @elseif($so->status === 'Rejected')
                                        <span class="badge badge-danger">Rejected</span>
                                    @else
                                        <span class="badge badge-warning">Pending</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('specialorder.show', $so) }}" class="btn btn-sm btn-primary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('specialorder.edit', $so) }}" class="btn btn-sm btn-info" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    @if(!auth()->user()->hasRole('personnel'))
                                    <form method="POST" action="{{ route('specialorder.status.update', $so) }}" style="display:inline;">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="Approved">
                                        <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('specialorder.status.update', $so) }}" style="display:inline;">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="Rejected">
                                        <button type="submit" class="btn btn-sm btn-warning" title="Reject">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </form>
                                    @endif

                                    @if(in_array($so->id, $deletableOrderIds ?? [], true))
                                    <form method="POST" action="{{ route('specialorder.destroy', $so) }}" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this submission?')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <h4 class="text-muted mb-0">No submissions found.</h4>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer py-4">
                    {{ $submissions->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
