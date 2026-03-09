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
                        <span class="mb-0 text-sm font-weight-bold" style="display: block; line-height: 1.2; color: #32325d;">
                            {{ Auth::check() ? Auth::user()->first_name . ' ' . Auth::user()->last_name : 'Guest' }}
                        </span>
                        
                        <small class="text-muted" style="display: block; font-size: 11px; margin-top: 2px;">
                            {{ Auth::check() ? strtoupper(Auth::user()->role) : '' }}
                            @if(Auth::check() && Auth::user()->access_level)
                                | {{ Str::limit(Auth::user()->access_level, 15) }}
                            @endif
                        </small>
                    </div>
                </div>
            </div>

            <div class="collapse navbar-collapse" id="sidenav-collapse-main" style="flex-basis: auto; flex-grow: 0;">
                
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="/dashboard">
                            <i class="ni ni-tv-2 text-primary"></i>
                            <span class="nav-link-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('employees*') ? 'active' : '' }}" href="/employees">
                            <i class="ni ni-single-02 text-yellow"></i>
                            <span class="nav-link-text">Personnel</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('schools*') ? 'active' : '' }}" href="/schools">
                            <i class="ni ni-building text-info"></i>
                            <span class="nav-link-text">School Profile</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('designations*') ? 'active' : '' }}" href="/designations">
                            <i class="ni ni-badge text-default"></i>
                            <span class="nav-link-text">Designation</span>
                        </a>
                    </li>
                </ul>

                <div id="hidden-menus" style="display: none;">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('monitoring*') ? 'active' : '' }}" href="/monitoring">
                                <i class="ni ni-sound-wave text-warning"></i>
                                <span class="nav-link-text">Monitoring</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('specialorder*') ? 'active' : '' }}" href="/specialorder">
                                <i class="ni ni-paper-diploma text-danger"></i>
                                <span class="nav-link-text">Special Order</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('training*') ? 'active' : '' }}" href="/training">
                                <i class="ni ni-hat-3 text-success"></i>
                                <span class="nav-link-text">Training</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('equipment*') ? 'active' : '' }}" href="/equipment">
                            <i class="ni ni-archive-2 text-primary"></i>
                            <span class="nav-link-text">Inventory</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('isp*') ? 'active' : '' }}" href="#navbar-internet" data-toggle="collapse" role="button" aria-expanded="{{ request()->is('isp*') ? 'true' : 'false' }}">
                            <i class="ni ni-world text-info"></i>
                            <span class="nav-link-text">Internet Connectivity</span>
                        </a>
                        <div class="collapse {{ request()->is('isp*') ? 'show' : '' }}" id="navbar-internet">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="/internet" class="nav-link {{ request()->is('isp/profiles') ? 'active text-primary font-weight-bold' : '' }}">
                                        <span class="sidenav-normal"> Internet Profile </span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/isp" class="nav-link {{ request()->is('isp/accounts') ? 'active text-primary font-weight-bold' : '' }}">
                                        <span class="sidenav-normal"> ISP Account </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    @if(Auth::check() && Auth::user()->role === 'admin')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('users*') ? 'active' : '' }}" href="/users">
                            <i class="ni ni-settings-gear-65 text-dark"></i>
                            <span class="nav-link-text">User Accounts</span>
                        </a>
                    </li>
                    @endif

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reports*') ? 'active' : '' }}" href="/reports">
                            <i class="ni ni-chart-pie-35 text-orange"></i>
                            <span class="nav-link-text">Report</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('logs*') ? 'active' : '' }}" href="/logs">
                            <i class="ni ni-bullet-list-67 text-default"></i>
                            <span class="nav-link-text">Audit Trail</span>
                        </a>
                    </li>
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
                
                <div class="mt-3 text-center">
                    <button id="toggle-menu-btn" class="btn btn-sm btn-outline-primary rounded-circle" type="button" title="Show/Hide Extra Menu">
                        <i class="ni ni-bold-down" id="toggle-icon"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var hiddenMenus = document.getElementById("hidden-menus");
    var toggleBtn = document.getElementById("toggle-menu-btn");
    var toggleIcon = document.getElementById("toggle-icon");
    
    var isVisible = localStorage.getItem("sidebar_extra_visible") === "true";
    
    if (isVisible) {
        hiddenMenus.style.display = "block";
        toggleIcon.classList.replace("ni-bold-down", "ni-bold-up");
    }
    
    toggleBtn.addEventListener("click", function() {
        if (hiddenMenus.style.display === "none" || hiddenMenus.style.display === "") {
            hiddenMenus.style.display = "block";
            localStorage.setItem("sidebar_extra_visible", "true");
            toggleIcon.classList.replace("ni-bold-down", "ni-bold-up");
        } else {
            hiddenMenus.style.display = "none";
            localStorage.setItem("sidebar_extra_visible", "false");
            toggleIcon.classList.replace("ni-bold-up", "ni-bold-down");
        }
    });

    var activeHiddenLink = hiddenMenus.querySelector(".nav-link.active");
    if(activeHiddenLink) {
         hiddenMenus.style.display = "block";
         localStorage.setItem("sidebar_extra_visible", "true"); 
         toggleIcon.classList.replace("ni-bold-down", "ni-bold-up");
    }
});
</script>