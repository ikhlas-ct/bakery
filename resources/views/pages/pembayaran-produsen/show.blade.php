@extends('layouts.user.user')

@section('title', 'Detail Pembayaran – ' . ($pembayaran->permintaanBahanBaku->nomor_transaksi ?? ''))

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
    $lunas = $pembayaran->status_bayar === 'lunas';
    $statusProdusen = [
        'pending' => ['Menunggu', 'bs-kuning'],
        'disetujui' => ['Disetujui', 'bs-hijau'],
        'disetujui_sebagian' => ['Disetujui sebagian', 'bs-biru'],
        'ditolak' => ['Ditolak', 'bs-merah'],
    ];
    $nomorPermintaan = $pembayaran->permintaanBahanBaku->nomor_transaksi ?? '-';
@endphp
<div class="container">

    {{-- ── Page Header ── --}}
    <div class="ph-card">
        <div class="ph-left">
            <div class="ph-icon"><i class="fas fa-wallet"></i></div>
            <div>
                <h5 class="ph-title">Detail Pembayaran {{ $nomorPermintaan }}</h5>
                <ol class="ph-breadcrumb">
                    <li><a href="{{ route('produsen.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('produsen.pembayaran.index') }}">Pembayaran Saya</a></li>
                    <li><span class="bc-active">Detail</span></li>
                </ol>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('produsen.pembayaran.cetak-transaksi', $pembayaran->id) }}" target="_blank"
                class="btn btn-primary btn-sm">
                <i class="fas fa-print me-1"></i> Cetak
            </a>
            <a href="{{ route('produsen.pembayaran.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success d-flex align-items-center mb-3 gap-2">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger d-flex align-items-center mb-3 gap-2">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    <div class="row g-3">

        {{-- ── Kolom kiri: rincian ── --}}
        <div class="col-lg-8">

            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="section-divider"><i class="fas fa-boxes-stacked me-2"></i>Item Permintaan</div>
                    <div class="table-responsive">
                        <table class="table-item table">
                            <thead>
                                <tr>
                                    <th>Bahan Baku</th>
                                    <th class="text-end">Harga</th>
                                    <th class="text-end">Diminta</th>
                                    <th class="text-end">Disetujui</th>
                                    <th class="text-end">Diterima</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($items as $it)
                                    @php [$labelStatus, $kelasStatus] = $statusProdusen[$it['status']] ?? [ucfirst($it['status']), 'bs-kuning']; @endphp
                                    <tr>
                                        <td>
                                            <div class="name-text">{{ $it['nama'] }}</div>
                                            <div class="sub-text">{{ $it['kode'] }}</div>
                                        </td>
                                        <td class="text-end">{{ $rupiah($it['harga']) }}</td>
                                        <td class="text-end">{{ $angka($it['diminta']) }} {{ $it['satuan'] }}</td>
                                        <td class="text-end">
                                            {{ $it['disetujui'] !== null ? $angka($it['disetujui']) . ' ' . $it['satuan'] : '-' }}
                                        </td>
                                        <td class="text-end fw-bold">{{ $angka($it['diterima']) }} {{ $it['satuan'] }}</td>
                                        <td><span class="badge-status {{ $kelasStatus }}">{{ $labelStatus }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada item untuk permintaan ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="section-divider"><i class="fas fa-file-invoice-dollar me-2"></i>Rincian Tagihan</div>
                    <div class="table-responsive">
                        <table class="table-item table">
                            <thead>
                                <tr>
                                    <th>Nota Penerimaan</th>
                                    <th>Bahan Baku</th>
                                    <th class="text-end">Jumlah</th>
                                    <th class="text-end">Harga</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rincian as $r)
                                    <tr>
                                        <td>
                                            <div class="sub-text">{{ $r['nota'] }}</div>
                                            <div class="sub-text">{{ $r['tanggal']->translatedFormat('d M Y') }}</div>
                                        </td>
                                        <td class="name-text">{{ $r['nama'] }}</td>
                                        <td class="text-end">{{ $angka($r['jumlah']) }} {{ $r['satuan'] }}</td>
                                        <td class="text-end">{{ $rupiah($r['harga']) }}</td>
                                        <td class="text-end fw-bold">{{ $rupiah($r['subtotal']) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada barang yang diterima untuk permintaan ini.</td></tr>
                                @endforelse
                            </tbody>
                            @if ($rincian->isNotEmpty())
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-end">Total Tagihan</td>
                                        <td class="text-end">{{ $rupiah($pembayaran->jumlah_bayar) }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                    <div class="sub-text mt-2">Tagihan = jumlah barang yang diterima admin × harga satuan.</div>
                </div>
            </div>
        </div>

        {{-- ── Kolom kanan: ringkasan, bukti, receipt ── --}}
        <div class="col-lg-4">

            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="section-divider"><i class="fas fa-info-circle me-2"></i>Ringkasan</div>

                    <div class="mb-3">
                        <div class="info-label">Total Tagihan</div>
                        <div class="pay-amount">{{ $rupiah($pembayaran->jumlah_bayar) }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="info-label">Permintaan</div>
                        <span class="badge-kode">{{ $nomorPermintaan }}</span>
                        @if ($pembayaran->permintaanBahanBaku?->tanggal_permintaan)
                            <div class="sub-text mt-1">
                                {{ \Carbon\Carbon::parse($pembayaran->permintaanBahanBaku->tanggal_permintaan)->translatedFormat('d M Y') }}
                            </div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <div class="info-label mb-1">Status</div>
                        <div class="d-flex flex-wrap gap-1">
                            <span class="badge-status {{ $lunas ? 'bs-hijau' : 'bs-kuning' }}">{{ $lunas ? 'Lunas' : 'Belum dibayar' }}</span>
                            <span class="badge-status {{ $pembayaran->status_acc === 'disetujui' ? 'bs-hijau' : ($pembayaran->status_acc === 'ditolak' ? 'bs-merah' : 'bs-kuning') }}">
                                ACC: {{ ucfirst($pembayaran->status_acc) }}
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="info-label">Metode Bayar</div>
                        <div class="info-value">{{ $pembayaran->metode_bayar ?: '-' }}</div>
                    </div>
                    <div>
                        <div class="info-label">Tanggal Bayar</div>
                        <div class="info-value">{{ $pembayaran->tanggal_bayar ? $pembayaran->tanggal_bayar->translatedFormat('d M Y') : '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="section-divider"><i class="fas fa-image me-2"></i>Bukti Pembayaran</div>
                    @if ($pembayaran->bukti_bayar)
                        <div class="sub-text mb-2">
                            {{ $pembayaran->metode_bayar === 'Transfer' ? 'Bukti transfer' : 'Bukti pembayaran tunai' }}
                        </div>
                        <a href="{{ asset('storage/' . $pembayaran->bukti_bayar) }}" target="_blank" rel="noopener" title="Buka di tab baru">
                            <img src="{{ asset('storage/' . $pembayaran->bukti_bayar) }}" alt="Bukti pembayaran" class="bukti-thumb">
                        </a>
                    @elseif (! $lunas)
                        <div class="sub-text"><i class="fas fa-lock me-1"></i> Bukti akan tersedia setelah pembayaran lunas.</div>
                    @else
                        <div class="sub-text"><i class="fas fa-image me-1"></i> Belum ada bukti pembayaran.</div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="section-divider"><i class="fas fa-receipt me-2"></i>Gambar Receipt</div>
                    @forelse ($receipts as $rc)
                        <div class="{{ $loop->last ? '' : 'mb-3' }}">
                            <div class="sub-text mb-2">
                                {{ $rc->nomor_transaksi }} · {{ $rc->tanggal_terima->translatedFormat('d M Y') }}
                            </div>
                            <a href="{{ asset('storage/' . $rc->foto_receipt) }}" target="_blank" rel="noopener" title="Buka di tab baru">
                                <img src="{{ asset('storage/' . $rc->foto_receipt) }}" alt="Receipt {{ $rc->nomor_transaksi }}" class="bukti-thumb">
                            </a>
                        </div>
                    @empty
                        <div class="sub-text"><i class="fas fa-image me-1"></i> Belum ada gambar receipt.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
