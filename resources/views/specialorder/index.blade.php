@extends('layouts.app')
@section('title', 'Special Orders')
@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card card-stats h-100 shadow-sm">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-muted mb-0">TOTAL SPECIAL ORDERS</h5>
                            <span class="h2 font-weight-bold mb-0 text-dark">{{ number_format($totalSo) }}</span>
                        </div>
                        <div class="col-auto">
                            <div class="icon icon-shape bg-gradient-info text-white rounded-circle shadow">
                                <i class="fas fa-file-signature"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="card shadow">
                <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
                    <h3 class="mb-0"><i class="fas fa-file-signature mr-2 text-primary"></i> Special Orders</h3>
                    
                    <div class="d-flex align-items-center">
                        <form action="{{ route('specialorder.index') }}" method="GET" class="mr-3 mb-0">
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" style="min-width: 280px;" 
                                    placeholder="Search title, SO #, or personnel..." value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    @if(request('search'))
                                        <a href="{{ route('specialorder.index') }}" class="btn btn-outline-danger" title="Clear Search">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>

                        <a href="{{ route('specialorder.create') }}" class="btn btn-sm btn-success">
                            <i class="fas fa-plus mr-1"></i> New Order
                        </a>
                    </div>
                </div>
                
                @if(session('success'))
                    <div class="alert alert-success m-3 alert-dismissible fade show" role="alert">
                        <span class="alert-icon"><i class="ni ni-like-2"></i></span>
                        <span class="alert-text"><strong>Success!</strong> {{ session('success') }}</span>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table align-items-center table-flush table-hover">
                        <thead class="thead-light">
                            <tr class="text-uppercase">
                                <th style="min-width: 300px;">Title / Description</th>
                                <th class="text-center">SO Number</th>
                                <th class="text-center">Date (Series)</th>
                                <th class="text-center">Personnel Included</th>
                                <th class="text-center">Type</th>
                                <th class="text-center">Attachment</th>
                                @if(Auth::user()->role === 'admin')
                                    <th class="text-center">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($specialorder as $so)
                            <tr>
                                <td class="font-weight-bold text-dark" style="white-space: normal;">
                                    {{ $so->title }}
                                </td>
                                
                                <td class="text-center">
                                    {{ $so->so_no }}
                                </td>
                                
                                <td class="text-center">
                                    {{ $so->series_year }}
                                </td>

                                <td class="text-center">
                                    <span class="badge badge-secondary badge-pill">
                                        <i class="fas fa-users mr-1"></i> {{ $so->employees->count() }}
                                    </span>
                                </td>
                                
                                <td class="text-center">
                                    @if($so->type === 'VL')
                                        <span class="badge badge-pill badge-success">VL</span>
                                    @elseif($so->type === 'SL')
                                        <span class="badge badge-pill badge-warning">SL</span>
                                    @else
                                        <span class="badge badge-pill badge-primary">{{ $so->type }}</span>
                                    @endif
                                </td>
                                
                                <td class="text-center">
                                    @if($so->file_path)
                                        <a href="{{ asset('storage/' . $so->file_path) }}" target="_blank" class="btn btn-icon-only text-danger btn-sm" title="View Attachment">
                                            <i class="fas fa-file-pdf fa-lg"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                
                                @if(Auth::user()->role === 'admin')
                                <td class="text-center">
                                    <a href="/specialorder/{{ $so->id }}/edit" class="btn btn-sm btn-info" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <form method="POST" action="/specialorder/{{ $so->id }}" style="display:inline;">
                                        @csrf 
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to permanently DELETE this record?')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ Auth::user()->role === 'admin' ? '7' : '6' }}" class="text-center py-5">
                                    <i class="ni ni-paper-diploma fa-3x text-muted mb-3 d-block"></i>
                                    <h4 class="text-muted mb-0">No Special Orders found.</h4>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="card-footer py-4">
                    {{ $specialorder->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection