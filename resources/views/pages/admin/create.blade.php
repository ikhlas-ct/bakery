@extends('layouts.user.user')

@section('title', 'Tambah Admin')

@section('styles')
<style>
    .section-divider {
        background: #f8f9fa;
        border-left: 4px solid #1a73e8;
        padding: 8px 14px;
        border-radius: 0 6px 6px 0;
        font-weight: 600;
        font-size: .9rem;
        color: #1a73e8;
        margin-bottom: 1rem;
    }

    .foto-preview-wrap {
        width: 130px; height: 130px;
        border: 2px dashed #ced4da;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; overflow: hidden;
        transition: border-color .2s;
    }
    .foto-preview-wrap:hover { border-color: #1a73e8; }
    .foto-preview-wrap img { width: 100%; height: 100%; object-fit: cover; }
    .required-mark { color: #dc3545; }
    label { font-size: .875rem; font-weight: 500; }

    .form-control, .form-select {
        border-radius: 10px; border: 1.5px solid #e2e8f0;
        font-size: .85rem; padding: 8px 12px; color: #334155;
        background-color: #f8fafc; transition: border-color .2s, box-shadow .2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: #1a73e8; background: #fff;
        box-shadow: 0 0 0 3px rgba(26,115,232,.12);
    }
    .form-control::placeholder { color: #94a3b8; }

    /* ── Page Header Card ── */
    .ph-card {
        background: #fff; border: 1px solid #e9ecef; border-radius: 14px;
        padding: 16px 20px; display: flex; align-items: center;
        justify-content: space-between; gap: 16px; flex-wrap: wrap;
        margin-bottom: 1.25rem; position: relative; overflow: hidden;
        box-shadow: 0 1px 6px rgba(0,0,0,.05);
    }
    .ph-card::before {
        content: ''; position: absolute;
        left: 0; top: 0; bottom: 0; width: 4px;
        border-radius: 14px 0 0 14px; background: #1a73e8;
    }
    .ph-left { display: flex; align-items: center; gap: 12px; }
    .ph-icon {
        width: 42px; height: 42px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; flex-shrink: 0;
        background: #dbeafe; color: #1a73e8;
    }
    .ph-title { font-size: 1.15rem; font-weight: 800; color: #1e293b; margin: 0; }
    .ph-breadcrumb { list-style: none; display: flex; gap: 6px; padding: 0; margin: 2px 0 0; }
    .ph-breadcrumb li:not(:last-child)::after { content: '/'; margin-left: 6px; color: #cbd5e1; }
    .ph-breadcrumb a { color: #1a73e8; text-decoration: none; font-size: .78rem; }
    .ph-breadcrumb a:hover { text-decoration: underline; }
    .ph-breadcrumb .bc-active { font-size: .75rem; color: #94a3b8; }

    /* ── Akun status box ── */
    .status-box {
        border: 1.5px dashed #e2e8f0; border-radius: 12px;
        padding: 16px; background: #fafbfc;
    }
</style>
@endsection

@section('content')
<div class="container">

    {{-- ── Page Header ── --}}
    <div class="ph-card">
        <div class="ph-left">
            <div class="ph-icon"><i class="fas fa-user-plus"></i></div>
            <div>
                <h5 class="ph-title">Tambah Admin</h5>
                <ol class="ph-breadcrumb">
                    <li><a href="{{ route('pemilik.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('pemilik.admin.index') }}">Admin</a></li>
                    <li><span class="bc-active">Tambah</span></li>
                </ol>
            </div>
        </div>
        <a href="{{ route('pemilik.admin.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="page-inner">
        <form action="{{ route('pemilik.admin.store') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf

            <div class="row g-4">

                {{-- ── Kolom Kiri: Form ── --}}
                <div class="col-lg-8">

                    {{-- Data Akun --}}
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="section-divider"><i class="fas fa-user me-2"></i>Data Akun</div>
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label">Username <span class="required-mark">*</span></label>
                                    <input type="text" name="username"
                                        class="form-control @error('username') is-invalid @enderror"
                                        value="{{ old('username') }}" placeholder="Username login" required>
                                    @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="required-mark">*</span></label>
                                    <input type="email" name="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email') }}" placeholder="email@domain.com" required>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Password <span class="required-mark">*</span></label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="akun-pass"
                                            class="form-control @error('password') is-invalid @enderror"
                                            placeholder="Min. 8 karakter">
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                            onclick="togglePass()" style="border-radius:0 10px 10px 0;">
                                            <i class="fas fa-eye" id="pass-icon"></i>
                                        </button>
                                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="status-box w-100">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                name="is_active" id="is-active" value="1"
                                                {{ old('is_active', true) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="is-active">
                                                Aktifkan akun
                                            </label>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- Data Admin --}}
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="section-divider"><i class="fas fa-id-card me-2"></i>Data Admin</div>
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label">Nama Lengkap <span class="required-mark">*</span></label>
                                    <input type="text" name="nama"
                                        class="form-control @error('nama') is-invalid @enderror"
                                        value="{{ old('nama') }}" placeholder="Nama lengkap admin" required>
                                    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">NIP</label>
                                    <input type="text" name="nip"
                                        class="form-control @error('nip') is-invalid @enderror"
                                        value="{{ old('nip') }}" placeholder="Nomor Induk Pegawai (opsional)">
                                    @error('nip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">No. Telp / WhatsApp <span class="required-mark">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        <input type="text" name="no_telp"
                                            class="form-control @error('no_telp') is-invalid @enderror"
                                            value="{{ old('no_telp') }}" placeholder="08xxxxxxxxxx" required>
                                        @error('no_telp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Alamat</label>
                                    <input type="text" name="alamat"
                                        class="form-control @error('alamat') is-invalid @enderror"
                                        value="{{ old('alamat') }}" placeholder="Alamat lengkap">
                                    @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                            </div>
                        </div>
                    </div>

                </div>

                {{-- ── Kolom Kanan: Foto & Simpan ── --}}
                <div class="col-lg-4">

                    {{-- Foto Profil --}}
                    <div class="card shadow-sm mb-4">
                        <div class="card-body text-center">
                            <div class="section-divider text-start"><i class="fas fa-camera me-2"></i>Foto Profil</div>
                            <div class="foto-preview-wrap mx-auto mb-3" id="foto-wrap"
                                 onclick="document.getElementById('foto-input').click()">
                                <img id="foto-preview"
                                    src="{{ asset('user/img/default-avatar.png') }}"
                                    onerror="this.src='https://ui-avatars.com/api/?name=Admin&background=e9ecef&color=6c757d&size=130'"
                                    alt="Preview Foto">
                            </div>
                            <input type="file" name="foto_profil" id="foto-input" accept="image/*" class="d-none">
                            <button type="button" class="btn btn-outline-primary btn-sm w-100"
                                onclick="document.getElementById('foto-input').click()">
                                <i class="fas fa-upload me-1"></i> Upload Foto
                            </button>
                            <div class="text-muted small mt-2">JPG/PNG/WEBP, maks. 2 MB</div>
                            @error('foto_profil')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Tombol Simpan --}}
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-save me-2"></i> Simpan Admin
                            </button>
                            <a href="{{ route('pemilik.admin.index') }}" class="btn btn-outline-secondary w-100">
                                Batal
                            </a>
                        </div>
                    </div>

                </div>

            </div>
        </form>
    </div>{{-- end .page-inner --}}

</div>
@endsection

@section('scripts')
<script>
    // ── Preview foto ──────────────────────────────────────────────────
    document.getElementById('foto-input').addEventListener('change', function () {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = e => document.getElementById('foto-preview').src = e.target.result;
            reader.readAsDataURL(this.files[0]);
        }
    });

    // ── Toggle password visibility ────────────────────────────────────
    function togglePass() {
        const input = document.getElementById('akun-pass');
        const icon  = document.getElementById('pass-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>
@endsection
