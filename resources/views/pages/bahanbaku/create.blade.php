@extends('layouts.user.user')

@section('title', 'Tambah Bahan Baku')

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

    .form-check-input:checked {
        background-color: #1a73e8; border-color: #1a73e8;
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
@php
    $dashboardRoute = auth()->user()->role === 'admin' ? route('admin.dashboard') : route('pemilik.dashboard');
@endphp
<div class="container">

    {{-- ── Page Header ── --}}
    <div class="ph-card">
        <div class="ph-left">
            <div class="ph-icon"><i class="fas fa-boxes-stacked"></i></div>
            <div>
                <h5 class="ph-title">Tambah Bahan Baku</h5>
                <ol class="ph-breadcrumb">
                    <li><a href="{{ $dashboardRoute }}">Dashboard</a></li>
                    <li><a href="{{ route('bahan-baku.index') }}">Bahan Baku</a></li>
                    <li><span class="bc-active">Tambah</span></li>
                </ol>
            </div>
        </div>
        <a href="{{ route('bahan-baku.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="section-divider"><i class="fas fa-boxes-stacked me-2"></i>Data Bahan Baku</div>

                    <form action="{{ route('bahan-baku.store') }}" method="POST" novalidate>
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label">Kode Bahan Baku <span class="required-mark">*</span></label>
                                <input type="text" name="kode_bahan_baku" autofocus
                                    class="form-control @error('kode_bahan_baku') is-invalid @enderror"
                                    value="{{ old('kode_bahan_baku') }}" placeholder="Contoh: BB-001"
                                    required>
                                @error('kode_bahan_baku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-7">
                                <label class="form-label">Nama Bahan Baku <span class="required-mark">*</span></label>
                                <input type="text" name="nama_bahan_baku"
                                    class="form-control @error('nama_bahan_baku') is-invalid @enderror"
                                    value="{{ old('nama_bahan_baku') }}" placeholder="Contoh: Tepung Terigu Cakra"
                                    required>
                                @error('nama_bahan_baku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Kategori <span class="required-mark">*</span></label>
                                <select name="kategori_barang_id"
                                    class="form-select @error('kategori_barang_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach ($kategoris as $kategori)
                                        <option value="{{ $kategori->id }}" @selected(old('kategori_barang_id') == $kategori->id)>
                                            {{ $kategori->nama_kategori }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kategori_barang_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Satuan <span class="required-mark">*</span></label>
                                <select name="satuan_id"
                                    class="form-select @error('satuan_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Satuan --</option>
                                    @foreach ($satuans as $satuan)
                                        <option value="{{ $satuan->id }}" @selected(old('satuan_id') == $satuan->id)>
                                            {{ $satuan->nama }}{{ $satuan->kode_satuan ? ' ('.$satuan->kode_satuan.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('satuan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Stok Minimum <span class="required-mark">*</span></label>
                                <input type="number" step="0.01" min="0" name="stok_minimum"
                                    class="form-control @error('stok_minimum') is-invalid @enderror"
                                    value="{{ old('stok_minimum', 0) }}" placeholder="0.00" required>
                                <div class="form-text" style="font-size:.75rem;">
                                    Ambang batas stok gudang yang memicu peringatan/permintaan baru.
                                </div>
                                @error('stok_minimum')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="is_aktif" name="is_aktif" value="1"
                                        @checked(old('is_aktif', true))>
                                    <label class="form-check-label" for="is_aktif">
                                        Bahan baku aktif digunakan
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-save me-2"></i> Simpan
                            </button>
                            <a href="{{ route('bahan-baku.index') }}" class="btn btn-outline-secondary flex-fill">
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
