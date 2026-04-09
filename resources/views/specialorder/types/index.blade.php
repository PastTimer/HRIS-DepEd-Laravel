@extends('layouts.app')
@section('title', 'Special Order Types')
@section('content')
<div class="container-fluid mt-4" data-ajax-content>
    <div class="row">
        <div class="col">
            <div class="card shadow">
                <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
                    <h3 class="mb-0"><i class="fas fa-tags mr-2 text-secondary"></i> Order Types</h3>

                    <div class="d-flex align-items-center">
                        <form action="{{ route('specialorder.types.index') }}" method="GET" class="mr-3 mb-0" data-ajax-search-form>
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" style="min-width: 240px;"
                                    placeholder="Search name or value..." value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    @if(request('search'))
                                        <a href="{{ route('specialorder.types.index') }}" class="btn btn-outline-danger" title="Clear Search" data-ajax-clear-search>
                                            <i class="fas fa-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>

                        <a href="{{ route('specialorder.index') }}" class="btn btn-sm btn-outline-secondary mr-2">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Orders
                        </a>

                        <a href="{{ route('specialorder.types.create') }}" class="btn btn-sm btn-success">
                            <i class="fas fa-plus mr-1"></i> New Type
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
                                <th>Name</th>
                                <th class="text-center">Value</th>
                                <th class="text-center">Usage Count</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($types as $type)
                            <tr>
                                <td class="font-weight-bold">{{ $type->name }}</td>
                                <td class="text-center">{{ number_format((float) $type->value, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge badge-info badge-pill">{{ $type->special_orders_count }}</span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('specialorder.types.edit', $type) }}" class="btn btn-sm btn-info" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('specialorder.types.destroy', $type) }}" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this order type?')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <h4 class="text-muted mb-0">No order types found.</h4>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer py-4">
                    {{ $types->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
