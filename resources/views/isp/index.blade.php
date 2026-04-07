@extends('layouts.app')
@section('title', 'ISP Inventory Management')

@section('content')
<style>
    /* Clickable row styles */
    .isp-row { cursor: pointer; transition: background-color 0.2s; }
    .isp-row:hover { background-color: #f6f9fc !important; box-shadow: inset 4px 0 0 #5e72e4; }
    
    /* Isolate the action buttons from the row click */
    .action-cell { position: relative; z-index: 10; }
    .action-btn-group { display: flex; justify-content: flex-end; gap: 8px; }
</style>

<div class="container-fluid mt-4" data-ajax-content>
    <div class="card shadow">
        <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
            <h3 class="mb-0"><i class="fas fa-network-wired mr-2 text-primary"></i> ISP Inventory</h3>
            
            <div class="d-flex align-items-center">
                <form action="{{ route('isp.index') }}" method="GET" class="mr-3 mb-0" data-ajax-search-form>
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="Search school, provider, acct..." value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                            @if(request('search'))
                                <a href="{{ route('isp.index') }}" class="btn btn-outline-danger" title="Clear Search" data-ajax-clear-search>
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

                <a href="{{ route('isp.create') }}" class="btn btn-sm btn-success">
                    <i class="fas fa-plus mr-1"></i> New Connection
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-items-center table-flush table-hover" id="ispTable">
                <thead class="thead-light">
                    <tr>
                        <th>School Name</th>
                        <th>Provider & Acct #</th>
                        <th>Speed</th>
                        <th>Monthly MRC</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($isps as $school)
                        @php $activeIsp = $school->isps->first(); @endphp
                        
                        <tr class="{{ $activeIsp ? 'isp-row' : '' }}" 
                            data-url="{{ $activeIsp ? route('isp.show', $activeIsp->id) : '' }}">
                            
                            <td>
                                <strong>{{ $school->name }}</strong><br>
                                <small class="text-muted">{{ $school->school_id }}</small>
                            </td>
                            <td>
                                <span class="badge badge-secondary mb-1">{{ $activeIsp->provider ?? 'None' }}</span><br>
                                <small class="text-primary font-weight-bold">{{ $activeIsp->account_no ?? 'Unassigned' }}</small>
                            </td>
                            <td><strong>{{ $activeIsp->plan_speed ?? '0' }}</strong> <small>Mbps</small></td>
                            <td>₱{{ number_format($activeIsp->monthly_mrc ?? 0, 2) }}</td>
                            <td>
                                <span class="badge badge-dot">
                                    <i class="{{ ($activeIsp && $activeIsp->status == 'Active') ? 'bg-success' : 'bg-warning' }}"></i>
                                    <small>{{ $activeIsp->status ?? 'Offline' }}</small>
                                </span>
                            </td>
                            <td class="text-right action-cell">
                                <div class="action-btn-group">
                                    @if($activeIsp)
                                        <a href="{{ route('isp.edit', $activeIsp->id) }}" class="btn btn-icon-only btn-sm btn-outline-primary" title="Edit Account">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form action="{{ route('isp.destroy', $activeIsp->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Permanently delete this ISP record?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-icon-only btn-sm btn-outline-danger" title="Delete Account">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
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

<script>
    document.addEventListener('click', function (e) {
        var row = e.target.closest('.isp-row');
        if (!row || e.target.closest('.action-cell')) {
            return;
        }

        var targetUrl = row.getAttribute('data-url');
        if (targetUrl) {
            window.location.href = targetUrl;
        }
    });
</script>
@endsection