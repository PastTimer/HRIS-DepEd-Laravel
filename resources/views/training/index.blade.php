@extends('layouts.app')
@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card card-stats shadow">
                <div class="card-body">
                    <h5 class="card-title text-uppercase text-muted mb-0">Total Trainings</h5>
                    <span class="h2 font-weight-bold mb-0">{{ $stats['total'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header border-0 d-flex justify-content-between align-items-center">
            <h2 class="mb-0"><i class="ni ni-hat-3 text-primary mr-2"></i> Training Management</h2>
            <a href="/training/create" class="btn btn-primary btn-sm">Add Training</a>
        </div>
        <div class="table-responsive">
            <table class="table align-items-center table-flush">
                <thead class="thead-light">
                    <tr>
                        <th>Title</th>
                        <th class="text-center">Hours</th>
                        <th class="text-center">Duration</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($trainings as $tr)
                    <tr>
                        <td class="font-weight-bold text-dark">{{ $tr->title }}</td>
                        <td class="text-center">{{ $tr->hours }} hrs</td>
                        <td class="text-center text-xs">{{ $tr->date_from }} to {{ $tr->date_to }}</td>
                        <td class="text-center">
                            <span class="badge badge-pill badge-{{ $tr->status == 'approved' ? 'success' : ($tr->status == 'pending' ? 'warning' : 'danger') }}">
                                {{ ucfirst($tr->status) }}
                            </span>
                        </td>
                        <td class="text-right">
                            <a href="/training/{{ $tr->id }}/edit" class="btn btn-sm btn-info">Edit</a>
                            <form action="/training/{{ $tr->id }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection