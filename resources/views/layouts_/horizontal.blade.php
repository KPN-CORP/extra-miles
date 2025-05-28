<!DOCTYPE html>
<html lang="en" data-layout="topnav">

<head>
    @include('layouts_.shared/title-meta', ['title' => $page_title])
    @yield('css')
    @include('layouts_.shared/head-css', ['mode' => $mode ?? '', 'demo' => $demo ?? ''])

    @vite(['resources/js/head.js'])
</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">

        @include('layouts_.shared/topbar')

        @include('layouts_.shared/horizontal-nav')

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="content-page">
            <div class="content">
                <!-- Start Content-->
                @yield('content')
            </div>
            @include('layouts_.shared/footer')
        </div>

        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->

    </div>

    @include('layouts_.shared/right-sidebar')
    @include('layouts_.shared/footer-script')
    @vite(['resources/js/app.js', 'resources/js/layout.js'])
    @yield('script')

</body>

</html>
