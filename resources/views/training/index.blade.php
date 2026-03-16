@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card card-stats shadow border-0">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-muted mb-0">Total Trainings</h5>
                            <span class="h2 font-weight-bold mb-0">{{ $stats['total'] }}</span>
                        </div>
                        <div class="col-auto">
                            <div class="icon icon-shape bg-primary text-white rounded-circle shadow">
                                <i class="fas fa-certificate"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>

    <div class="card shadow border-0">
        <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
            <h3 class="mb-0"><i class="fas fa-chalkboard-teacher mr-2 text-primary"></i> Training & Seminars</h3>
            
            <div class="d-flex align-items-center">
                <form action="{{ route('training.index') }}" method="GET" class="mr-3 mb-0">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" style="min-width: 250px;" 
                               placeholder="Search title, ID, or employee..." value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                            @if(request('search'))
                                <a href="{{ route('training.index') }}" class="btn btn-outline-danger" title="Clear Search">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

                <a href="{{ route('training.create') }}" class="btn btn-sm btn-success">
                    <i class="fas fa-plus mr-1"></i> Add Training
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-items-center table-flush table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>Ref ID & Title</th>
                        <th class="text-center">Duration</th>
                        <th class="text-center">Date Range</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- FIXED: Changed to @forelse to support the @empty block --}}
                    @forelse($trainings as $tr)
                    <tr>
                        <td>
                            <span class="text-xs text-muted font-weight-bold">{{ $tr->trefid }}</span><br>
                            <span class="font-weight-bold text-dark">{{ Str::limit($tr->title, 50) }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-secondary">{{ $tr->hours }} Hours</span>
                        </td>
                        <td class="text-center text-sm">
                            {{ \Carbon\Carbon::parse($tr->date_from)->format('M d') }} - 
                            {{ \Carbon\Carbon::parse($tr->date_to)->format('M d, Y') }}
                        </td>
                        <td class="text-center">
                            @php
                                $badgeClass = [
                                    'approved' => 'success',
                                    'pending' => 'warning',
                                    'denied' => 'danger'
                                ][$tr->status] ?? 'secondary';
                            @endphp
                            <span class="badge badge-dot mr-4">
                                <i class="bg-{{ $badgeClass }}"></i>
                                <span class="status">{{ ucfirst($tr->status) }}</span>
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="d-flex justify-content-end align-items-center">
                                @if($tr->file_path)
                                    <a href="{{ asset('storage/' . $tr->file_path) }}" 
                                    target="_blank" 
                                    class="btn btn-sm btn-outline-danger mr-2" 
                                    title="View Certificate">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                @endif

                                <a href="{{ route('training.edit', $tr->id) }}" 
                                class="btn btn-sm btn-info mr-2">
                                    <i class="fas fa-edit"></i> Edit
                                </a>

                                <form action="{{ route('training.destroy', $tr->id) }}" method="POST" class="d-inline">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn btn-sm btn-danger" 
                                            onclick="return confirm('Are you sure you want to delete this training record?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="empty-state">
                                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">No trainings found</h4>
                                <p class="text-sm">Try a different search term or add a new record.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($trainings->hasPages())
            <div class="card-footer py-4">
                {{ $trainings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection