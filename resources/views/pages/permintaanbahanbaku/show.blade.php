@extends('layouts.user.user')

@section('title', 'Detail Permintaan – ' . $permintaan->nomor_transaksi)

@section('styles')
<style>
    .section-divider {
        background: #f8f9fa; border-left: 4px solid #1a73e8; padding: 8px 14px;
        border-radius: 0 6px 6px 0; font-weight: 600; font-size: .9rem;
        color: #1a73e8; margin-bottom: 1rem;
    }
    .ph-card {
        background: #fff; border: 1px solid #e9ecef; border-radius: 14px;
        padding: 16px 20px; display: flex; align-items: center;
        justify-content: space-between; gap: 16px; flex-wrap: wrap;
        margin-bottom: 1.25rem; position: relative; overflow: hidden;
        box-shadow: 0 1px 6px rgba(0,0,0,.05);
    }
    .ph-card::before {
        content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px;
        border-radius: 14px 0 0 14px; background: #1a73e8;
    }
    .ph-left { display: flex; align-items: center; gap: 12px; }
    .ph-icon {
        width: 42px; height: 42px; border-radius: 10px; display: flex;
        align-items: center; justify-content: center; font-size: 1rem;
        flex-shrink: 0; background: #dbeafe; color: #1a73e8;
    }
    .ph-title { font-size: 1.15rem; font-weight: 800; color: #1e293b; margin: 0; }
    .ph-breadcrumb { list-style: none; display: flex; gap: 6px; padding: 0; margin: 2px 0 0; }
    .ph-breadcrumb li:not(:last-child)::after { content: '/'; margin-left: 6px; color: #cbd5e1; }
    .ph-breadcrumb a { color: #1a73e8; text-decoration: none; font-size: .78rem; }
    .ph-breadcrumb .bc-active { font-size: .75rem; color: #94a3b8; }

    .info-label { font-size: .75rem; text-transform: uppercase; color: #94a3b8; margin-bottom: 2px; }
    .info-value { font-size: .95rem; font-weight: 600; color: #1e293b; }
    .badge-pending { background: #fef3c7; color: #92400e; }
    .badge-approved { background: #dcfce7; color: #166534; }
    .badge-rejected { background: #fee2e2; color: #991b1b; }
    table.table thead th { font-size: .75rem; text-transform: uppercase; color: #64748b; }
</style>
@endsection

@section('content')
@php
    $dashboardRoute = auth()->user()->role === 'admin' ? route('admin.dashboard') : route('pemilik.dashboard');
    $badgeMap = [
        'pending' => 'badge-pending',
        'disetujui' => 'badge-approved',
        'ditolak' => 'badge-rejected',
    ];
@endphp
<div class="container">

    <div class="ph-card">
        <div class="ph-left">
            <div class="ph-icon"><i class="fas fa-file-invoice"></i></div>
            <div>
                <h5 class="ph-title">Detail Permintaan {{ $permintaan->nomor_transaksi }}</h5>
                <ol class="ph-breadcrumb">
                    <li><a href="{{ $dashboardRoute }}">Dashboard</a></li>
                    <li><a href="{{ route('permintaan-bahan-baku.index') }}">Permintaan Bahan Baku</a></li>
                    <li><span class="bc-active">Detail</span></li>
                </ol>
            </div>
        </div>
        <a href="{{ route('permintaan-bahan-baku.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="section-divider"><i class="fas fa-info-circle me-2"></i>Informasi Permintaan</div>
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="info-label">Nomor Transaksi</div>
                    <div class="info-value">{{ $permintaan->nomor_transaksi }}</div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Tanggal Permintaan</div>
                    <div class="info-value">{{ \Carbon\Carbon::parse($permintaan->tanggal_permintaan)->format('d M Y') }}</div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Diminta Oleh</div>
                    <div class="info-value">{{ $permintaan->admin->nama ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Jumlah Item</div>
                    <div class="info-value">{{ $permintaan->permintaanBahanBakuDetails->count() }} item</div>
                </div>
                <div class="col-md-12">
                    <div class="info-label">Keterangan</div>
                    <div class="info-value fw-normal">{{ $permintaan->keterangan ?: '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="section-divider"><i class="fas fa-boxes-stacked me-2"></i>Item Diminta</div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Bahan Baku</th>
                            <th>Produsen</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-end">Jumlah Diminta</th>
                            <th class="text-end">Jumlah Disetujui</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($permintaan->permintaanBahanBakuDetails as $detail)
                            <tr>
                                <td>
                                    {{ $detail->bahanBaku->nama_bahan_baku ?? '-' }}
                                    <div class="text-muted" style="font-size:.75rem;">{{ $detail->bahanBaku->kode_bahan_baku ?? '' }}</div>
                                </td>
                                <td>{{ $detail->produsen->nama_produsen ?? '-' }}</td>
                                <td class="text-end">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                                <td class="text-end">
                                    {{ rtrim(rtrim(number_format($detail->jumlah_diminta, 2, ',', '.'), '0'), ',') }}
                                    {{ $detail->bahanBaku->satuan->nama ?? '' }}
                                </td>
                                <td class="text-end">
                                    {{ $detail->jumlah_disetujui !== null
                                        ? rtrim(rtrim(number_format($detail->jumlah_disetujui, 2, ',', '.'), '0'), ',')
                                        : '-' }}
                                </td>
                                <td>
                                    <span class="badge {{ $badgeMap[$detail->status_produsen] ?? 'badge-pending' }}">
                                        {{ ucfirst($detail->status_produsen) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('permintaan-bahan-baku.edit', $permintaan->id) }}" class="btn btn-outline-secondary">
            <i class="fas fa-pen me-1"></i> Edit Data Header
        </a>
    </div>
</div>
@endsection
