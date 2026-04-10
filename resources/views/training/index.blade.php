@extends('layouts.app')

@section('content')

<div class="container-fluid mt-4" data-ajax-content>

@php
    $user = Auth::user();
    $isPersonnel = $user && $user->hasRole('personnel');
@endphp

{{-- ===================== STATS (HIDDEN FOR PERSONNEL) ===================== --}}
@unless($isPersonnel)
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
@endunless


{{-- ===================== MAIN CARD ===================== --}}
<div class="card shadow border-0">

    {{-- ===================== HEADER (VISIBLE TO ALL) ===================== --}}
    <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">

        <h3 class="mb-0">
            <i class="fas fa-chalkboard-teacher mr-2 text-primary"></i>
            Training & Seminars
        </h3>

        <div class="d-flex align-items-center">

            {{-- SEARCH (ALL USERS) --}}
            <form action="{{ route('training.index') }}" method="GET" class="mr-3 mb-0">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" style="min-width: 250px;"
                        placeholder="Search title, ID, or personnel..."
                        value="{{ request('search') }}">

                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>

                        @if(request('search'))
                        <a href="{{ route('training.index') }}"
                           class="btn btn-outline-danger"
                           title="Clear Search">
                            <i class="fas fa-times"></i>
                        </a>
                        @endif
                    </div>
                </div>
            </form>

            {{-- ADD BUTTON (ALL USERS) --}}
            <a href="{{ route('training.create') }}" class="btn btn-sm btn-success">
                <i class="fas fa-plus mr-1"></i> Add Training
            </a>

        </div>
    </div>

    {{-- ===================== TABLE ===================== --}}
    <div class="table-responsive">
        <table class="table align-items-center table-flush table-hover">

            <thead class="thead-light">
                <tr>
                    @if(!$isPersonnel)
                        <th>Personnel</th>
                    @endif
                    <th>Title</th>
                    <th class="text-center">Type</th>
                    <th class="text-center">Sponsor</th>
                    <th class="text-center">Total Hours</th>
                    <th class="text-center">Start Date</th>
                    <th class="text-center">End Date</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($trainings as $tr)

                    @if(!$isPersonnel || ($isPersonnel && $tr->personnel_id == $user->personnel_id))

                        <tr>

                            {{-- Personnel column only for admin --}}
                            @if(!$isPersonnel)
                                <td>
                                    @if($tr->personnel && $tr->personnel->pdsMain)
                                        <span class="font-weight-bold text-dark">
                                            {{ $tr->personnel->pdsMain->last_name }},
                                            {{ $tr->personnel->pdsMain->first_name }}
                                        </span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            @endif

                            <td>{{ Str::limit($tr->title, 50) }}</td>
                            <td class="text-center">{{ $tr->type ?? '' }}</td>
                            <td class="text-center">{{ $tr->sponsor }}</td>
                            <td class="text-center">{{ $tr->hours ?? '' }}</td>

                            <td class="text-center">
                                {{ $tr->start_date ? \Carbon\Carbon::parse($tr->start_date)->format('M d, Y') : '' }}
                            </td>

                            <td class="text-center">
                                {{ $tr->end_date ? \Carbon\Carbon::parse($tr->end_date)->format('M d, Y') : '' }}
                            </td>

                            <td class="text-right">
                                <div class="d-flex justify-content-end align-items-center">

                                    <a href="{{ route('training.edit', $tr->id) }}"
                                       class="btn btn-sm btn-info mr-2">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>

                                    <form action="{{ route('training.destroy', $tr->id) }}"
                                          method="POST"
                                          class="d-inline">
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

                    @endif

                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
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

    {{-- ===================== PAGINATION ===================== --}}
    @if($trainings->hasPages())
    <div class="card-footer py-4 d-flex justify-content-center">
        {{ $trainings->links('pagination::bootstrap-4') }}
    </div>
    @endif

</div>
</div>

@endsection