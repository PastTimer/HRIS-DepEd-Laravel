@extends('layouts.app')
@section('title', 'Inventory Management')
@section('content')
<div class="container-fluid mt-4" data-ajax-content>
    <div class="row">
        <div class="col">
            <div class="card shadow">
                <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
                    <h3 class="mb-0"><i class="fas fa-desktop mr-2 text-primary"></i> Equipment Inventory</h3>
                    
                    <div class="d-flex align-items-center">
                        <form action="{{ route('equipment.index') }}" method="GET" class="mr-3 mb-0" data-ajax-search-form>
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" style="min-width: 250px;" placeholder="Search item, serial no, school, or officer..." value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    @if(request('search'))
                                        <a href="{{ route('equipment.index') }}" class="btn btn-outline-danger" title="Clear Search" data-ajax-clear-search>
                                            <i class="fas fa-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>

                        <a href="{{ route('equipment.create') }}" class="btn btn-sm btn-success">
                            <i class="fas fa-plus mr-1"></i> Add New Item
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
                            <tr>
                                <th>Property No.</th>
                                <th>Item / Article</th>
                                <th>Assigned School</th>
                                <th>Accountable Officer</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($equipments as $equipment)
                            <tr>
                                <td>
                                    <strong>{{ $equipment->property_no ?? 'Unassigned' }}</strong><br>
                                    <small class="text-muted">{{ $equipment->qr_code }}</small>
                                </td>
                                
                                <td style="white-space: normal; min-width: 250px;">
                                    <span class="font-weight-bold">{{ $equipment->item }}</span>
                                    @if($equipment->brand_manufacturer || $equipment->model)
                                        <br><small class="text-muted">{{ $equipment->brand_manufacturer }} {{ $equipment->model }}</small>
                                    @endif
                                </td>
                                
                                <td style="white-space: normal; min-width: 200px;">
                                    {{ $equipment->school->name ?? 'Unassigned' }}
                                </td>
                                
                                <td>
                                    @if($equipment->accountableOfficer)
                                        {{ optional($equipment->accountableOfficer->pdsMain)->last_name ?? 'N/A' }}, {{ optional($equipment->accountableOfficer->pdsMain)->first_name ?? '' }}
                                    @else
                                        <span class="text-muted">None</span>
                                    @endif
                                </td>
                                
                                <td class="text-center">
                                    @if($equipment->equipment_condition === 'Serviceable' || $equipment->is_functional)
                                        <span class="badge badge-success">Serviceable</span>
                                    @elseif($equipment->equipment_condition === 'For Repair')
                                        <span class="badge badge-warning">For Repair</span>
                                    @else
                                        <span class="badge badge-danger">{{ $equipment->equipment_condition ?? 'Unserviceable' }}</span>
                                    @endif
                                </td>
                                
                                <td class="text-center">
                                    <a href="{{ route('equipment.show', $equipment->id) }}" class="btn btn-sm btn-primary" title="View">
                                        <i class="fas fa-eye"></i> View
                                    </a>

                                    <a href="{{ route('equipment.edit', $equipment->id) }}" class="btn btn-sm btn-info" title="Edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    
                                    <form method="POST" action="{{ route('equipment.destroy', $equipment->id) }}" style="display:inline;">
                                        @csrf 
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to remove this equipment from the inventory?')" title="Delete">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="ni ni-archive-2 fa-3x text-muted mb-3 d-block"></i>
                                    <h4 class="text-muted mb-0">No equipment found in inventory.</h4>
                                    <p class="text-sm">Click "Add Equipment" to register a new asset.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="card-footer py-4">
                    {{ $equipments->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection