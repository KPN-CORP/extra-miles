<!-- ========== Left Sidebar Start ========== -->
<div class="leftside-menu">
    {{-- @if(session('system') == 'kpnpm') --}}
    <!-- Brand Logo Light -->
    <a href="{{ Url('/') }}" class="logo logo-light">
        <span class="logo-lg">
            <img src="{{ asset('storage/img/extra_mile.png') }}" alt="logo">
        </span>
        <span class="logo-sm">
            <img src="{{ asset('storage/img/logo-sm_ori.png') }}" alt="small logo">
        </span>
    </a>

    <!-- Brand Logo Dark -->
    <a href="{{ Url('/') }}" class="logo logo-dark">
        <span class="logo-lg">
            <img src="{{ asset('storage/img/extra_mile.png') }}" alt="logo">
        </span>
        <span class="logo-sm">
            <img src="{{ asset('storage/img/logo-sm_ori.png') }}" alt="small logo">
        </span>
    </a>
    
    <!-- Sidebar Hover Menu Toggle Button -->
    <div class="button-sm-hover" data-bs-toggle="tooltip" data-bs-placement="right" title="Show Full Sidebar">
        <i class="ri-checkbox-blank-circle-line align-middle"></i>
    </div>

    <!-- Full Sidebar Menu Close Button -->
    {{-- <div class="button-close-fullsidebar">
        <i class="ri-close-fill align-middle"></i>
    </div> --}}

    <!-- Sidebar -left -->
    <div class="h-100" id="leftside-menu-container" data-simplebar>

        <!--- Sidemenu -->
        <ul class="side-nav">

            <li class="side-nav-title">Menu</li>
            <li class="side-nav-item">
                <a href="{{ url('/admin/dashboard') }}" class="side-nav-link">
                    <i class="ri-dashboard-3-line"></i>
                    <span> Dashboard </span>
                </a>
            </li>
            @if(auth()->check())

                @can('viewmenunews')
                <li class="side-nav-item">
                    <a href="{{ url('/admin/news') }}" class="side-nav-link">
                        <i class="ri-file-list-2-line"></i>
                        <span> News Update </span>
                    </a>
                </li>
                @endcan

                @can('viewmenuevent')
                <li class="side-nav-item">
                    <a href="{{ url('/admin/events') }}" class="side-nav-link">
                        <i class="ri-settings-5-line"></i>
                        <span> Event Management </span>
                    </a>
                </li>
                @endcan

                @can('viewmenusurvey')
                <li class="side-nav-item">
                    <a href="{{ url('/admin/survey') }}" class="side-nav-link">
                        <i class="ri-group-line"></i>
                        <span> Survey/Voting </span>
                    </a>
                </li>
                @endcan

                @can('viewmenusocial')
                <li class="side-nav-item">
                    <a href="{{ url('/admin/social') }}" class="side-nav-link">
                        <i class="ri-earth-line"></i>
                        <span> Social Media </span>
                    </a>
                </li>
                @endcan

                @can('viewmenulive')
                <li class="side-nav-item">
                    <a href="{{ url('/admin/live') }}" class="side-nav-link">
                        <i class="ri-live-line"></i>
                        <span> LIVE </span>
                    </a>
                </li>
                @endcan

                @can('viewmenuquotes')
                <li class="side-nav-item">
                    <a href="{{ url('/admin/quotes') }}" class="side-nav-link">
                        <i class="ri-double-quotes-l"></i>
                        <span> Quotes/Affirmation </span>
                    </a>
                </li>
                @endcan

                @can('viewmenuform')
                <li class="side-nav-item">
                    <a href="{{ url('/admin/formbuilder') }}" class="side-nav-link">
                        <i class="ri-ai-generate"></i>
                        <span> Form Builder </span>
                    </a>
                </li>
                @endcan

                @can('viewsetting')
                    <li class="side-nav-title">Admin</li>
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarCharts" aria-expanded="false" aria-controls="sidebarCharts" class="side-nav-link">
                            <i class="ri-admin-line"></i>
                            <span> Settings </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarCharts">
                            <ul class="side-nav-second-level">
                                @can('viewrole')
                                <li class="side-nav-item">
                                    <a href="{{ route('roles') }}">Role</a>
                                </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endcan
            @endif
        </ul>
        <!--- End Sidemenu -->

        <div class="clearfix"></div>
    </div>
</div>
<!-- ========== Left Sidebar End ========== -->
