<!-- Sidebar -->
<div class="sidebar-logo">
    <!-- Logo Header -->
    <div class="logo-header" data-background-color="dark">
        @php
            $dashboardRoute = match (auth()->user()->role) {
                'admin'    => route('admin.dashboard'),
                'pemilik'  => route('pemilik.dashboard'),
                'produsen' => route('produsen.dashboard'),
                default    => route('login'),
            };
        @endphp
        <a href="{{ $dashboardRoute }}" class="logo d-flex align-items-center">
            {{-- Gunakan accessor logo_url agar path storage/ selalu benar --}}
            <img src="{{ $settings->logo_url }}"
                alt="navbar brand" class="navbar-brand" height="50" />
            <span class="ms-2 text-white">{{ $settings->nama ?? 'Roti Baru Bakery' }}</span>
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
        @php
            $role = auth()->user()->role;
        @endphp
        <ul class="nav nav-secondary">

            <!-- Dashboard -->
            <li class="nav-item {{ request()->routeIs('admin.dashboard') || request()->routeIs('pemilik.dashboard') || request()->routeIs('produsen.dashboard') ? 'active' : '' }}">
                <a href="{{ $dashboardRoute }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <p>Dashboard</p>
                </a>
            </li>

            {{-- ================= MENU KHUSUS ADMIN ================= --}}
            @if ($role === 'admin')
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Data Master</h4>
                </li>

                <li class="nav-item {{ request()->routeIs('admin.produsen.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.produsen.index') }}">
                        <i class="fas fa-industry"></i>
                        <p>Data Produsen</p>
                    </a>
                </li>

                {{-- TODO: sesuaikan nama route saat sudah dibuat --}}
                {{--
                <li class="nav-item {{ request()->routeIs('kategori-barang.*') ? 'active' : '' }}">
                    <a href="{{ route('kategori-barang.index') }}">
                        <i class="fas fa-tags"></i>
                        <p>Kategori Barang</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('satuan.*') ? 'active' : '' }}">
                    <a href="{{ route('satuan.index') }}">
                        <i class="fas fa-balance-scale"></i>
                        <p>Satuan</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('bahan-baku.*') ? 'active' : '' }}">
                    <a href="{{ route('bahan-baku.index') }}">
                        <i class="fas fa-boxes"></i>
                        <p>Bahan Baku</p>
                    </a>
                </li>
                --}}

                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Transaksi</h4>
                </li>

                {{--
                <li class="nav-item {{ request()->routeIs('permintaan-bahan-baku.*') ? 'active' : '' }}">
                    <a href="{{ route('permintaan-bahan-baku.index') }}">
                        <i class="fas fa-file-signature"></i>
                        <p>Permintaan Bahan Baku</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('penerimaan-bahan-baku.*') ? 'active' : '' }}">
                    <a href="{{ route('penerimaan-bahan-baku.index') }}">
                        <i class="fas fa-truck-loading"></i>
                        <p>Penerimaan Bahan Baku</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('pemakaian-bahan-baku.*') ? 'active' : '' }}">
                    <a href="{{ route('pemakaian-bahan-baku.index') }}">
                        <i class="fas fa-utensils"></i>
                        <p>Pemakaian Bahan Baku</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('pengalihan-bahan-baku.*') ? 'active' : '' }}">
                    <a href="{{ route('pengalihan-bahan-baku.index') }}">
                        <i class="fas fa-random"></i>
                        <p>Pengalihan Bahan Baku</p>
                    </a>
                </li>
                --}}

                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Pengaturan</h4>
                </li>

                <li class="nav-item {{ request()->routeIs('setting.website.*') ? 'active' : '' }}">
                    <a href="{{ route('setting.website.edit') }}">
                        <i class="fas fa-cogs"></i>
                        <p>Pengaturan Website</p>
                    </a>
                </li>
            @endif

            {{-- ================= MENU KHUSUS PEMILIK ================= --}}
            @if ($role === 'pemilik')
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Manajemen Pengguna</h4>
                </li>

                <li class="nav-item {{ request()->routeIs('pemilik.admin.*') ? 'active' : '' }}">
                    <a href="{{ route('pemilik.admin.index') }}">
                        <i class="fas fa-user-shield"></i>
                        <p>Data Admin</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('pemilik.produsen.*') ? 'active' : '' }}">
                    <a href="{{ route('pemilik.produsen.index') }}">
                        <i class="fas fa-industry"></i>
                        <p>Data Produsen</p>
                    </a>
                </li>

                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Keuangan</h4>
                </li>

                {{--
                <li class="nav-item {{ request()->routeIs('pembayaran.*') ? 'active' : '' }}">
                    <a href="{{ route('pembayaran.index') }}">
                        <i class="fas fa-money-bill-wave"></i>
                        <p>Pembayaran</p>
                    </a>
                </li>
                --}}
            @endif

            {{-- ================= MENU KHUSUS PRODUSEN ================= --}}
            @if ($role === 'produsen')
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Akun</h4>
                </li>

                <li class="nav-item {{ request()->routeIs('produsen.profile') ? 'active' : '' }}">
                    <a href="{{ route('produsen.profile') }}">
                        <i class="fas fa-user-circle"></i>
                        <p>Profil Saya</p>
                    </a>
                </li>

                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Pasokan</h4>
                </li>

                {{--
                <li class="nav-item {{ request()->routeIs('permintaan-bahan-baku.*') ? 'active' : '' }}">
                    <a href="{{ route('permintaan-bahan-baku.index') }}">
                        <i class="fas fa-file-signature"></i>
                        <p>Permintaan Masuk</p>
                    </a>
                </li>
                --}}
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
