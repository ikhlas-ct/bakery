@extends('layouts.user.user')

@section('title', 'Pengaturan Website')

@section('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
    body, .card, label, .btn, input, textarea, select { font-family: 'Plus Jakarta Sans', sans-serif; }

    /* ── Layout ── */
    .container { padding-left:28px; padding-right:24px; }

    /* ── Page Header ── */
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

    /* ── Tab Nav ── */
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

    /* ── Section cards ── */
    .section-card { border:none; border-radius:14px; box-shadow:0 1px 8px rgba(0,0,0,.06); margin-bottom:1.25rem; }
    .section-card .card-body { padding:22px 24px; }

    .section-divider {
        border-left:4px solid #6366f1; background:#f8f9fa;
        padding:7px 13px; border-radius:0 6px 6px 0;
        font-weight:700; font-size:.82rem; color:#6366f1;
        display:flex; align-items:center; gap:8px; margin-bottom:1.1rem;
    }

    /* ── Form ── */
    label { font-size:.83rem; font-weight:600; color:#475569; }
    .required-mark { color:#dc3545; }
    .form-control, .form-select {
        border-radius:10px; border:1.5px solid #e2e8f0; font-size:.85rem;
        padding:8px 12px; color:#334155; background:#f8fafc;
        transition:border-color .2s, box-shadow .2s;
    }
    .form-control:focus, .form-select:focus { border-color:#6366f1; background:#fff; box-shadow:0 0 0 3px rgba(99,102,241,.12); }
    .form-control::placeholder { color:#b0bec5; }
    .input-group-text {
        background:#f8fafc; border:1.5px solid #e2e8f0; border-right:none;
        border-radius:10px 0 0 10px; font-size:.85rem; color:#94a3b8;
    }
    .input-group .form-control { border-left:none; border-radius:0 10px 10px 0; }
    .form-text { font-size:.74rem; color:#94a3b8; }

    /* ── Summernote ── */
    .note-editor.note-frame { border-radius:10px; border:1.5px solid #e2e8f0; overflow:hidden; }
    .note-editor.note-frame .note-toolbar { background:#f8fafc; border-bottom:1px solid #e2e8f0; }
    .note-editor.note-frame.focus { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.12); }
    .note-editor .note-editable { font-family:'Plus Jakarta Sans',sans-serif; font-size:.9rem; min-height:240px; }

    /* ── Image upload zone ── */
    .img-upload-wrap {
        position:relative; border:2px dashed #e2e8f0; border-radius:12px;
        overflow:hidden; cursor:pointer; background:#fafbfc;
        transition:border-color .2s, background .2s;
    }
    .img-upload-wrap:hover { border-color:#6366f1; background:#f5f3ff; }
    .img-upload-wrap img.preview {
        width:100%; height:140px; object-fit:cover;
        display:block;
    }
    .img-upload-wrap .upload-overlay {
        position:absolute; inset:0; display:flex; flex-direction:column;
        align-items:center; justify-content:center; gap:4px;
    }
    .img-upload-wrap.has-img .upload-overlay {
        background:rgba(0,0,0,.45); opacity:0; transition:opacity .2s;
    }
    .img-upload-wrap.has-img:hover .upload-overlay { opacity:1; }
    .img-upload-wrap:not(.has-img) .upload-overlay { background:transparent; }
    .upload-overlay i   { font-size:1.6rem; color:#94a3b8; }
    .upload-overlay span { font-size:.75rem; color:#94a3b8; }
    .img-upload-wrap.has-img .upload-overlay i,
    .img-upload-wrap.has-img .upload-overlay span { color:#fff; }

    /* ── Logo wrap (smaller) ── */
    .logo-wrap { width:100%; max-width:200px; }
    .logo-wrap .img-upload-wrap img.preview { height:100px; object-fit:contain; padding:8px; background:#fff; }

    /* ── Social inputs ── */
    .social-row { display:flex; align-items:center; gap:10px; margin-bottom:.75rem; }
    .social-icon {
        width:38px; height:38px; border-radius:9px; display:flex; align-items:center;
        justify-content:center; font-size:1rem; flex-shrink:0;
    }
    .social-fb   { background:#e7f0fd; color:#1877f2; }
    .social-ig   { background:#fce4ec; color:#e1306c; }
    .social-tw   { background:#e1f5fe; color:#1da1f2; }
    .social-yt   { background:#ffebee; color:#ff0000; }

    /* ── Btn ── */
    .btn-save {
        background:linear-gradient(135deg,#6366f1,#4f46e5); border:none;
        border-radius:10px; font-weight:600; font-size:.85rem;
        padding:9px 24px; color:#fff; box-shadow:0 2px 8px rgba(99,102,241,.35); transition:all .2s;
    }
    .btn-save:hover { filter:brightness(1.07); transform:translateY(-1px); color:#fff; }
    .btn-outline-secondary { border-radius:10px; font-size:.83rem; border-color:#e2e8f0; color:#64748b; }
    .btn-outline-secondary:hover { background:#f1f5f9; }

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
                    <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
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
            <i class="fas fa-address-card"></i> Kontak & Sosial
        </button>
        <button type="button" class="setting-tab" data-tab="pengantar">
            <i class="fas fa-image"></i> Pengantar
        </button>
        <button type="button" class="setting-tab" data-tab="tentang">
            <i class="fas fa-info-circle"></i> Tentang Kami
        </button>
    </div>

    <form action="{{ route('setting.website.update') }}" method="POST"
          enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')

        {{-- ══════════ TAB: IDENTITAS ══════════ --}}
        <div class="tab-pane active" id="tab-identitas">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card section-card">
                        <div class="card-body">
                            <div class="section-divider"><i class="fas fa-building"></i> Identitas Website</div>

                            <div class="mb-3">
                                <label class="form-label">Nama Website / Instansi</label>
                                <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                                       value="{{ old('nama', $setting->nama) }}"
                                       placeholder="Contoh: Dinas Sosial Kota ...">
                                @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Slogan / Tagline</label>
                                <input type="text" name="slogan" class="form-control @error('slogan') is-invalid @enderror"
                                       value="{{ old('slogan', $setting->slogan) }}"
                                       placeholder="Slogan singkat instansi">
                                @error('slogan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-0">
                                <label class="form-label">Alamat Lengkap</label>
                                <textarea name="alamat" rows="3"
                                          class="form-control @error('alamat') is-invalid @enderror"
                                          placeholder="Jl. Contoh No. 1, Kelurahan, Kecamatan, Kota">{{ old('alamat', $setting->alamat) }}</textarea>
                                @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card section-card">
                        <div class="card-body">
                            <div class="section-divider"><i class="fas fa-image"></i> Logo</div>

                            <div class="logo-wrap mb-2">
                                <div class="img-upload-wrap {{ $setting->logo ? 'has-img' : '' }}"
                                     id="logo-zone" onclick="document.getElementById('logo-input').click()">
                                    @if($setting->logo)
                                        <img class="preview" id="logo-preview"
                                             src="{{ asset('storage/' . $setting->logo) }}" alt="Logo">
                                    @else
                                        <img class="preview" id="logo-preview" src="" style="display:none;height:100px;" alt="">
                                    @endif
                                    <div class="upload-overlay">
                                        <i class="fas fa-{{ $setting->logo ? 'sync-alt' : 'cloud-upload-alt' }}"></i>
                                        <span>{{ $setting->logo ? 'Ganti Logo' : 'Upload Logo' }}</span>
                                    </div>
                                </div>
                            </div>
                            <input type="file" name="logo" id="logo-input" accept="image/*" class="d-none">
                            <div class="form-text mb-0">PNG/SVG transparan direkomendasikan. Maks 2 MB.</div>
                            @error('logo')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="card section-card">
                        <div class="card-body p-3">
                            <button type="submit" class="btn btn-save w-100">
                                <i class="fas fa-save me-2"></i> Simpan Semua Perubahan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════ TAB: KONTAK & SOSIAL ══════════ --}}
        <div class="tab-pane" id="tab-kontak">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card section-card">
                        <div class="card-body">
                            <div class="section-divider"><i class="fas fa-phone-alt"></i> Kontak</div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email', $setting->email) }}"
                                           placeholder="email@instansi.go.id">
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="mb-0">
                                <label class="form-label">Nomor Telepon</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" name="nomor_telepon"
                                           class="form-control @error('nomor_telepon') is-invalid @enderror"
                                           value="{{ old('nomor_telepon', $setting->nomor_telepon) }}"
                                           placeholder="08xx-xxxx-xxxx" maxlength="20">
                                    @error('nomor_telepon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card section-card">
                        <div class="card-body">
                            <div class="section-divider"><i class="fas fa-share-alt"></i> Media Sosial</div>

                            {{-- Facebook --}}
                            <div class="social-row">
                                <div class="social-icon social-fb"><i class="fab fa-facebook-f"></i></div>
                                <div class="flex-fill">
                                    <input type="url" name="social_facebook"
                                           class="form-control @error('social_facebook') is-invalid @enderror"
                                           value="{{ old('social_facebook', $setting->social_facebook) }}"
                                           placeholder="https://facebook.com/...">
                                    @error('social_facebook')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- Instagram --}}
                            <div class="social-row">
                                <div class="social-icon social-ig"><i class="fab fa-instagram"></i></div>
                                <div class="flex-fill">
                                    <input type="url" name="social_instagram"
                                           class="form-control @error('social_instagram') is-invalid @enderror"
                                           value="{{ old('social_instagram', $setting->social_instagram) }}"
                                           placeholder="https://instagram.com/...">
                                    @error('social_instagram')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- Twitter / X --}}
                            <div class="social-row">
                                <div class="social-icon social-tw"><i class="fab fa-twitter"></i></div>
                                <div class="flex-fill">
                                    <input type="url" name="social_twitter"
                                           class="form-control @error('social_twitter') is-invalid @enderror"
                                           value="{{ old('social_twitter', $setting->social_twitter) }}"
                                           placeholder="https://twitter.com/...">
                                    @error('social_twitter')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- YouTube --}}
                            <div class="social-row mb-0">
                                <div class="social-icon social-yt"><i class="fab fa-youtube"></i></div>
                                <div class="flex-fill">
                                    <input type="url" name="social_youtube"
                                           class="form-control @error('social_youtube') is-invalid @enderror"
                                           value="{{ old('social_youtube', $setting->social_youtube) }}"
                                           placeholder="https://youtube.com/@...">
                                    @error('social_youtube')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="text-end">
                        <button type="submit" class="btn btn-save">
                            <i class="fas fa-save me-2"></i> Simpan Semua Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════ TAB: PENGANTAR ══════════ --}}
        <div class="tab-pane" id="tab-pengantar">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card section-card">
                        <div class="card-body">
                            <div class="section-divider"><i class="fas fa-align-left"></i> Konten Pengantar</div>

                            <div class="mb-3">
                                <label class="form-label">Judul Pengantar</label>
                                <input type="text" name="title_pengantar"
                                       class="form-control @error('title_pengantar') is-invalid @enderror"
                                       value="{{ old('title_pengantar', $setting->title_pengantar) }}"
                                       placeholder="Judul yang tampil di halaman depan">
                                @error('title_pengantar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-0">
                                <label class="form-label">Paragraf Pengantar</label>
                                <textarea name="paragraf_pengantar" id="paragraf_pengantar"
                                          class="form-control summernote-pengantar @error('paragraf_pengantar') is-invalid @enderror"
                                          rows="8">{{ old('paragraf_pengantar', $setting->paragraf_pengantar) }}</textarea>
                                @error('paragraf_pengantar')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card section-card">
                        <div class="card-body">
                            <div class="section-divider"><i class="fas fa-image"></i> Gambar Pengantar</div>

                            <div class="img-upload-wrap {{ $setting->gambar_pengantar ? 'has-img' : '' }}"
                                 id="pengantar-zone" onclick="document.getElementById('pengantar-input').click()">
                                @if($setting->gambar_pengantar)
                                    <img class="preview" id="pengantar-preview"
                                         src="{{ asset('storage/' . $setting->gambar_pengantar) }}" alt="Gambar Pengantar">
                                @else
                                    <img class="preview" id="pengantar-preview" src="" style="display:none;" alt="">
                                @endif
                                <div class="upload-overlay">
                                    <i class="fas fa-{{ $setting->gambar_pengantar ? 'sync-alt' : 'cloud-upload-alt' }}"></i>
                                    <span>{{ $setting->gambar_pengantar ? 'Ganti Gambar' : 'Upload Gambar' }}</span>
                                </div>
                            </div>
                            <input type="file" name="gambar_pengantar" id="pengantar-input" accept="image/*" class="d-none">
                            <div class="form-text mt-1">Rasio 4:3 atau 16:9 direkomendasikan. Maks 3 MB.</div>
                            @error('gambar_pengantar')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-save w-100">
                            <i class="fas fa-save me-2"></i> Simpan Semua Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════ TAB: TENTANG KAMI ══════════ --}}
        <div class="tab-pane" id="tab-tentang">
            <div class="card section-card">
                <div class="card-body">
                    <div class="section-divider"><i class="fas fa-info-circle"></i> Tentang Kami (About Us)</div>
                    <p class="text-muted mb-3" style="font-size:.83rem;">
                        <i class="fas fa-lightbulb text-warning me-1"></i>
                        Konten ini akan tampil di halaman "Tentang Kami" website publik.
                    </p>
                    <textarea name="about_us" id="about_us"
                              class="form-control summernote-about @error('about_us') is-invalid @enderror"
                              rows="12">{{ old('about_us', $setting->about_us) }}</textarea>
                    @error('about_us')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="text-end mt-2">
                <button type="submit" class="btn btn-save">
                    <i class="fas fa-save me-2"></i> Simpan Semua Perubahan
                </button>
            </div>
        </div>

    </form>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {

    // ── Summernote config (reusable) ──────────────────────────
    var summernoteConfig = {
        height: 300,
        lang: 'id-ID',
        toolbar: [
            ['style',    ['style']],
            ['font',     ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color',    ['color']],
            ['para',     ['ul', 'ol', 'paragraph']],
            ['height',   ['height']],
            ['table',    ['table']],
            ['insert',   ['link', 'picture', 'video', 'hr']],
            ['view',     ['fullscreen', 'codeview']],
        ],
        callbacks: {
            onImageUpload: function (files) {
                uploadSettingImage(files[0], $(this));
            },
            onMediaDelete: function (target) {
                deleteSettingImage($(target).attr('src'));
            }
        }
    };

    $('.summernote-pengantar').summernote($.extend({}, summernoteConfig, {
        height: 280,
        placeholder: 'Tulis paragraf pengantar di sini…'
    }));

    $('.summernote-about').summernote($.extend({}, summernoteConfig, {
        height: 400,
        placeholder: 'Tulis konten tentang kami di sini…'
    }));

    // MutationObserver — tiap summernote
    document.querySelectorAll('.note-editable').forEach(function (editable) {
        new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.removedNodes.forEach(function (node) {
                    if (node.nodeName === 'IMG') {
                        deleteSettingImage(node.getAttribute('src'));
                    } else if (node.querySelectorAll) {
                        node.querySelectorAll('img').forEach(function (img) {
                            deleteSettingImage(img.getAttribute('src'));
                        });
                    }
                });
            });
        }).observe(editable, { childList: true, subtree: true });
    });

    function uploadSettingImage(file, $editor) {
        var data = new FormData();
        data.append('image', file);
        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: "{{ route('setting.upload.image') }}",
            method: 'POST', data: data, contentType: false, processData: false,
            success: function (res) {
                if (res.url) $editor.summernote('insertImage', res.url);
            },
            error: function (xhr) { console.error('Upload gagal:', xhr.responseText); }
        });
    }

    function deleteSettingImage(src) {
        if (!src) return;
        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: "{{ route('setting.delete.image') }}",
            method: 'POST', data: { src: src }
        });
    }

    // ── Tab switching ─────────────────────────────────────────
    var activeTabKey = 'setting_active_tab';

    function activateTab(name) {
        document.querySelectorAll('.setting-tab').forEach(function (t) {
            t.classList.toggle('active', t.dataset.tab === name);
        });
        document.querySelectorAll('.tab-pane').forEach(function (p) {
            p.classList.toggle('active', p.id === 'tab-' + name);
        });
        localStorage.setItem(activeTabKey, name);
    }

    document.querySelectorAll('.setting-tab').forEach(function (btn) {
        btn.addEventListener('click', function () {
            activateTab(this.dataset.tab);
        });
    });

    // Restore tab setelah redirect (simpan di localStorage)
    var savedTab = localStorage.getItem(activeTabKey);
    if (savedTab) activateTab(savedTab);

    // Jika ada error, otomatis ke tab yang punya error
    @if($errors->any())
        @if($errors->has('nama') || $errors->has('slogan') || $errors->has('alamat') || $errors->has('logo'))
            activateTab('identitas');
        @elseif($errors->has('email') || $errors->has('nomor_telepon') || $errors->has('social_facebook') || $errors->has('social_instagram') || $errors->has('social_twitter') || $errors->has('social_youtube'))
            activateTab('kontak');
        @elseif($errors->has('title_pengantar') || $errors->has('paragraf_pengantar') || $errors->has('gambar_pengantar'))
            activateTab('pengantar');
        @elseif($errors->has('about_us'))
            activateTab('tentang');
        @endif
    @endif

    // ── Preview upload gambar ─────────────────────────────────
    function initImgPreview(inputId, previewId, zoneId) {
        var input   = document.getElementById(inputId);
        var preview = document.getElementById(previewId);
        var zone    = document.getElementById(zoneId);
        if (!input) return;
        input.addEventListener('change', function () {
            var file = this.files[0];
            if (!file || !file.type.match('image.*')) return;
            var reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                if (zone) zone.classList.add('has-img');
            };
            reader.readAsDataURL(file);
        });
    }

    initImgPreview('logo-input',     'logo-preview',     'logo-zone');
    initImgPreview('pengantar-input','pengantar-preview','pengantar-zone');

});
</script>
@endsection
