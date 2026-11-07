<style>
    /* Ikon menu tidak boleh menyusut saat teks menu panjang (mis. "Laporan Pemakaian Bahan Baku"),
       supaya teks semua menu sejajar. Teks panjang turun ke baris berikutnya, tidak terpotong.
       !important dipakai karena CSS template sidebar memaksa teks menu satu baris. */
    .sidebar .nav-item > a > i,
    .sidebar-wrapper .nav-item > a > i {
        flex-shrink: 0 !important;
    }
    .sidebar .nav-item > a > p,
    .sidebar-wrapper .nav-item > a > p {
        flex: 1 1 0%;
        min-width: 0 !important;
        white-space: normal !important;
        overflow-wrap: anywhere;
        line-height: 1.3;
    }

    /* Badge angka di menu: jumlah item yang perlu tindakan (tidak dirender bila 0). */
    .sidebar .nav-item > a > .sidebar-badge,
    .sidebar-wrapper .nav-item > a > .sidebar-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-left: auto;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 700;
        line-height: 1;
        color: #fff;
    }
    .sidebar-badge--warning { background: #ea580c; }
    .sidebar-badge--danger  { background: #dc2626; }
    /* Saat sidebar diciutkan (hanya ikon), sembunyikan badge kecuali sedang di-hover. */
    .sidebar_minimize:not(.sidebar_minimize_hover) .sidebar-badge { display: none !important; }
</style>

@php
    // Angka badge disiapkan oleh App\View\Composers\SidebarBadgeComposer (tanpa query di Blade).
    $sidebarBadges = $sidebarBadges ?? [];
    $badgeText = fn ($n) => $n > 99 ? '99+' : $n;
@endphp

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

                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Transaksi</h4>
                </li>

                <li class="nav-item {{ request()->routeIs('permintaan-bahan-baku.*') ? 'active' : '' }}">
                    <a href="{{ route('permintaan-bahan-baku.index') }}">
                        <i class="fas fa-file-signature"></i>
                        <p>Permintaan Bahan Baku</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('pengalihan-bahan-baku.*') ? 'active' : '' }}">
                    <a href="{{ route('pengalihan-bahan-baku.index') }}">
                        <i class="fas fa-random"></i>
                        <p>Pengalihan Bahan Baku</p>
                        @if (($sidebarBadges['pengalihan'] ?? 0) > 0)
                            <span class="sidebar-badge sidebar-badge--warning"
                                title="{{ $sidebarBadges['pengalihan'] }} pengalihan menunggu penentuan produsen pengganti">{{ $badgeText($sidebarBadges['pengalihan']) }}</span>
                        @endif
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('penerimaan-bahan-baku.*') ? 'active' : '' }}">
                    <a href="{{ route('penerimaan-bahan-baku.index') }}">
                        <i class="fas fa-truck-loading"></i>
                        <p>Penerimaan Bahan Baku</p>
                        @if (($sidebarBadges['tagihan'] ?? 0) > 0)
                            <span class="sidebar-badge sidebar-badge--warning"
                                title="{{ $sidebarBadges['tagihan'] }} tagihan menunggu pembayaran">{{ $badgeText($sidebarBadges['tagihan']) }}</span>
                        @endif
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('pemakaian-bahan-baku.*') ? 'active' : '' }}">
                    <a href="{{ route('pemakaian-bahan-baku.index') }}">
                        <i class="fas fa-utensils"></i>
                        <p>Pemakaian Bahan Baku</p>
                    </a>
                </li>

                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Laporan</h4>
                </li>

                <li class="nav-item {{ request()->routeIs('laporan.penerimaan-bahan-baku.*') ? 'active' : '' }}">
                    <a href="{{ route('laporan.penerimaan-bahan-baku.index') }}">
                        <i class="fas fa-file-alt"></i>
                        <p>Laporan Penerimaan Barang</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('laporan.pemakaian-bahan-baku.*') ? 'active' : '' }}">
                    <a href="{{ route('laporan.pemakaian-bahan-baku.index') }}">
                        <i class="fas fa-clipboard-list"></i>
                        <p>Laporan Pemakaian Bahan Baku</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('laporan.stok.*') ? 'active' : '' }}">
                    <a href="{{ route('laporan.stok.index') }}">
                        <i class="fas fa-warehouse"></i>
                        <p>Laporan Stok Bahan Baku</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('laporan.kedaluwarsa.*') ? 'active' : '' }}">
                    <a href="{{ route('laporan.kedaluwarsa.index') }}">
                        <i class="fas fa-hourglass-half"></i>
                        <p>Bahan Baku Mendekati Kedaluwarsa</p>
                        @if (($sidebarBadges['kedaluwarsa'] ?? 0) > 0)
                            <span class="sidebar-badge sidebar-badge--danger"
                                title="{{ $sidebarBadges['kedaluwarsa'] }} batch sudah kedaluwarsa atau kedaluwarsa dalam {{ \App\Services\StokService::BATAS_MENDESAK }} hari">{{ $badgeText($sidebarBadges['kedaluwarsa']) }}</span>
                        @endif
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('laporan.kartu-stok.*') ? 'active' : '' }}">
                    <a href="{{ route('laporan.kartu-stok.index') }}">
                        <i class="fas fa-book"></i>
                        <p>Kartu Stok</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('laporan.receipt-pembayaran.*') ? 'active' : '' }}">
                    <a href="{{ route('laporan.receipt-pembayaran.index') }}">
                        <i class="fas fa-receipt"></i>
                        <p>Receipt Pembayaran</p>
                    </a>
                </li>

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
                    <h4 class="text-section">Transaksi</h4>
                </li>

                <li class="nav-item {{ request()->routeIs('penerimaan-bahan-baku.*') ? 'active' : '' }}">
                    <a href="{{ route('penerimaan-bahan-baku.index') }}">
                        <i class="fas fa-truck-loading"></i>
                        <p>Penerimaan Bahan Baku</p>
                        @if (($sidebarBadges['tagihan'] ?? 0) > 0)
                            <span class="sidebar-badge sidebar-badge--warning"
                                title="{{ $sidebarBadges['tagihan'] }} tagihan menunggu pembayaran">{{ $badgeText($sidebarBadges['tagihan']) }}</span>
                        @endif
                    </a>
                </li>

                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Laporan</h4>
                </li>

                <li class="nav-item {{ request()->routeIs('laporan.penerimaan-bahan-baku.*') ? 'active' : '' }}">
                    <a href="{{ route('laporan.penerimaan-bahan-baku.index') }}">
                        <i class="fas fa-file-alt"></i>
                        <p>Laporan Penerimaan Barang</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('laporan.pemakaian-bahan-baku.*') ? 'active' : '' }}">
                    <a href="{{ route('laporan.pemakaian-bahan-baku.index') }}">
                        <i class="fas fa-clipboard-list"></i>
                        <p>Laporan Pemakaian Bahan Baku</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('laporan.stok.*') ? 'active' : '' }}">
                    <a href="{{ route('laporan.stok.index') }}">
                        <i class="fas fa-warehouse"></i>
                        <p>Laporan Stok Bahan Baku</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('laporan.kedaluwarsa.*') ? 'active' : '' }}">
                    <a href="{{ route('laporan.kedaluwarsa.index') }}">
                        <i class="fas fa-hourglass-half"></i>
                        <p>Bahan Baku Mendekati Kedaluwarsa</p>
                        @if (($sidebarBadges['kedaluwarsa'] ?? 0) > 0)
                            <span class="sidebar-badge sidebar-badge--danger"
                                title="{{ $sidebarBadges['kedaluwarsa'] }} batch sudah kedaluwarsa atau kedaluwarsa dalam {{ \App\Services\StokService::BATAS_MENDESAK }} hari">{{ $badgeText($sidebarBadges['kedaluwarsa']) }}</span>
                        @endif
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('laporan.kartu-stok.*') ? 'active' : '' }}">
                    <a href="{{ route('laporan.kartu-stok.index') }}">
                        <i class="fas fa-book"></i>
                        <p>Kartu Stok</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('laporan.receipt-pembayaran.*') ? 'active' : '' }}">
                    <a href="{{ route('laporan.receipt-pembayaran.index') }}">
                        <i class="fas fa-receipt"></i>
                        <p>Receipt Pembayaran</p>
                    </a>
                </li>

            @endif

            {{-- ================= MENU KHUSUS PRODUSEN ================= --}}
            @if ($role === 'produsen')
                {{-- ---------- AKUN ---------- --}}
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

                {{-- ---------- DATA MASTER ---------- --}}
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Data Master</h4>
                </li>

                <li class="nav-item {{ request()->routeIs('produsen.bahan-baku.*') ? 'active' : '' }}">
                    <a href="{{ route('produsen.bahan-baku.index') }}">
                        <i class="fas fa-boxes"></i>
                        <p>Bahan Baku Saya</p>
                    </a>
                </li>

                {{-- ---------- PROSES ---------- --}}
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Proses</h4>
                </li>

                <li class="nav-item {{ request()->routeIs('produsen.permintaan.*') || request()->routeIs('produsen.permintaan-tambahan.*') ? 'active' : '' }}">
                    <a href="{{ route('produsen.permintaan.index') }}">
                        <i class="fas fa-inbox"></i>
                        <p>Permintaan Masuk</p>
                        @if (($sidebarBadges['permintaan_masuk'] ?? 0) > 0)
                            <span class="sidebar-badge sidebar-badge--warning"
                                title="{{ $sidebarBadges['permintaan_masuk'] }} permintaan belum direspon">{{ $badgeText($sidebarBadges['permintaan_masuk']) }}</span>
                        @endif
                    </a>
                </li>

                {{-- ---------- LAPORAN / RIWAYAT ---------- --}}
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Laporan</h4>
                </li>

                <li class="nav-item {{ request()->routeIs('produsen.pengiriman.*') ? 'active' : '' }}">
                    <a href="{{ route('produsen.pengiriman.index') }}">
                        <i class="fas fa-truck"></i>
                        <p>Riwayat Pengiriman</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('produsen.pembayaran.*') ? 'active' : '' }}">
                    <a href="{{ route('produsen.pembayaran.index') }}">
                        <i class="fas fa-wallet"></i>
                        <p>Pembayaran Saya</p>
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
