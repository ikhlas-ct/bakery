<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Daftar Donatur – Dinas Sosial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"/>
    <style>
        body {
            background-color: #f0f4f8;
            font-family: 'Segoe UI', sans-serif;
        }
        .page-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 16px 60px;
        }
        .brand-logo {
            width: 42px; height: 42px;
            background: #1a2035;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,.08), 0 4px 16px rgba(0,0,0,.06);
        }
        .card-header {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            border-radius: 10px 10px 0 0 !important;
            padding: 13px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card-header .section-bar {
            width: 4px; height: 20px; border-radius: 4px;
        }
        .card-header h2 {
            font-size: .875rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }
        .form-label {
            font-size: .8rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 5px;
        }
        .form-control, .form-select {
            font-size: .875rem;
            background-color: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 12px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,.12);
            background: #fff;
        }
        .form-control.is-invalid {
            border-color: #dc2626;
        }
        /* Jenis Donatur Buttons */
        .jenis-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        .jenis-btn {
            display: flex; flex-direction: column;
            align-items: center; gap: 6px;
            padding: 12px 8px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            cursor: pointer;
            background: #f8fafc;
            font-size: .78rem; font-weight: 600;
            color: #64748b;
            transition: all .15s;
            width: 100%;
        }
        .jenis-btn:hover {
            border-color: #2563eb;
            color: #2563eb;
            background: #eff6ff;
        }
        .jenis-btn.selected {
            border-color: #2563eb;
            color: #2563eb;
            background: #eff6ff;
            box-shadow: 0 0 0 3px rgba(37,99,235,.1);
        }
        .jenis-btn i { font-size: 1.2rem; }
        .btn-daftar {
            background: #16a34a;
            border: none;
            border-radius: 8px;
            font-size: .9rem; font-weight: 600;
            padding: 10px;
            width: 100%;
            transition: background .15s;
        }
        .btn-daftar:hover { background: #15803d; }
        .btn-batal {
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: .875rem; font-weight: 500;
            color: #64748b;
            padding: 9px;
            width: 100%;
            background: transparent;
            transition: all .15s;
        }
        .btn-batal:hover {
            border-color: #dc2626;
            color: #dc2626;
            background: #fff1f1;
        }
        footer {
            margin-top: 36px;
            font-size: .75rem;
            color: #64748b;
            text-align: center;
        }
        @media (max-width: 576px) {
            .jenis-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
<div class="page-wrapper">

    {{-- Header --}}
    <div class="text-center mb-4">
        <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
            <div class="brand-logo">
                <i class="bi bi-house-heart-fill text-white fs-5"></i>
            </div>
            <h1 class="fs-4 fw-bold mb-0" style="color:#1a2035">Dinas Sosial</h1>
        </div>
        <p class="text-muted small mb-0">Daftar sebagai Donatur</p>
    </div>

    <div style="width:100%;max-width:820px">

        {{-- Error Global --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Periksa kembali isian form berikut:</strong>
                <ul class="mb-0 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('register.donatur.store') }}" method="POST">
            @csrf

            {{-- Data Donatur --}}
            <div class="card mb-4">
                <div class="card-header">
                    <div class="section-bar bg-primary"></div>
                    <h2><i class="bi bi-person-fill me-2 text-primary"></i>Data Donatur</h2>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">

                        {{-- Nama --}}
                        <div class="col-12">
                            <label class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="nama"
                                   class="form-control @error('nama') is-invalid @enderror"
                                   value="{{ old('nama') }}"
                                   placeholder="Nama lengkap / nama organisasi"/>
                            @error('nama')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Jenis Donatur --}}
                        <div class="col-12">
                            <label class="form-label">Jenis Donatur <span class="text-danger">*</span></label>
                            <input type="hidden" name="jenis_donatur" id="jenis_donatur" value="{{ old('jenis_donatur') }}"/>
                            <div class="jenis-grid">
                                <button type="button"
                                        class="jenis-btn {{ old('jenis_donatur') === 'perorangan' ? 'selected' : '' }}"
                                        onclick="pilihJenis('perorangan', this)">
                                    <i class="bi bi-person"></i> Perorangan
                                </button>
                                <button type="button"
                                        class="jenis-btn {{ old('jenis_donatur') === 'organisasi' ? 'selected' : '' }}"
                                        onclick="pilihJenis('organisasi', this)">
                                    <i class="bi bi-people"></i> Organisasi
                                </button>
                                <button type="button"
                                        class="jenis-btn {{ old('jenis_donatur') === 'perusahaan' ? 'selected' : '' }}"
                                        onclick="pilihJenis('perusahaan', this)">
                                    <i class="bi bi-building"></i> Perusahaan
                                </button>
                                <button type="button"
                                        class="jenis-btn {{ old('jenis_donatur') === 'pemerintah' ? 'selected' : '' }}"
                                        onclick="pilihJenis('pemerintah', this)">
                                    <i class="bi bi-bank"></i> Pemerintah
                                </button>
                            </div>
                            @error('jenis_donatur')
                                <div class="text-danger mt-1" style="font-size:.8rem">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

            {{-- Akun Login --}}
            <div class="card mb-4">
                <div class="card-header">
                    <div class="section-bar bg-success"></div>
                    <h2><i class="bi bi-key-fill me-2 text-success"></i>Akun Login</h2>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">

                        {{-- Username --}}
                        <div class="col-12">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="username"
                                   class="form-control @error('username') is-invalid @enderror"
                                   value="{{ old('username') }}"
                                   placeholder="Contoh: rizki_r"
                                   autocomplete="username"/>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="col-12">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email"
                                   name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}"
                                   placeholder="Contoh: rizki@email.com"
                                   autocomplete="email"/>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div class="col-md-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password"
                                       name="password"
                                       id="pw1"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Min. 8 karakter"
                                       autocomplete="new-password"/>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePw('pw1', 'icon1')">
                                    <i class="bi bi-eye" id="icon1"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Konfirmasi Password --}}
                        <div class="col-md-6">
                            <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password"
                                       name="password_confirmation"
                                       id="pw2"
                                       class="form-control"
                                       placeholder="Ulangi password"
                                       autocomplete="new-password"/>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePw('pw2', 'icon2')">
                                    <i class="bi bi-eye" id="icon2"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="d-flex flex-column gap-2">
                <button type="submit" class="btn btn-success btn-daftar">
                    <i class="bi bi-save me-2"></i>Daftar Sekarang
                </button>
                <button type="button" class="btn-batal" onclick="history.back()">Batal</button>
                <p class="text-center text-muted small mt-1 mb-0">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="text-primary fw-semibold text-decoration-none">Masuk di sini</a>
                </p>
            </div>

        </form>
    </div>

    <footer>2026, made with ❤️ by Dinas Sosial</footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Restore jenis donatur button state on validation error
    document.addEventListener('DOMContentLoaded', function () {
        const saved = document.getElementById('jenis_donatur').value;
        if (saved) {
            document.querySelectorAll('.jenis-btn').forEach(btn => {
                if (btn.getAttribute('onclick').includes(saved)) {
                    btn.classList.add('selected');
                }
            });
        }
    });

    function pilihJenis(value, el) {
        document.querySelectorAll('.jenis-btn').forEach(b => b.classList.remove('selected'));
        el.classList.add('selected');
        document.getElementById('jenis_donatur').value = value;
    }

    function togglePw(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon  = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    }
</script>
</body>
</html>
