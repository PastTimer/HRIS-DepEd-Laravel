<nav class="sidenav navbar navbar-vertical fixed-left navbar-expand-xs navbar-light bg-white" id="sidenav-main">
    <div class="scrollbar-inner">
        <div class="sidenav-header align-items-center" style="height: auto; padding: 1.5rem 1.5rem 0.5rem;">
            <img src="{{ asset('assets/img/brand/blue.png') }}" style="width: 100%; max-width: 210px; display: block; margin: 0 auto;" alt="Logo">
        </div>

        <div class="navbar-inner">
            <div class="p-3 mt-2 mb-3 rounded" style="background-color: #f6f9fc;">
                <div class="media align-items-center">
                    <span class="avatar avatar-sm rounded-circle bg-primary text-white">
                        <i class="ni ni-circle-08"></i>
                    </span>
                    <div class="media-body ml-2 d-none d-lg-block">
                        @php
                            $currentUser = Auth::user();
                            $roleName = $currentUser?->getRoleNames()->first();
                            $accessLabel = $currentUser?->school?->name ?? $currentUser?->personnel?->emp_id;
                            $displayName = trim(($currentUser?->first_name ?? '') . ' ' . ($currentUser?->last_name ?? ''));

                            $isAdmin = $currentUser?->hasRole('admin');
                            $isSchool = $currentUser?->hasRole('school');
                            $isEO = $currentUser?->hasRole('encoding_officer');
                            $isPersonnel = $currentUser?->hasRole('personnel');

                            $schoolProfileUrl = ($isSchool || $isEO) && !empty($currentUser?->school_id)
                                ? route('schools.show', $currentUser->school_id)
                                : '/schools';

                            $personnelUrl = $isPersonnel
                                ? route('personnel.me')
                                : '/personnel';

                            $isRequestsOpen = request()->routeIs('specialorder.requests')
                                || request()->routeIs('training.requests')
                                || request()->routeIs('training.requests.*');
                        @endphp
                        <span class="mb-0 text-sm font-weight-bold" style="display: block; line-height: 1.2; color: #32325d;">
                            {{ Auth::check() ? ($displayName !== '' ? $displayName : Auth::user()->username) : 'Guest' }}
                        </span>
                        
                        <small class="text-muted" style="display: block; font-size: 11px; margin-top: 2px;">
                            {{ $roleName ? strtoupper($roleName) : '' }}
                            @if($accessLabel)
                                | {{ Str::limit($accessLabel, 15) }}
                            @endif
                        </small>
                    </div>
                </div>
            </div>

            <div class="collapse navbar-collapse" id="sidenav-collapse-main" style="flex-basis: auto; flex-grow: 0;">
                <ul class="navbar-nav">
                    @if(!$isPersonnel)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="/dashboard">
                            <i class="ni ni-tv-2 text-primary"></i>
                            <span class="nav-link-text">Dashboard</span>
                        </a>
                    </li>
                    @endif
                    @if($isAdmin || $isSchool)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('personnel*') || request()->is('employees*') || request()->is('my-profile') ? 'active' : '' }}" href="{{ $personnelUrl }}">
                            <i class="ni ni-single-02 text-yellow"></i>
                            <span class="nav-link-text">Personnel</span>
                        </a>
                    </li>
                    @endif
                    @if($isPersonnel)
                    <li class="nav-item">
                        <a class="nav-link {{ (request()->is('my-profile') || (request()->is('personnel/*') && Auth::user() && request()->route('personnel') == Auth::user()->personnel_id)) ? 'active' : '' }}" href="{{ $personnelUrl }}">
                            <i class="ni ni-single-02 text-yellow"></i>
                            <span class="nav-link-text">Personnel Profile</span>
                        </a>
                    </li>
                    @endif

                    @if($isAdmin)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('positions*') ? 'active' : '' }}" href="/positions">
                            <i class="ni ni-badge text-default"></i>
                            <span class="nav-link-text">Position</span>
                        </a>
                    </li>
                    @endif

                    @if($isAdmin || $isSchool || $isEO)
                        @if(!$isPersonnel)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('schools*') ? 'active' : '' }}" href="{{ $schoolProfileUrl }}">
                                <i class="ni ni-building text-info"></i>
                                <span class="nav-link-text">School Profile</span>
                            </a>
                        </li>
                        @endif
                    @endif

                    @if($isAdmin || $isSchool || $isEO)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('monitoring*') ? 'active' : '' }}" href="/monitoring">
                            <i class="ni ni-sound-wave text-warning"></i>
                            <span class="nav-link-text">Monitoring</span>
                        </a>
                    </li>
                    @endif


                    @if($isAdmin || $isSchool || $isEO || $isPersonnel)
                    <li class="nav-item">
                        @php
                            // Highlight SO/Training tabs only when not on requests pages
                            $isSOActive = request()->is('specialorder*') && !request()->routeIs('specialorder.requests');
                            $isTrainingActive = request()->is('training*') && !request()->routeIs('training.requests') && !request()->routeIs('training.requests.*');
                        @endphp
                        <a class="nav-link {{ $isSOActive ? 'active' : '' }}" href="/specialorder">
                            <i class="ni ni-paper-diploma text-danger"></i>
                            <span class="nav-link-text">Special Order</span>
                        </a>
                    </li>
                    @endif

                    @if($isAdmin || $isSchool || $isPersonnel)
                    <li class="nav-item">
                        <a class="nav-link {{ $isTrainingActive ? 'active' : '' }}" href="/training">
                            <i class="ni ni-hat-3 text-success"></i>
                            <span class="nav-link-text">Training</span>
                        </a>
                    </li>
                    @endif

                    <!-- Requests Menu -->
                     @if($isAdmin || $isSchool || $isPersonnel)
                    <li class="nav-item">
                        <a class="nav-link {{ $isRequestsOpen ? 'active' : '' }}" href="#navbar-requests" data-toggle="collapse" role="button" aria-expanded="{{ $isRequestsOpen ? 'true' : 'false' }}">
                            <i class="ni ni-archive-2 text-warning"></i>
                            <span class="nav-link-text">Requests</span>
                        </a>
                        <div class="collapse {{ $isRequestsOpen ? 'show' : '' }}" id="navbar-requests">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('specialorder.requests') ? 'active text-primary font-weight-bold' : '' }}" href="{{ route('specialorder.requests') }}">
                                        <span class="sidenav-normal"> Special Order Requests </span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('training.requests') || request()->routeIs('training.requests.*') ? 'active text-primary font-weight-bold' : '' }}" href="{{ route('training.requests') }}">
                                        <span class="sidenav-normal"> Training Requests </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif
                    @if($isAdmin || $isSchool)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('equipment*') ? 'active' : '' }}" href="/equipment">
                            <i class="ni ni-archive-2 text-primary"></i>
                            <span class="nav-link-text">Inventory</span>
                        </a>
                    </li>
                    @endif

                    @if($isAdmin)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('internet*') || request()->is('isp*') ? 'active' : '' }}" href="#navbar-internet" data-toggle="collapse" role="button" aria-expanded="{{ request()->is('internet*') || request()->is('isp*') ? 'true' : 'false' }}">
                            <i class="ni ni-world text-info"></i>
                            <span class="nav-link-text">Internet Connectivity</span>
                        </a>
                        <div class="collapse {{ request()->is('internet*') || request()->is('isp*') ? 'show' : '' }}" id="navbar-internet">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="/internet" class="nav-link {{ request()->is('internet*') ? 'active text-primary font-weight-bold' : '' }}">
                                        <span class="sidenav-normal"> Internet Profile </span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/isp" class="nav-link {{ request()->is('isp*') ? 'active text-primary font-weight-bold' : '' }}">
                                        <span class="sidenav-normal"> ISP Inventory </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if($isAdmin || $isSchool)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reports*') ? 'active' : '' }}" href="/reports">
                            <i class="ni ni-chart-pie-35 text-orange"></i>
                            <span class="nav-link-text">Reports</span>
                        </a>
                    </li>
                    @endif

                    @if($isAdmin || $isSchool)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('logs*') ? 'active' : '' }}" href="/logs">
                            <i class="ni ni-bullet-list-67 text-default"></i>
                            <span class="nav-link-text">Audit Trail</span>
                        </a>
                    </li>
                    @endif

                    @if($isAdmin || $isSchool)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('users*') ? 'active' : '' }}" href="/users">
                            <i class="ni ni-settings-gear-65 text-dark"></i>
                            <span class="nav-link-text">User Accounts</span>
                        </a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <form method="POST" action="/logout" style="display:inline;">
                            @csrf
                            <button type="submit" class="nav-link" style="background:none; border:none; width:100%; text-align:left; cursor:pointer;">
                                <i class="ni ni-user-run text-green"></i>
                                <span class="nav-link-text">Log Out</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>