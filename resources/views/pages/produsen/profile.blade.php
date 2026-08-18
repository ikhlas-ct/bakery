@extends('layouts.user.user')

@section('title', 'Profil Saya')

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
    .form-control[readonly], .form-control:disabled { background-color: #f1f5f9; color: #64748b; }

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
    .ph-breadcrumb .bc-active { font-size: .75rem; color: #94a3b8; }

    /* ── Profile summary card ── */
    .profile-card { border: none; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,.07); overflow: hidden; }
    .cover-banner { height: 90px; background: linear-gradient(135deg, #1a73e8 0%, #4f8ef7 100%); }
    .profile-avatar {
        width: 110px; height: 110px; border-radius: 18px; object-fit: cover;
        border: 4px solid #fff; margin-top: -60px; box-shadow: 0 4px 14px rgba(0,0,0,.12);
    }
    .avatar-placeholder-lg {
        width: 110px; height: 110px; border-radius: 18px; border: 4px solid #fff;
        margin-top: -60px; box-shadow: 0 4px 14px rgba(0,0,0,.12);
        display: flex; align-items: center; justify-content: center;
        font-size: 2.2rem; font-weight: 800; background: #dbeafe; color: #1a73e8;
    }
    .profile-name { font-size: 1.2rem; font-weight: 800; color: #1e293b; }
    .profile-role { font-size: .8rem; color: #64748b; }

    .badge-aktif { background: #dcfce7; color: #166534; font-weight: 600; font-size: .74rem; padding: 5px 12px; border-radius: 20px; }
    .badge-nonaktif { background: #fee2e2; color: #991b1b; font-weight: 600; font-size: .74rem; padding: 5px 12px; border-radius: 20px; }
</style>
@endsection

@section('content')
<div class="container">

    {{-- ── Page Header ── --}}
    <div class="ph-card">
        <div class="ph-left">
            <div class="ph-icon"><i class="fas fa-user-circle"></i></div>
            <div>
                <h5 class="ph-title">Profil Saya</h5>
                <ol class="ph-breadcrumb">
                    <li><a href="{{ route('produsen.dashboard') }}">Dashboard</a></li>
                    <li><span class="bc-active">Profil Saya</span></li>
                </ol>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success mb-3" style="border-radius:12px;border:none;font-size:.85rem;">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger mb-3" style="border-radius:12px;border:none;font-size:.85rem;">
            <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
        </div>
    @endif

    <div class="row g-4">

        {{-- ── Kolom Kiri: Ringkasan Profil ── --}}
        <div class="col-lg-4">
            <div class="card profile-card">
                <div class="cover-banner"></div>
                <div class="card-body text-center pt-0">
                    @if ($produsen->user->foto_profil)
                        <img src="{{ asset('storage/' . $produsen->user->foto_profil) }}"
                            class="profile-avatar" alt="{{ $produsen->nama_produsen }}">
                    @else
                        <div class="avatar-placeholder-lg mx-auto">
                            {{ strtoupper(substr($produsen->nama_produsen, 0, 1)) }}
                        </div>
                    @endif

                    <div class="profile-name mt-3">{{ $produsen->nama_produsen }}</div>
                    <div class="profile-role mb-2">Produsen / Supplier</div>

                    @if ($produsen->status_mitra === 'aktif')
                        <span class="badge-aktif"><i class="fas fa-check-circle me-1"></i>Mitra Aktif</span>
                    @else
                        <span class="badge-nonaktif"><i class="fas fa-times-circle me-1"></i>Mitra Nonaktif</span>
                    @endif

                    <div class="text-muted mt-2" style="font-size:.72rem;">
                        <i class="fas fa-info-circle me-1"></i>Status mitra &amp; status akun diatur oleh Admin/Pemilik.
                    </div>

                    <hr class="my-3">

                    <div class="text-start">
                        <div class="d-flex align-items-center gap-2 mb-2" style="font-size:.85rem;">
                            <i class="fas fa-envelope text-muted"></i>
                            <span>{{ $produsen->user->email }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2" style="font-size:.85rem;">
                            <i class="fas fa-phone text-muted"></i>
                            <span>{{ $produsen->no_telp ?: '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Kolom Kanan: Form Edit Profil ── --}}
        <div class="col-lg-8">
            <form action="{{ route('produsen.profile.update') }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')

                {{-- Foto Profil --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="section-divider"><i class="fas fa-camera me-2"></i>Foto Profil</div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="foto-preview-wrap" id="foto-wrap"
                                 onclick="document.getElementById('foto-input').click()">
                                <img id="foto-preview"
                                    src="{{ $produsen->user->foto_profil ? asset('storage/' . $produsen->user->foto_profil) : asset('user/img/default-avatar.png') }}"
                                    onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($produsen->nama_produsen) }}&background=e9ecef&color=6c757d&size=130'"
                                    alt="Preview Foto">
                            </div>
                            <div>
                                <input type="file" name="foto_profil" id="foto-input" accept="image/*" class="d-none">
                                <button type="button" class="btn btn-outline-primary btn-sm"
                                    onclick="document.getElementById('foto-input').click()">
                                    <i class="fas fa-upload me-1"></i> Ganti Foto
                                </button>
                                <div class="text-muted small mt-2">JPG/PNG/WEBP, maks. 2 MB</div>
                                @error('foto_profil')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Data Akun --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="section-divider"><i class="fas fa-user me-2"></i>Data Akun</div>
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Username <span class="required-mark">*</span></label>
                                <input type="text" name="username"
                                    class="form-control @error('username') is-invalid @enderror"
                                    value="{{ old('username', $produsen->user->username) }}" required>
                                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email <span class="required-mark">*</span></label>
                                <input type="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $produsen->user->email) }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Password Baru</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="akun-pass"
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="Kosongkan jika tidak diubah">
                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                        onclick="togglePass()" style="border-radius:0 10px 10px 0;">
                                        <i class="fas fa-eye" id="pass-icon"></i>
                                    </button>
                                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-text">Min. 8 karakter, biarkan kosong untuk memakai password lama.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status Akun</label>
                                <input type="text" class="form-control"
                                    value="{{ $produsen->user->is_active ? 'Aktif' : 'Nonaktif' }}" readonly>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Data Produsen --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="section-divider"><i class="fas fa-industry me-2"></i>Data Produsen</div>
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Nama Produsen <span class="required-mark">*</span></label>
                                <input type="text" name="nama_produsen"
                                    class="form-control @error('nama_produsen') is-invalid @enderror"
                                    value="{{ old('nama_produsen', $produsen->nama_produsen) }}" required>
                                @error('nama_produsen')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">No. Telp / WhatsApp <span class="required-mark">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" name="no_telp"
                                        class="form-control @error('no_telp') is-invalid @enderror"
                                        value="{{ old('no_telp', $produsen->no_telp) }}" required>
                                    @error('no_telp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status Mitra</label>
                                <input type="text" class="form-control text-capitalize"
                                    value="{{ $produsen->status_mitra }}" readonly>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Alamat <span class="required-mark">*</span></label>
                                <input type="text" name="alamat"
                                    class="form-control @error('alamat') is-invalid @enderror"
                                    value="{{ old('alamat', $produsen->alamat) }}" required>
                                @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i> Simpan Perubahan
                    </button>
                </div>

            </form>
        </div>

    </div>

</div>
@endsection

@section('scripts')
<script>
    document.getElementById('foto-input').addEventListener('change', function () {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = e => document.getElementById('foto-preview').src = e.target.result;
            reader.readAsDataURL(this.files[0]);
        }
    });

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
