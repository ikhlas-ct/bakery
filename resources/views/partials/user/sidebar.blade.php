<!-- Sidebar -->
<div class="sidebar-logo">
    <!-- Logo Header -->
    <div class="logo-header" data-background-color="dark">
        <a href="{{ route('dinsos.dashboard') }}" class="logo d-flex align-items-center">
            {{-- Gunakan accessor logo_url agar path storage/ selalu benar --}}
            <img src="{{ $settings->logo_url }}"
                alt="navbar brand" class="navbar-brand" height="50" />
            <span class="ms-2 text-white">{{ $settings->nama ?? 'Nama Website' }}</span>
        </a>

        <div class="nav-toggle">
            <button class="btn btn-toggle toggle-sidebar">
                <i class="gg-menu-right"></i>
            </button>
            <button class="btn btn-toggle sidenav-toggler">
                <i class="gg-menu-left"></i>
            </button>
        </div>
        <button class="topbar-toggler more">
            <i class="gg-more-vertical-alt"></i>
        </button>
    </div>
    <!-- End Logo Header -->
</div>

<div class="sidebar-wrapper scrollbar scrollbar-inner">
    <div class="sidebar-content">
        <ul class="nav nav-secondary">

            <!-- Dashboard -->
            <li class="nav-item">
                @php
                    $dashboardRoute = match (auth()->user()->role) {
                        'admin_dinsos' => route('dinsos.dashboard'),
                        'admin_panti'  => route('admin_panti.dashboard'),
                        default        => route('donatur.dashboard'),
                    };
                @endphp
                <a href="{{ $dashboardRoute }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <p>Dashboard</p>
                </a>
            </li>

            <!-- Profile -->
            <li class="nav-item">
                @php
                    $role = auth()->user()->role;
                    $profilRoute = match ($role) {
                        'admin_dinsos' => route('pegawai.profil'),
                        'admin_panti'  => route('admin_panti.profil'),
                        default        => route('donatur.profil'),
                    };
                    $profilNama =
                        match ($role) {
                            'admin_dinsos' => auth()->user()->pegawai?->nama,
                            'admin_panti'  => auth()->user()->pengurus?->nama,
                            default        => auth()->user()->donatur?->nama,
                        } ?? auth()->user()->username;
                @endphp
                <a href="{{ $profilRoute }}">
                    <i class="fas fa-user-circle"></i>
                    <p>{{ $profilNama }}</p>
                </a>
            </li>

            @if (auth()->user()->role === 'admin_dinsos')
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Pengaturan</h4>
                </li>

                <!-- Website Setting -->
                <li class="nav-item {{ request()->routeIs('setting.website.*') ? 'active' : '' }}">
                    <a href="{{ route('setting.website.edit') }}">
                        <i class="fas fa-cogs"></i>
                        <p>Website Setting</p>
                    </a>
                </li>
            @endif

            @if (auth()->user()->role === 'admin_dinsos' || auth()->user()->role === 'admin_panti')
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Data Master</h4>
                </li>
            @endif

            @if (auth()->user()->role === 'admin_dinsos')
                <li class="nav-item {{ request()->routeIs('panti-asuhan.*') ? 'active' : '' }}">
                    <a href="{{ route('panti-asuhan.index') }}">
                        <i class="fas fa-house-user"></i>
                        <p>Panti Asuhan</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('pegawai.*') ? 'active' : '' }}">
                    <a href="{{ route('pegawai.index') }}">
                        <i class="fas fa-users"></i>
                        <p>Pegawai</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('pengurus.*') ? 'active' : '' }}">
                    <a href="{{ route('pengurus.index') }}">
                        <i class="fas fa-user-tie"></i>
                        <p>Pengurus</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('donatur.*') ? 'active' : '' }}">
                    <a href="{{ route('donatur.index') }}">
                        <i class="fas fa-hand-holding-heart"></i>
                        <p>Donatur</p>
                    </a>
                </li>
            @endif

            @if (auth()->user()->role === 'admin_dinsos' || auth()->user()->role === 'admin_panti')
                <li class="nav-item {{ request()->routeIs('anak-asuh.*') ? 'active' : '' }}">
                    <a href="{{ route('anak-asuh.index') }}">
                        <i class="fas fa-child"></i>
                        <p>Anak Asuh</p>
                    </a>
                </li>
            @endif

            <li class="nav-section">
                <span class="sidebar-mini-icon">
                    <i class="fa fa-ellipsis-h"></i>
                </span>
                <h4 class="text-section">Proses &amp; Kegiatan</h4>
            </li>

            @if (auth()->user()->role === 'admin_panti' || auth()->user()->role === 'admin_dinsos')
                <li class="nav-item {{ request()->routeIs('konten.*') && request()->route('jenis') === 'kegiatan' ? 'active' : '' }}">
                    <a href="{{ route('konten.index', 'kegiatan') }}">
                        <i class="fas fa-calendar-alt"></i>
                        <p>Kegiatan</p>
                    </a>
                </li>
            @endif

            @if (auth()->user()->role === 'donatur')
                <li class="nav-item {{ request()->routeIs('panti-asuhan.*') ? 'active' : '' }}">
                    <a href="{{ route('panti-asuhan.index') }}">
                        <i class="fas fa-house-user"></i>
                        <p>Panti Asuhan</p>
                    </a>
                </li>
            @endif

            @if (auth()->user()->role === 'admin_dinsos')
                <li class="nav-item {{ request()->routeIs('konten.*') && request()->route('jenis') === 'berita' ? 'active' : '' }}">
                    <a href="{{ route('konten.index', 'berita') }}">
                        <i class="fas fa-newspaper"></i>
                        <p>Berita</p>
                    </a>
                </li>
            @endif

            <li class="nav-item {{ request()->routeIs('donasi.*') ? 'active' : '' }}">
                <a href="{{ route('donasi.index') }}">
                    <i class="fas fa-donate"></i>
                    <p>Donasi</p>
                </a>
            </li>

            @if (auth()->user()->role === 'admin_panti' || auth()->user()->role === 'admin_dinsos')
                <li class="nav-item {{ request()->routeIs('keuangan.*') ? 'active' : '' }}">
                    <a href="{{ route('keuangan.index') }}">
                        <i class="fas fa-money-bill-wave"></i>
                        <p>Keuangan</p>
                    </a>
                </li>
            @endif

            {{-- ── Laporan (hanya admin_dinsos) ── --}}
            @if (auth()->user()->role === 'admin_dinsos')
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Laporan</h4>
                </li>

                <li class="nav-item {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                    <a href="{{ route('laporan.index') }}">
                        <i class="fas fa-chart-bar"></i>
                        <p>Cetak Laporan</p>
                    </a>
                </li>
            @endif

            <!-- Logout -->
            <li class="nav-item mt-4">
                <a href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i>
                    <p>Logout</p>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </li>

        </ul>
    </div>
</div>
