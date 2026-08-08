<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Explore ethical and responsible Mentawai cultural tourism. Discover Mentawai tribe traditions, customs, and authentic experiences on Mentawai Island, Indonesia. Sustainable tourism for ethnic culture enthusiasts.">
    <meta name="keywords"
        content="Mentawai tourism, Mentawai culture, Mentawai tribe, Mentawai traditions, Mentawai Island, Indonesia tourism, Mentawai ethnic, Mentawai customs, ethical cultural tourism, responsible tourism">
    <meta name="author" content="Mentawai Tribe">
    <meta name="robots" content="index, follow">

    <!-- Open Graph -->
    <meta property="og:title" content="@yield('title', 'Mentawai Tribe - Ethical and Responsible Mentawai Cultural Tourism')">
    <meta property="og:description"
        content="Explore ethical and responsible Mentawai cultural tourism. Discover Mentawai tribe traditions, customs, and authentic experiences on Mentawai Island, Indonesia.">
    <meta property="og:image" content="{{ asset($settings->logo ?? 'default-image/default-logo.png') }}">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta property="og:type" content="website">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Mentawai Tribe - Ethical and Responsible Mentawai Cultural Tourism')">
    <meta name="twitter:description"
        content="Explore ethical and responsible Mentawai cultural tourism. Discover Mentawai tribe traditions, customs, and authentic experiences on Mentawai Island, Indonesia.">
    <meta name="twitter:image" content="{{ asset($settings->logo ?? 'default-image/default-logo.png') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="canonical" href="{{ request()->url() }}">
    <link rel="icon" href="{{ asset($settings->logo ?? 'default-image/default-logo.png') }}" type="image/x-icon">

    <title>
        @hasSection('title')
            @yield('title') - Mentawai Tribe: Ethical Cultural Tourism on Mentawai Island
        @else
            Mentawai Tribe: Ethical and Responsible Cultural Tourism on Mentawai Island, Indonesia
        @endif
    </title>

    <!-- Fonts and Icons -->
    <script src="{{ asset('user/js/plugin/webfont/webfont.min.js') }}"></script>
    <link href="{{ asset('home/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">

    <script>
        WebFont.load({
            google: {
                families: ["Public Sans:300,400,500,600,700"]
            },
            custom: {
                families: [
                    "Font Awesome 6 Free",
                    "simple-line-icons",
                ],
                urls: ["{{ asset('user/css/all.min.css') }}"],
            },
            active: function() {
                sessionStorage.fonts = true;
            },
        });
    </script>

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('user/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('user/css/kaiadmin.min.css') }}">
    <link rel="stylesheet" href="{{ asset('user/css/plugins.min.css') }}">
    <link rel="stylesheet" href="{{ asset('user/css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('user/css/override.css') }}">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">

    @yield('styles')
    <style>
        
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="sidebar" data-background-color="dark">
            @include('partials.user.sidebar')
        </div>

        <div class="main-panel">
            <div class="main-header">
                @include('partials.user.header')
            </div>

            @yield('content')

            <footer class="footer">
                @include('partials.user.footer')
            </footer>
        </div>
    </div>

    <!-- Core JS -->
    <script src="{{ asset('user/js/core/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('user/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('user/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('user/js/plugin/summernote/summernote-lite.min.js') }}"></script>

    <!-- Plugins -->
    <script src="{{ asset('user/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}"></script>
    <script src="{{ asset('user/js/plugin/chart.js/chart.min.js') }}"></script>
    <script src="{{ asset('user/js/plugin/jquery.sparkline/jquery.sparkline.min.js') }}"></script>
    <script src="{{ asset('user/js/plugin/chart-circle/circles.min.js') }}"></script>
    <script src="{{ asset('user/js/plugin/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('user/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('user/js/plugin/jsvectormap/jsvectormap.min.js') }}"></script>
    <script src="{{ asset('user/js/plugin/jsvectormap/world.js') }}"></script>
    <script src="{{ asset('user/js/plugin/sweetalert/sweetalert.min.js') }}"></script>
    <script src="{{ asset('user/js/kaiadmin.min.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    @yield('scripts')
</body>
</html>
