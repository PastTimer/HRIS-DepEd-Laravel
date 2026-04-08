@extends('layouts.app')
@section('title', 'User Management')
@section('content')
<div class="container-fluid mt-4" data-ajax-content>
    <div class="row">
        <div class="col">
            <div class="card shadow">
                <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center">
                    <h3 class="mb-0"><i class="fas fa-users mr-2 text-primary"></i> User Management</h3>
                    
                    <div class="d-flex align-items-center">
                        <form action="{{ route('users.index') }}" method="GET" class="mr-3 mb-0" data-ajax-search-form>
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" placeholder="Search name, username, role, school, personnel..." value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    @if(request('search'))
                                        <a href="{{ route('users.index') }}" class="btn btn-outline-danger" title="Clear Search" data-ajax-clear-search>
                                            <i class="fas fa-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>

                        <a href="{{ route('users.create') }}" class="btn btn-sm btn-success">
                            <i class="fas fa-user-plus mr-1"></i> Add New User
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
                                <th>Username</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Contact</th>
                                <th>Office</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td><strong>{{ $user->username }}</strong></td>
                                
                                <td>
                                    @php($roleName = $user->getRoleNames()->first())
                                    @php($profile = $user->personnel?->pdsMain)
                                    @php($personnelName = trim(($profile->first_name ?? '') . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? '')))
                                    @if($profile)
                                        {{ $personnelName !== '' ? $personnelName : strtoupper(str_replace('_', ' ', $roleName ?? 'N/A')) }}
                                    @elseif($user->school)
                                        {{ $user->school->name }}
                                    @else
                                        {{ strtoupper(str_replace('_', ' ', $roleName ?? 'N/A')) }}
                                    @endif
                                </td>
                                
                                <td>
                                    @if($user->email)
                                        <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                
                                <td>
                                    @if($user->school)
                                        {{ $user->school->contact_landline ?? 'N/A' }}
                                    @elseif($profile)
                                        {{ $profile->mobile ?? 'N/A' }}
                                    @else
                                        {{ $user->contact_no ?? 'N/A' }}
                                    @endif
                                </td>

                                <td>{{ $user->office }}</td>
                                
                                <td>
                                    @if($roleName === 'admin')
                                        <span class="badge badge-danger">ADMINISTRATOR</span>
                                    @elseif($roleName === 'school')
                                        <span class="badge badge-warning">SCHOOL USER</span>
                                    @elseif($roleName === 'encoding_officer')
                                        <span class="badge badge-primary">ENCODING OFFICER</span>
                                    @else
                                        <span class="badge badge-info">{{ strtoupper($roleName ?? 'N/A') }}</span>
                                    @endif
                                </td>
                                
                                <td>
                                    @if($user->status === 'active')
                                        <span class="badge badge-success">Active</span>
                                    @elseif($user->status === 'locked')
                                        <span class="badge badge-warning">Locked</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>

                                <td>
                                    @if($user->last_login)
                                        {{ \Carbon\Carbon::parse($user->last_login)->format('M d, Y h:i A') }}
                                    @else
                                        <span class="text-muted">Never</span>
                                    @endif
                                </td>
                                
                                <td class="text-center">
                                    <a href="/users/{{ $user->id }}/edit" class="btn btn-sm btn-primary" title="Edit User">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    @if(Auth::id() !== $user->id)
                                        <form method="POST" action="/users/{{ $user->id }}" style="display:inline;">
                                            @csrf 
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?')" title="Delete User">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <button class="btn btn-sm btn-secondary" disabled title="You cannot delete your own account">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <h4 class="text-muted mb-0">No users found.</h4>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="card-footer py-4 d-flex justify-content-center">
                    {{ $users->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection