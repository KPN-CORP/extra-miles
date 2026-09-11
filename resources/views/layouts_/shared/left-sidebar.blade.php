<!-- ========== Left Sidebar Start ========== -->
<div class="leftside-menu">
    {{-- @if(session('system') == 'kpnpm') --}}
    <!-- Brand Logo Light -->
    <a href="#" class="logo logo-light">
        <span class="logo-lg">
            <img src="{{ asset('storage/img/extra_mile.png') }}" alt="logo">
        </span>
        <span class="logo-sm">
            <img src="{{ asset('storage/img/logo-sm_ori.png') }}" alt="small logo">
        </span>
    </a>

    <!-- Brand Logo Dark -->
    <a href="#" class="logo logo-dark">
        <span class="logo-lg">
            <img src="{{ asset('storage/img/extra_mile.png') }}" alt="logo">
        </span>
        <span class="logo-sm">
            <img src="{{ asset('storage/img/logo-sm_ori.png') }}" alt="small logo">
        </span>
    </a>
    
    <!-- Sidebar Hover Menu Toggle Button -->
    <div class="button-sm-hover" data-bs-toggle="tooltip" data-bs-placement="right" title="{{ __('Show Full Sidebar') }}">
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
            {{-- @if(auth()->check())
                <p><strong>User Role(s):</strong> {{ auth()->user()->getRoleNames()->implode(', ') }}</p>

                <p><strong>Permissions from Role(s):</strong></p>
                <ul>
                    @php
                        $permissions = auth()->user()->getPermissionsViaRoles()->pluck('name')->unique();
                    @endphp

                    @forelse ($permissions as $permission)
                        <li>{{ $permission }}</li>
                    @empty
                        <li>No permissions assigned via roles.</li>
                    @endforelse
                </ul>
                <br>
                <pre>{{ auth()->user()->getAllPermissions()->pluck('name') }}</pre>
            @endif --}}
            <li class="side-nav-title">{{ __('Menu') }}</li>
            <li class="side-nav-item">
                <a href="{{ url('/admin/dashboard') }}" class="side-nav-link">
                    <i class="ri-dashboard-3-line"></i>
                    <span> {{ __('Dashboard') }} </span>
                </a>
            </li>
            @if(auth()->check())

                @can('viewmenunews')
                <li class="side-nav-item">
                    <a href="{{ url('/admin/news') }}" class="side-nav-link">
                        <i class="ri-file-list-2-line"></i>
                        <span> {{ __('News Update') }} </span>
                    </a>
                </li>
                @endcan

                @can('viewmenuevent')
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#sidebarEvent" aria-expanded="false" aria-controls="sidebarEvent" class="side-nav-link">
                        <i class="ri-settings-5-line"></i>
                        <span>{{ __('Event Management') }}</span>
                        <span class="menu-arrow"></span>  
                    </a>
                    <div class="collapse" id="sidebarEvent">
                        <ul class="side-nav-second-level">
                            <li>
                                <a href="{{ url('/admin/events') }}"> {{ __('Events') }} </a>
                            </li>
                            @can('viewmenuevo')
                            <li>
                                <a href="{{ url('/admin/evo') }}"> EVO </a>
                            </li>
                            @endcan
                        </ul>
                    </div>
                </li>
                @endcan

                @can('viewmenuwellness')
                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#sidebarWellness" aria-expanded="false" aria-controls="sidebarWellness" class="side-nav-link">
                        <i class="ri-heart-pulse-line"></i>
                        <span>{{ __('Wellness') }}</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarWellness">
                        <ul class="side-nav-second-level">
                            <li>
                                <a href="{{ url('/admin/wellness/activities') }}"> {{ __('Activities') }} </a>
                            </li>
                            <li>
                                <a href="{{ url('/admin/wellness/blacklist') }}"> {{ __('Blacklist') }} </a>
                            </li>
                            @can('viewmenuwellnesstype')
                            <li>
                                <a href="{{ url('/admin/wellness/types') }}"> {{ __('Activity Types') }} </a>
                            </li>
                            @endcan
                        </ul>
                    </div>
                </li>
                @endcan

                @can('viewmenusurvey')
                <li class="side-nav-item">
                    <a href="{{ url('/admin/survey') }}" class="side-nav-link">
                        <i class="ri-group-line"></i>
                        <span> {{ __('Survey/Voting') }} </span>
                    </a>
                </li>
                @endcan

                @can('viewmenusocial')
                <li class="side-nav-item">
                    <a href="{{ url('/admin/social') }}" class="side-nav-link">
                        <i class="ri-earth-line"></i>
                        <span> {{ __('Social Media') }} </span>
                    </a>
                </li>
                @endcan

                @can('viewmenulive')
                <li class="side-nav-item">
                    <a href="{{ url('/admin/live') }}" class="side-nav-link">
                        <i class="ri-live-line"></i>
                        <span> {{ __('LIVE') }} </span>
                    </a>
                </li>
                @endcan

                @can('viewmenuquotes')
                <li class="side-nav-item">
                    <a href="{{ url('/admin/quotes') }}" class="side-nav-link">
                        <i class="ri-double-quotes-l"></i>
                        <span> {{ __('Quotes/Affirmation') }} </span>
                    </a>
                </li>
                @endcan

                @can('viewmenuform')
                <li class="side-nav-item">
                    <a href="{{ url('/admin/formbuilder') }}" class="side-nav-link">
                        <i class="ri-ai-generate"></i>
                        <span> {{ __('Form Builder') }} </span>
                    </a>
                </li>
                @endcan

                @can('viewsettingem')
                    <li class="side-nav-title">{{ __('Admin') }}</li>
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarCharts" aria-expanded="false" aria-controls="sidebarCharts" class="side-nav-link">
                            <i class="ri-admin-line"></i>
                            <span> {{ __('Settings') }} </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarCharts">
                            <ul class="side-nav-second-level">
                                @can('viewroleem')
                                <li class="side-nav-item">
                                    <a href="{{ route('roles') }}">{{ __('Role') }}</a>
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
