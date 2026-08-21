@extends('layouts.user.user')

@section('title', 'Tambah Bahan Baku yang Saya Sediakan')

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

    .input-group-text {
        border-radius: 10px 0 0 10px !important; border: 1.5px solid #e2e8f0; border-right: none;
        background: #f1f5f9; color: #64748b; font-size: .85rem; font-weight: 600;
    }
    .input-group .form-control { border-radius: 0 10px 10px 0 !important; }

    .info-box {
        background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px;
        padding: 12px 16px; font-size: .8rem; color: #1e40af;
        display: flex; align-items: flex-start; gap: 10px; margin-bottom: 1.25rem;
    }
    .info-box i { margin-top: 2px; }

    .satuan-hint { font-size: .75rem; color: #94a3b8; margin-top: 4px; }

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
</style>
@endsection

@section('content')
<div class="container">

    {{-- ── Page Header ── --}}
    <div class="ph-card">
        <div class="ph-left">
            <div class="ph-icon"><i class="fas fa-boxes-stacked"></i></div>
            <div>
                <h5 class="ph-title">Tambah Bahan Baku</h5>
                <ol class="ph-breadcrumb">
                    <li><a href="{{ route('produsen.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('produsen.bahan-baku.index') }}">Bahan Baku Saya</a></li>
                    <li><span class="bc-active">Tambah</span></li>
                </ol>
            </div>
        </div>
        <a href="{{ route('produsen.bahan-baku.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="section-divider"><i class="fas fa-boxes-stacked me-2"></i>Pilih Bahan Baku</div>

                    <div class="info-box">
                        <i class="fas fa-circle-info"></i>
                        <div>
                            Pilih bahan baku yang bisa Anda sediakan/pasok, lalu tentukan harga per satuan.
                            Hanya bahan baku aktif yang belum Anda pilih sebelumnya yang muncul di daftar ini.
                        </div>
                    </div>

                    @if ($bahanBakus->isEmpty())
                        <div class="text-center py-4">
                            <div class="text-muted mb-2" style="font-size:.85rem;">
                                Semua bahan baku aktif sudah ada di daftar yang Anda sediakan.
                            </div>
                            <a href="{{ route('produsen.bahan-baku.index') }}" class="btn btn-outline-primary btn-sm">
                                Kembali ke Daftar
                            </a>
                        </div>
                    @else
                        <form action="{{ route('produsen.bahan-baku.store') }}" method="POST" novalidate>
                            @csrf

                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Bahan Baku <span class="required-mark">*</span></label>
                                    <select name="bahan_baku_id" id="bahan_baku_id"
                                        class="form-select @error('bahan_baku_id') is-invalid @enderror" required>
                                        <option value="">-- Pilih Bahan Baku --</option>
                                        @foreach ($bahanBakus as $bahanBaku)
                                            <option value="{{ $bahanBaku->id }}"
                                                data-satuan="{{ $bahanBaku->satuan->nama ?? '-' }}"
                                                @selected(old('bahan_baku_id') == $bahanBaku->id)>
                                                {{ $bahanBaku->nama_bahan_baku }} ({{ $bahanBaku->kode_bahan_baku }}) —
                                                {{ $bahanBaku->kategoriBarang->nama_kategori ?? '-' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="satuan-hint" id="satuan-hint"></div>
                                    @error('bahan_baku_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Harga per Satuan <span class="required-mark">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" step="0.01" min="0" name="harga"
                                            class="form-control @error('harga') is-invalid @enderror"
                                            value="{{ old('harga') }}" placeholder="0.00" required>
                                    </div>
                                    <div class="form-text" style="font-size:.75rem;">
                                        Harga yang Anda tawarkan untuk bahan baku ini per satuannya.
                                    </div>
                                    @error('harga')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary flex-fill">
                                    <i class="fas fa-save me-2"></i> Simpan
                                </button>
                                <a href="{{ route('produsen.bahan-baku.index') }}" class="btn btn-outline-secondary flex-fill">
                                    Batal
                                </a>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    const select = document.getElementById('bahan_baku_id');
    const hint = document.getElementById('satuan-hint');

    function updateHint() {
        const opt = select.options[select.selectedIndex];
        const satuan = opt ? opt.dataset.satuan : null;
        hint.textContent = satuan ? `Satuan: ${satuan}` : '';
    }

    select.addEventListener('change', updateHint);
    updateHint();
</script>
@endsection
