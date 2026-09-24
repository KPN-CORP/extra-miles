<!-- ========== Topbar Start ========== -->
<div class="navbar-custom shadow-none p-0" style="z-index: 999;">
    <div class="topbar container-fluid">
        <div class="d-flex align-items-center gap-lg-2 gap-1">

            <!-- Topbar Brand Logo -->
            <div class="logo-topbar d-none">
                <!-- Logo light -->
                <a href="{{ Url('/') }}" class="logo-light">
                    <span class="logo-lg">
                        <img src="{{ asset('storage/img/logo.png') }}" alt="{{ __('Logo') }}">
                    </span>
                    <span class="logo-sm">
                        <img src="{{ asset('storage/img/logo-sm.png') }}" alt="{{ __('Small logo') }}">
                    </span>
                </a>

                <!-- Logo Dark -->
                <a href="{{ Url('/') }}" class="logo-dark">
                    <span class="logo-lg">
                        <img src="{{ asset('storage/img/logo-dark.png') }}" alt="{{ __('Dark logo') }}">
                    </span>
                    <span class="logo-sm">
                        <img src="{{ asset('storage/img/logo-sm.png') }}" alt="{{ __('Small logo') }}">
                    </span>
                </a>
            </div>

            <!-- Sidebar Menu Toggle Button -->
            <button class="button-toggle-menu">
                <i class="ri-menu-2-fill"></i>
            </button>

            <!-- Horizontal Menu Toggle Button -->
            <button class="navbar-toggle" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                <div class="lines">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </button>
            
        </div>

        <ul class="topbar-menu d-flex align-items-center gap-3">
            <li class="dropdown d-none">
                <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                    <i class="ri-search-line fs-22"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-animated dropdown-lg p-0">
                    <form class="p-3">
                        <input type="search" class="form-control" placeholder="{{ __('Search ...') }}" aria-label="{{ __('Search') }}">
                    </form>
                </div>
            </li>

            <li class="d-none">
                <div class="nav-link" id="light-dark-mode" data-bs-toggle="tooltip" data-bs-placement="left" title="{{ __('Theme Mode') }}">
                    <i class="ri-moon-line fs-22"></i>
                </div>
            </li>


            <li class="d-none">
                <a class="nav-link" href="" data-toggle="fullscreen">
                    <i class="ri-fullscreen-line fs-22"></i>
                </a>
            </li>
            @php
                // app()->getLocale() already reflects the session value applied
                // by the 'locale' middleware, and unlike env() it survives
                // `config:cache` on the cPanel deploy.
                $lang = app()->getLocale();
                $languages = ['en' => 'English', 'id' => 'Bahasa Indonesia'];
            @endphp
            <li class="dropdown">
                <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false" title="{{ __('Language') }}">
                    @include('layouts_.shared.flag', ['code' => $lang, 'uid' => 'current'])
                    <span class="align-middle d-none d-lg-inline-block ms-1">{{ $languages[$lang] ?? $languages['en'] }}</span>
                    <i class="ri-arrow-down-s-line d-none d-sm-inline-block align-middle"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated">
                    @foreach ($languages as $code => $label)
                        <a href="{{ route('language.switch', ['locale' => $code]) }}"
                           class="dropdown-item {{ $lang === $code ? 'active' : '' }}" onclick="showLoader()">
                            @include('layouts_.shared.flag', ['code' => $code, 'uid' => 'opt-'.$code])
                            <span class="align-middle ms-1">{{ $label }}</span>
                            @if ($lang === $code)
                                <i class="ri-check-line ms-1 align-middle"></i>
                            @endif
                        </a>
                    @endforeach
                </div>
            </li>

            <li class="dropdown">
                <a class="nav-link dropdown-toggle arrow-none nav-user px-2" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                    <span class="account-user-avatar">
                        <img src="{{ asset('storage/img/profiles/user.png') }}" alt="{{ __('User avatar') }}" width="32" class="rounded-circle">
                    </span>
                    <span class="d-flex flex-column gap-1">
                        <h5 class="my-0">
                            {{ auth()->user()->name }}
                        </h5>
                        <h6 class="my-0 fw-normal">{{ auth()->user()->employee_id }}</h6>
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated profile-dropdown mt-1">

                    <!-- item-->
                    <a href="{{ route('second', ['auth', 'lock-screen']) }}" class="dropdown-item d-none">
                        <i class="ri-key-2-fill fs-18 align-middle me-1"></i>
                        <span>{{ __('Change Password') }}</span>
                    </a>

                    <!-- item-->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a onclick="event.preventDefault(); this.closest('form').submit();" class="dropdown-item">
                            <i class="ri-logout-box-line fs-18 align-middle me-1"></i>
                            <span>{{ __('Logout') }}</span>
                        </a>
                    </form>
                </div>
            </li>
        </ul>
    </div>
    <div class="container-fluid" style="background-color: #f2f2f7;">
        <div class="page-title-box mx-2">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item">{{ __($parentLink) }}</li>
                    <li class="breadcrumb-item active">{{ __($link) }}</li>
                </ol>
            </div>
            <h4 class="page-title">@if (!empty($back))
                <a href="{{ route($back) }}" class="text-decoration-none me-2">
                    <i class="ri-arrow-left-line"></i>
                </a>
            @endif {{ __($link) }}</h4>
        </div>
    </div>
</div>
                
<!-- ========== Topbar End ========== -->
