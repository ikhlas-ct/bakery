<div class="main-header-logo">
    <div class="logo-header" data-background-color="dark">
        @php
            $role = auth()->user()->role;

            // Route dashboard sesuai role
            $dashboardRoute = match ($role) {
                'admin_dinsos' => route('dinsos.dashboard'),
                'admin_panti'  => route('admin_panti.dashboard'),
                default        => route('donatur.dashboard'),
            };

            // Route profil sesuai role
            $profilRoute = match ($role) {
                'admin_dinsos' => route('pegawai.profil'),
                'admin_panti'  => route('admin_panti.profil'),
                default        => route('donatur.profil'),
            };

            // Data profil (nama, email, foto) sesuai role
            $profilData = match ($role) {
                'admin_dinsos' => auth()->user()->pegawai,
                'admin_panti'  => auth()->user()->pengurus,
                default        => auth()->user()->donatur,
            };

            $profilNama  = $profilData?->nama ?? auth()->user()->username;
            $profilEmail = $profilData?->email ?? auth()->user()->email ?? 'Tidak ada email';

            $profilFoto = !empty($profilData?->foto_profil) && file_exists(public_path($profilData->foto_profil))
                ? asset($profilData->foto_profil)
                : asset('default-image/default-user.png');
        @endphp

        <a href="{{ $dashboardRoute }}" class="logo">
            {{-- Gunakan accessor logo_url agar path storage/ selalu benar --}}
            <img src="{{ $settings->logo_url }}" alt="Logo" height="20">
        </a>
        <div class="nav-toggle">
            <button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-right"></i></button>
            <button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
        </div>
        <button class="topbar-toggler more"><i class="gg-more-vertical-alt"></i></button>
    </div>
</div>

<nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
    <div class="container-fluid">
        <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
            <li class="nav-item topbar-user dropdown hidden-caret">
                <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                    <div class="avatar-sm">
                        <img src="{{ $profilFoto }}" alt="Profile" class="avatar-img rounded-circle" />
                    </div>
                    <span class="profile-username">
                        <span class="op-7">Hi,</span>
                        <span class="fw-bold">{{ $profilNama }}</span>
                    </span>
                </a>

                <ul class="dropdown-menu dropdown-user animated fadeIn">
                    <div class="dropdown-user-scroll scrollbar-outer">
                        <li>
                            <div class="user-box">
                                <div class="avatar-lg">
                                    <img src="{{ $profilFoto }}" alt="Profile" class="avatar-img rounded-circle" />
                                </div>
                                <div class="u-text">
                                    <h4>{{ $profilNama }}</h4>
                                    <p class="text-muted">{{ $profilEmail }}</p>
                                    <a href="{{ $profilRoute }}" class="btn btn-xs btn-secondary btn-sm">
                                        View Profile
                                    </a>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ $dashboardRoute }}">
                                Dashboard
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Logout
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </li>
                    </div>
                </ul>
            </li>
        </ul>
    </div>
</nav>
