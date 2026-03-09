@extends('layouts.app')
@section('title', 'ISP Inventory Management')

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow">
        <div class="card-header border-0 bg-white">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="mb-0"><i class="fas fa-list-alt mr-2 text-primary"></i> ISP Inventory</h3>
                </div>
                <div class="col text-right">
                    <form action="/isp" method="GET" class="d-inline-block mr-2">
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control" placeholder="Search Account/School..." value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </form>
                    <a href="/isp/create" class="btn btn-sm btn-success"><i class="fas fa-plus"></i> New ISP</a>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-items-center table-flush table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>School / Station</th>
                        <th>Provider & Account</th>
                        <th>Type & Speed</th>
                        <th>Monthly Cost</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($isps as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->school->name ?? 'N/A' }}</strong><br>
                            <small class="text-muted">{{ $item->school->school_id ?? '' }}</small>
                        </td>
                        <td>
                            <span class="badge badge-primary">{{ $item->provider }}</span><br>
                            <small>{{ $item->account_no ?? 'No Account #' }}</small>
                        </td>
                        <td>
                            {{ $item->internet_type }}<br>
                            <small class="text-success font-weight-bold">
                                {{ $item->speedTests->first()->download_mbps ?? $item->plan_speed }} Mbps
                            </small>
                        </td>
                        <td>₱{{ number_format($item->monthly_mrc, 2) }}</td>
                        <td>
                            <span class="badge badge-dot">
                                <i class="{{ $item->status == 'Active' ? 'bg-success' : 'bg-warning' }}"></i>
                                {{ $item->status }}
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="dropdown">
                                <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                    <a class="dropdown-item" href="/internet/{{ $item->school_id }}">View School Dashboard</a>
                                    <a class="dropdown-item" href="/isp/{{ $item->id }}/edit">Edit Connection</a>
                                    <div class="dropdown-divider"></div>
                                    <form action="/isp/{{ $item->id }}" method="POST" onsubmit="return confirm('Delete this record?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">Delete Record</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer py-4">
            {{ $isps->links() }}
        </div>
    </div>
</div>
@endsection