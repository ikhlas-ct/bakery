@extends('layouts.user.user')

@section('title', 'Tambah Satuan')

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
            <div class="ph-icon"><i class="fas fa-ruler-combined"></i></div>
            <div>
                <h5 class="ph-title">Tambah Satuan</h5>
                <ol class="ph-breadcrumb">
                    <li><a href="{{ $dashboardRoute }}">Dashboard</a></li>
                    <li><a href="{{ route('satuan.index') }}">Satuan</a></li>
                    <li><span class="bc-active">Tambah</span></li>
                </ol>
            </div>
        </div>
        <a href="{{ route('satuan.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="section-divider"><i class="fas fa-ruler me-2"></i>Data Satuan</div>

                    <form action="{{ route('satuan.store') }}" method="POST" novalidate>
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-7">
                                <label class="form-label">Nama Satuan <span class="required-mark">*</span></label>
                                <input type="text" name="nama" autofocus
                                    class="form-control @error('nama') is-invalid @enderror"
                                    value="{{ old('nama') }}" placeholder="Contoh: Kilogram, Liter, Pcs"
                                    required>
                                @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-5">
                                <label class="form-label">Kode Satuan</label>
                                <input type="text" name="kode_satuan"
                                    class="form-control @error('kode_satuan') is-invalid @enderror"
                                    value="{{ old('kode_satuan') }}" placeholder="Contoh: KG, LTR, PCS">
                                @error('kode_satuan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-save me-2"></i> Simpan
                            </button>
                            <a href="{{ route('satuan.index') }}" class="btn btn-outline-secondary flex-fill">
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
