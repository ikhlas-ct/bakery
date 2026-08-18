@extends('layouts.user.user')

@section('title', 'Pengaturan Website')

@section('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
    body, .card, label, .btn, input, textarea, select { font-family: 'Plus Jakarta Sans', sans-serif; }

    .ph-card {
        background:#fff; border:1px solid #e9ecef; border-radius:14px;
        padding:16px 22px; display:flex; align-items:center;
        justify-content:space-between; gap:16px; flex-wrap:wrap;
        margin-bottom:1.5rem; position:relative; overflow:hidden;
        box-shadow:0 1px 6px rgba(0,0,0,.05);
    }
    .ph-card::before { content:''; position:absolute; left:0; top:0; bottom:0; width:4px; border-radius:14px 0 0 14px; background:#6366f1; }
    .ph-left { display:flex; align-items:center; gap:12px; }
    .ph-icon { width:44px; height:44px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; background:#ede9fe; color:#6366f1; }
    .ph-title { font-size:1.05rem; font-weight:700; color:#1e293b; margin:0; line-height:1.2; }
    .ph-breadcrumb { display:flex; align-items:center; gap:4px; list-style:none; padding:0; margin:4px 0 0; flex-wrap:wrap; }
    .ph-breadcrumb li { display:flex; align-items:center; }
    .ph-breadcrumb li+li::before { content:'›'; color:#cbd5e1; font-size:.7rem; margin:0 4px; }
    .ph-breadcrumb a { font-size:.75rem; color:#1a73e8; text-decoration:none; }
    .ph-breadcrumb .bc-active { font-size:.75rem; color:#94a3b8; }

    .setting-tabs {
        display:flex; gap:4px; flex-wrap:wrap;
        background:#f8fafc; border:1px solid #e9ecef;
        border-radius:12px; padding:5px; margin-bottom:1.5rem;
    }
    .setting-tab {
        flex:1; min-width:120px; text-align:center;
        padding:8px 14px; border-radius:9px; font-size:.8rem;
        font-weight:600; cursor:pointer; border:none;
        background:transparent; color:#64748b;
        transition:all .18s; display:flex; align-items:center;
        justify-content:center; gap:6px;
    }
    .setting-tab:hover { background:#fff; color:#1e293b; }
    .setting-tab.active { background:#fff; color:#6366f1; box-shadow:0 1px 6px rgba(0,0,0,.08); }
    .setting-tab.active i { color:#6366f1; }

    .tab-pane { display:none; }
    .tab-pane.active { display:block; }

    .section-card { border:none; border-radius:14px; box-shadow:0 1px 8px rgba(0,0,0,.06); margin-bottom:1.25rem; }
    .section-card .card-body { padding:22px 24px; }

    .section-divider {
        border-left:4px solid #6366f1; background:#f8f9fa;
        padding:7px 13px; border-radius:0 6px 6px 0;
        font-weight:700; font-size:.82rem; color:#6366f1;
        display:flex; align-items:center; gap:8px; margin-bottom:1.1rem;
    }

    label { font-size:.83rem; font-weight:600; color:#475569; }
    .form-control, .form-select {
        border-radius:10px; border:1.5px solid #e2e8f0; font-size:.85rem;
        padding:8px 12px; color:#334155; background:#f8fafc;
        transition:border-color .2s, box-shadow .2s;
    }
    .form-control:focus, .form-select:focus { border-color:#6366f1; background:#fff; box-shadow:0 0 0 3px rgba(99,102,241,.12); }
    .form-text { font-size:.74rem; color:#94a3b8; }

    .img-upload-wrap {
        position:relative; border:2px dashed #e2e8f0; border-radius:12px;
        overflow:hidden; cursor:pointer; background:#fafbfc;
        transition:border-color .2s, background .2s;
        width:100%; max-width:200px;
    }
    .img-upload-wrap:hover { border-color:#6366f1; background:#f5f3ff; }
    .img-upload-wrap img.preview {
        width:100%; height:100px; object-fit:contain; padding:8px; background:#fff;
        display:block;
    }
    .img-upload-wrap .upload-overlay {
        position:absolute; inset:0; display:flex; flex-direction:column;
        align-items:center; justify-content:center; gap:4px;
    }
    .img-upload-wrap.has-img .upload-overlay { background:rgba(0,0,0,.45); opacity:0; transition:opacity .2s; }
    .img-upload-wrap.has-img:hover .upload-overlay { opacity:1; }
    .img-upload-wrap:not(.has-img) .upload-overlay { background:transparent; }
    .upload-overlay i   { font-size:1.6rem; color:#94a3b8; }
    .upload-overlay span { font-size:.75rem; color:#94a3b8; }
    .img-upload-wrap.has-img .upload-overlay i,
    .img-upload-wrap.has-img .upload-overlay span { color:#fff; }

    .social-row { display:flex; align-items:center; gap:10px; margin-bottom:.75rem; }
    .social-icon {
        width:38px; height:38px; border-radius:9px; display:flex; align-items:center;
        justify-content:center; font-size:1rem; flex-shrink:0;
    }
    .social-fb { background:#e7f0fd; color:#1877f2; }
    .social-ig { background:#fce4ec; color:#e1306c; }
    .social-tw { background:#e1f5fe; color:#1da1f2; }
    .social-yt { background:#ffebee; color:#ff0000; }

    .btn-save {
        background:linear-gradient(135deg,#6366f1,#4f46e5); border:none;
        border-radius:10px; font-weight:600; font-size:.85rem;
        padding:9px 24px; color:#fff; box-shadow:0 2px 8px rgba(99,102,241,.35); transition:all .2s;
    }
    .btn-save:hover { filter:brightness(1.07); transform:translateY(-1px); color:#fff; }

    .alert-success { background:#dcfce7; border:1px solid #bbf7d0; color:#15803d; border-radius:12px; font-size:.85rem; }
    .alert-danger  { background:#fee2e2; border:1px solid #fecaca; color:#dc2626; border-radius:12px; font-size:.85rem; }
</style>
@endsection

@section('content')
<div class="container">

    {{-- Page Header --}}
    <div class="ph-card">
        <div class="ph-left">
            <div class="ph-icon"><i class="fas fa-cog"></i></div>
            <div>
                <h5 class="ph-title">Pengaturan Website</h5>
                <ol class="ph-breadcrumb">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><span class="bc-active">Pengaturan Website</span></li>
                </ol>
            </div>
        </div>
    </div>

    {{-- Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Terdapat kesalahan:</strong>
            <ul class="mb-0 mt-1 ps-3">
                @foreach($errors->all() as $err)
                    <li style="font-size:.83rem;">{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Tab Nav --}}
    <div class="setting-tabs">
        <button type="button" class="setting-tab active" data-tab="identitas">
            <i class="fas fa-building"></i> Identitas
        </button>
        <button type="button" class="setting-tab" data-tab="kontak">
            <i class="fas fa-address-book"></i> Kontak &amp; Sosial Media
        </button>
    </div>

    <form action="{{ route('setting.website.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- ══════════ TAB: IDENTITAS ══════════ --}}
        <div class="tab-pane active" id="tab-identitas">
            <div class="card section-card">
                <div class="card-body">
                    <div class="section-divider"><i class="fas fa-building"></i> Identitas Website</div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nama" class="form-label">Nama Website</label>
                            <input type="text" name="nama" id="nama"
                                   class="form-control @error('nama') is-invalid @enderror"
                                   value="{{ old('nama', $setting->nama) }}"
                                   placeholder="Roti Baru Bakery">
                            @error('nama')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="slogan" class="form-label">Slogan</label>
                            <input type="text" name="slogan" id="slogan"
                                   class="form-control @error('slogan') is-invalid @enderror"
                                   value="{{ old('slogan', $setting->slogan) }}"
                                   placeholder="Roti segar setiap hari">
                            @error('slogan')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea name="alamat" id="alamat" rows="3"
                                      class="form-control @error('alamat') is-invalid @enderror"
                                      placeholder="Alamat toko/produksi">{{ old('alamat', $setting->alamat) }}</textarea>
                            @error('alamat')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Logo</label>
                            <div class="img-upload-wrap {{ $setting->logo ? 'has-img' : '' }}" id="logo-zone" onclick="document.getElementById('logo-input').click();">
                                <img src="{{ $setting->logo_url }}" alt="Logo" class="preview" id="logo-preview">
                                <div class="upload-overlay">
                                    <i class="fas fa-camera"></i>
                                    <span>{{ $setting->logo ? 'Ganti logo' : 'Upload logo' }}</span>
                                </div>
                            </div>
                            <input type="file" name="logo" id="logo-input" accept="image/*" class="d-none">
                            <div class="form-text mt-1">Format PNG/JPG, maks 2 MB.</div>
                            @error('logo')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-save">
                            <i class="fas fa-save me-2"></i> Simpan Semua Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════ TAB: KONTAK & SOSIAL MEDIA ══════════ --}}
        <div class="tab-pane" id="tab-kontak">
            <div class="card section-card">
                <div class="card-body">
                    <div class="section-divider"><i class="fas fa-address-book"></i> Kontak</div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $setting->email) }}"
                                   placeholder="info@rotibaru.com">
                            @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="nomor_telepon" class="form-label">Nomor Telepon</label>
                            <input type="text" name="nomor_telepon" id="nomor_telepon"
                                   class="form-control @error('nomor_telepon') is-invalid @enderror"
                                   value="{{ old('nomor_telepon', $setting->nomor_telepon) }}"
                                   placeholder="08xxxxxxxxxx">
                            @error('nomor_telepon')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="section-divider"><i class="fas fa-share-alt"></i> Sosial Media</div>

                    <div class="social-row">
                        <div class="social-icon social-fb"><i class="fab fa-facebook-f"></i></div>
                        <input type="url" name="social_facebook"
                               class="form-control @error('social_facebook') is-invalid @enderror"
                               value="{{ old('social_facebook', $setting->social_facebook) }}"
                               placeholder="https://facebook.com/...">
                    </div>
                    @error('social_facebook')<div class="text-danger small mb-2">{{ $message }}</div>@enderror

                    <div class="social-row">
                        <div class="social-icon social-ig"><i class="fab fa-instagram"></i></div>
                        <input type="url" name="social_instagram"
                               class="form-control @error('social_instagram') is-invalid @enderror"
                               value="{{ old('social_instagram', $setting->social_instagram) }}"
                               placeholder="https://instagram.com/...">
                    </div>
                    @error('social_instagram')<div class="text-danger small mb-2">{{ $message }}</div>@enderror

                    <div class="social-row">
                        <div class="social-icon social-tw"><i class="fab fa-twitter"></i></div>
                        <input type="url" name="social_twitter"
                               class="form-control @error('social_twitter') is-invalid @enderror"
                               value="{{ old('social_twitter', $setting->social_twitter) }}"
                               placeholder="https://twitter.com/...">
                    </div>
                    @error('social_twitter')<div class="text-danger small mb-2">{{ $message }}</div>@enderror

                    <div class="social-row">
                        <div class="social-icon social-yt"><i class="fab fa-youtube"></i></div>
                        <input type="url" name="social_youtube"
                               class="form-control @error('social_youtube') is-invalid @enderror"
                               value="{{ old('social_youtube', $setting->social_youtube) }}"
                               placeholder="https://youtube.com/...">
                    </div>
                    @error('social_youtube')<div class="text-danger small mb-2">{{ $message }}</div>@enderror

                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-save">
                            <i class="fas fa-save me-2"></i> Simpan Semua Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Tab switching ─────────────────────────────────────────
    function activateTab(name) {
        document.querySelectorAll('.setting-tab').forEach(function (t) {
            t.classList.toggle('active', t.dataset.tab === name);
        });
        document.querySelectorAll('.tab-pane').forEach(function (p) {
            p.classList.toggle('active', p.id === 'tab-' + name);
        });
    }

    document.querySelectorAll('.setting-tab').forEach(function (btn) {
        btn.addEventListener('click', function () {
            activateTab(this.dataset.tab);
        });
    });

    @if($errors->any())
        @if($errors->has('nama') || $errors->has('slogan') || $errors->has('alamat') || $errors->has('logo'))
            activateTab('identitas');
        @elseif($errors->has('email') || $errors->has('nomor_telepon') || $errors->has('social_facebook') || $errors->has('social_instagram') || $errors->has('social_twitter') || $errors->has('social_youtube'))
            activateTab('kontak');
        @endif
    @endif

    // ── Preview upload logo ─────────────────────────────────
    var input   = document.getElementById('logo-input');
    var preview = document.getElementById('logo-preview');
    var zone    = document.getElementById('logo-zone');

    input.addEventListener('change', function () {
        var file = this.files[0];
        if (!file || !file.type.match('image.*')) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            zone.classList.add('has-img');
        };
        reader.readAsDataURL(file);
    });
});
</script>
@endsection
