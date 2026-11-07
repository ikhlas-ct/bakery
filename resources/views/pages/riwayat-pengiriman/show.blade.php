@extends('layouts.user.user')

@section('title', 'Detail Pengiriman – ' . $penerimaan->nomor_transaksi)

@section('styles')
<style>
    .section-divider {
        background: #f8f9fa; border-left: 4px solid #1a73e8; padding: 8px 14px;
        border-radius: 0 6px 6px 0; font-weight: 600; font-size: .9rem; color: #1a73e8; margin-bottom: 1rem;
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
        width: 42px; height: 42px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; flex-shrink: 0; background: #dbeafe; color: #1a73e8;
    }
    .ph-title { font-size: 1.15rem; font-weight: 800; color: #1e293b; margin: 0; }
    .ph-breadcrumb { list-style: none; display: flex; gap: 6px; padding: 0; margin: 2px 0 0; }
    .ph-breadcrumb li:not(:last-child)::after { content: '/'; margin-left: 6px; color: #cbd5e1; }
    .ph-breadcrumb a { color: #1a73e8; text-decoration: none; font-size: .78rem; }
    .ph-breadcrumb .bc-active { font-size: .75rem; color: #94a3b8; }

    .info-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: #94a3b8; }
    .info-value { font-size: .9rem; font-weight: 600; color: #1e293b; }
    .pay-amount { font-size: 1.5rem; font-weight: 800; color: #1e293b; }

    .table-item { font-size: .83rem; margin-bottom: 0; }
    .table-item thead th {
        background: #f8fafc; color: #475569; font-weight: 700;
        font-size: .72rem; text-transform: uppercase; letter-spacing: .4px;
        border-bottom: 1.5px solid #eef2f6; padding: 10px 12px; white-space: nowrap;
    }
    .table-item tbody td { padding: 10px 12px; vertical-align: middle; border-bottom: 1px solid #f4f6f8; }
    .table-item tfoot td { padding: 10px 12px; font-weight: 800; color: #1e293b; background: #f8fafc; }

    .badge-kode {
        background: #eef2ff; color: #4338ca; font-weight: 600;
        font-size: .72rem; padding: 5px 10px; border-radius: 8px;
    }
    .badge-status { font-weight: 600; font-size: .72rem; padding: 5px 10px; border-radius: 8px; display: inline-block; }
    .bs-hijau { background: #f0fdf4; color: #166534; }
    .bs-kuning { background: #fef3c7; color: #92400e; }
    .bs-merah { background: #fef2f2; color: #dc2626; }
    .bs-biru { background: #f0f9ff; color: #0369a1; }
    .name-text { font-weight: 700; color: #1e293b; font-size: .85rem; }
    .sub-text { color: #94a3b8; font-size: .74rem; }

    .bukti-thumb {
        max-width: 100%; max-height: 260px; border-radius: 12px;
        border: 1.5px solid #e2e8f0; background: #f8fafc;
    }

    .alert { border-radius: 12px; border: none; font-size: .85rem; }
    .alert-success { background: #dcfce7; color: #166534; }
    .alert-danger { background: #fee2e2; color: #991b1b; }
</style>
@endsection

@section('content')
@php
    $rupiah = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $angka = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');
@endphp
<div class="container">

    {{-- ── Page Header ── --}}
    <div class="ph-card">
        <div class="ph-left">
            <div class="ph-icon"><i class="fas fa-truck"></i></div>
            <div>
                <h5 class="ph-title">Detail Pengiriman {{ $penerimaan->nomor_transaksi }}</h5>
                <ol class="ph-breadcrumb">
                    <li><a href="{{ route('produsen.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('produsen.pengiriman.index') }}">Riwayat Pengiriman</a></li>
                    <li><span class="bc-active">Detail</span></li>
                </ol>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('produsen.pengiriman.cetak-transaksi', $penerimaan->id) }}" target="_blank"
                class="btn btn-primary btn-sm">
                <i class="fas fa-print me-1"></i> Cetak
            </a>
            <a href="{{ route('produsen.pengiriman.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row g-3">

        {{-- ── Rincian item ── --}}
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="section-divider"><i class="fas fa-boxes-stacked me-2"></i>Rincian Item Diterima</div>
                    <div class="table-responsive">
                        <table class="table-item table">
                            <thead>
                                <tr>
                                    <th>Bahan Baku</th>
                                    <th class="text-end">Jumlah Diterima</th>
                                    <th>Tgl. Kedaluwarsa</th>
                                    <th class="text-end">Harga Satuan</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($items as $it)
                                    <tr>
                                        <td>
                                            <div class="name-text">{{ $it['nama'] }}</div>
                                            <div class="sub-text">
                                                {{ $it['kode'] }}
                                                @if ($it['permintaan']) · {{ $it['permintaan'] }} @endif
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold">{{ $angka($it['jumlah']) }} {{ $it['satuan'] }}</td>
                                        <td>{{ $it['kadaluarsa'] ? $it['kadaluarsa']->translatedFormat('d M Y') : '-' }}</td>
                                        <td class="text-end">{{ $it['harga'] !== null ? $rupiah($it['harga']) : '-' }}</td>
                                        <td class="text-end fw-bold">{{ $rupiah($it['subtotal']) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada item pada pengiriman ini.</td></tr>
                                @endforelse
                            </tbody>
                            @if ($items->isNotEmpty())
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-end">Total Nilai</td>
                                        <td class="text-end">{{ $rupiah($totalNilai) }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                    <div class="sub-text mt-2">Subtotal = jumlah diterima × harga satuan pada permintaan.</div>
                </div>
            </div>
        </div>

        {{-- ── Ringkasan ── --}}
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="section-divider"><i class="fas fa-info-circle me-2"></i>Ringkasan</div>

                    <div class="mb-3">
                        <div class="info-label">Total Nilai</div>
                        <div class="pay-amount">{{ $rupiah($totalNilai) }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="info-label">No. Transaksi</div>
                        <span class="badge-kode">{{ $penerimaan->nomor_transaksi }}</span>
                    </div>
                    <div class="mb-3">
                        <div class="info-label">Tanggal Terima</div>
                        <div class="info-value">{{ $penerimaan->tanggal_terima->translatedFormat('d M Y') }}</div>
                    </div>
                    <div>
                        <div class="info-label">Jumlah Item</div>
                        <div class="info-value">{{ $items->count() }} item</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
