@extends('layouts.app')
@section('title', 'Special Orders')
@section('content')
<div class="container-fluid mt-4" data-ajax-content>
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
                        <form action="{{ route('specialorder.index') }}" method="GET" class="mr-3 mb-0" data-ajax-search-form>
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" style="min-width: 280px;" 
                                    placeholder="Search title, SO #, year, or type..." value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    @if(request('search'))
                                        <a href="{{ route('specialorder.index') }}" class="btn btn-outline-danger" title="Clear Search" data-ajax-clear-search>
                                            <i class="fas fa-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>

                        <a href="{{ route('specialorder.submissions') }}" class="btn btn-sm btn-outline-info mr-2">
                            <i class="fas fa-inbox mr-1"></i> Submissions
                        </a>

                        <a href="{{ route('specialorder.types.index') }}" class="btn btn-sm btn-outline-secondary mr-2">
                            <i class="fas fa-tags mr-1"></i> Order Types
                        </a>

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
                @if(session('error'))
                    <div class="alert alert-danger m-3 alert-dismissible fade show" role="alert">
                        <span class="alert-icon"><i class="ni ni-fat-remove"></i></span>
                        <span class="alert-text"><strong>Error:</strong> {{ session('error') }}</span>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table align-items-center table-flush table-hover">
                        <thead class="thead-light">
                            <tr class="text-uppercase">
                                <th style="min-width: 280px;">Title</th>
                                <th class="text-center">SO Number</th>
                                <th class="text-center">Series Year</th>
                                <th class="text-center">Type</th>
                                <th class="text-center">Personnel Included (Number)</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $so)
                            <tr>
                                <td class="font-weight-bold text-dark" style="white-space: normal;">
                                    {{ $so->title }}
                                </td>
                                
                                <td class="text-center">
                                    {{ $so->so_number }}
                                </td>
                                
                                <td class="text-center">
                                    {{ $so->series_year }}
                                </td>

                                <td class="text-center">
                                    <span class="badge badge-pill badge-primary">{{ $so->type->name ?? 'N/A' }}</span>
                                </td>

                                <td class="text-center">
                                    <span class="badge badge-secondary badge-pill">
                                        <i class="fas fa-users mr-1"></i> {{ $so->personnel_count }}
                                    </span>
                                </td>
                                
                                <td class="text-center">
                                    <a href="{{ route('specialorder.show', $so) }}" class="btn btn-sm btn-primary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <a href="{{ route('specialorder.edit', $so) }}" class="btn btn-sm btn-info" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    @if(in_array($so->id, $deletableOrderIds ?? [], true))
                                    <form method="POST" action="{{ route('specialorder.destroy', $so) }}" style="display:inline;">
                                        @csrf 
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to permanently DELETE this record?')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="ni ni-paper-diploma fa-3x text-muted mb-3 d-block"></i>
                                    <h4 class="text-muted mb-0">No Special Orders found.</h4>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="card-footer py-4">
                    {{ $orders->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection