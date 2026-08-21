@extends('layouts.user.user')

@section('title', 'Ubah Harga – ' . ($bahanBakuProdusen->bahanBaku->nama_bahan_baku ?? ''))

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
    .form-control[readonly] { background-color: #f1f5f9; color: #64748b; }

    .input-group-text {
        border-radius: 10px 0 0 10px !important; border: 1.5px solid #e2e8f0; border-right: none;
        background: #f1f5f9; color: #64748b; font-size: .85rem; font-weight: 600;
    }
    .input-group .form-control { border-radius: 0 10px 10px 0 !important; }

    .badge-kode {
        background: #eef2ff; color: #4338ca; font-weight: 600;
        font-size: .72rem; padding: 5px 10px; border-radius: 8px;
    }
    .badge-kategori {
        background: #f0f9ff; color: #0369a1; font-weight: 600;
        font-size: .72rem; padding: 5px 10px; border-radius: 8px;
    }

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
                <h5 class="ph-title">Ubah Harga Bahan Baku</h5>
                <ol class="ph-breadcrumb">
                    <li><a href="{{ route('produsen.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('produsen.bahan-baku.index') }}">Bahan Baku Saya</a></li>
                    <li><span class="bc-active">Ubah Harga</span></li>
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
                    <div class="section-divider"><i class="fas fa-boxes-stacked me-2"></i>Data Bahan Baku</div>

                    <div class="row g-3 mb-2">
                        <div class="col-md-12">
                            <label class="form-label">Bahan Baku</label>
                            <input type="text" class="form-control" readonly
                                value="{{ $bahanBakuProdusen->bahanBaku->nama_bahan_baku ?? '-' }}">
                            <div class="mt-2 d-flex gap-2">
                                <span class="badge-kode">{{ $bahanBakuProdusen->bahanBaku->kode_bahan_baku ?? '-' }}</span>
                                <span class="badge-kategori">
                                    {{ $bahanBakuProdusen->bahanBaku->kategoriBarang->nama_kategori ?? '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('produsen.bahan-baku.update', $bahanBakuProdusen->id) }}" method="POST" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Harga per Satuan
                                    ({{ $bahanBakuProdusen->bahanBaku->satuan->nama ?? '-' }})
                                    <span class="required-mark">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" step="0.01" min="0" name="harga" autofocus
                                        class="form-control @error('harga') is-invalid @enderror"
                                        value="{{ old('harga', $bahanBakuProdusen->harga) }}" required>
                                </div>
                                @error('harga')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-save me-2"></i> Simpan Perubahan
                            </button>
                            <a href="{{ route('produsen.bahan-baku.index') }}" class="btn btn-outline-secondary flex-fill">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
