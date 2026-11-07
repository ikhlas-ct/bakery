@extends('layouts.user.user')

@section('title', 'Pembayaran Saya')

@section('styles')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        body, .card, .table, .btn, h4, h5 {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* ===== STAT CARDS ===== */
        .stat-card {
            border: none;
            border-radius: 16px;
            padding: 20px;
            position: relative;
            overflow: hidden;
            height: 100%;
            transition: transform .2s ease, box-shadow .2s ease;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0, 0, 0, .12); }
        .stat-card::after {
            content: ''; position: absolute; right: -18px; top: -18px;
            width: 80px; height: 80px; border-radius: 50%; opacity: .12;
        }
        .stat-card.blue   { background: linear-gradient(135deg, #e8f0fe 0%, #dbeafe 100%); }
        .stat-card.blue::after   { background: #1a73e8; }
        .stat-card.green  { background: linear-gradient(135deg, #e6f9f0 0%, #d1fae5 100%); }
        .stat-card.green::after  { background: #16a34a; }
        .stat-card.orange { background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); }
        .stat-card.orange::after { background: #ea580c; }
        .stat-card.red    { background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); }
        .stat-card.red::after    { background: #dc2626; }
        .stat-card.purple { background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); }
        .stat-card.purple::after { background: #7c3aed; }
        .stat-card.teal   { background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%); }
        .stat-card.teal::after   { background: #0d9488; }

        .stat-icon {
            width: 48px; height: 48px; border-radius: 12px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.25rem; flex-shrink: 0;
        }
        .stat-icon.blue   { background: #1a73e8; color: #fff; }
        .stat-icon.green  { background: #16a34a; color: #fff; }
        .stat-icon.orange { background: #ea580c; color: #fff; }
        .stat-icon.red    { background: #dc2626; color: #fff; }
        .stat-icon.purple { background: #7c3aed; color: #fff; }
        .stat-icon.teal   { background: #0d9488; color: #fff; }

        .stat-value { font-size: 1.85rem; font-weight: 800; line-height: 1; color: #1e293b; }
        .stat-value.sm { font-size: 1.35rem; }
        .stat-label {
            font-size: .78rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: .5px; color: #64748b; margin-top: 3px;
        }
        .stat-note { font-size: .72rem; color: #94a3b8; margin-top: 2px; }

        /* ===== PAGE HEADER ===== */
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
        .ph-breadcrumb .bc-active { font-size: .78rem; color: #94a3b8; }

        /* ===== CARD ===== */
        .filter-card { border: none; border-radius: 16px; box-shadow: 0 2px 12px rgba(0, 0, 0, .07); overflow: hidden; height: 100%; }
        .filter-card .card-header { background: #fff; border-bottom: 1px solid #f1f5f9; padding: 18px 24px; }
        .filter-card .card-header h5 { font-size: .95rem; font-weight: 700; color: #1e293b; }
        .link-semua { font-size: .78rem; font-weight: 600; color: #1a73e8; text-decoration: none; }
        .link-semua:hover { text-decoration: underline; }

        /* ===== TABLE ===== */
        .table { font-size: .83rem; margin-bottom: 0; }
        .table thead th {
            background: #f8fafc; color: #475569; font-weight: 700;
            font-size: .72rem; text-transform: uppercase; letter-spacing: .4px;
            border-bottom: 1.5px solid #eef2f6; padding: 12px 16px; white-space: nowrap;
        }
        .table tbody td { padding: 12px 16px; vertical-align: middle; border-bottom: 1px solid #f4f6f8; }
        .table tbody tr:hover { background: #fafbfc; }

        .name-text { font-weight: 700; color: #1e293b; font-size: .85rem; }
        .sub-text { color: #94a3b8; font-size: .74rem; }

        /* ===== BADGES ===== */
        .badge-kode, .badge-kategori, .badge-kritis, .badge-aman,
        .badge-pending, .badge-disetujui, .badge-sebagian, .badge-ditolak, .badge-warn {
            font-weight: 600; font-size: .72rem; padding: 5px 10px; border-radius: 8px;
            display: inline-block; white-space: nowrap;
        }
        .badge-kode      { background: #eef2ff; color: #4338ca; }
        .badge-kategori  { background: #f0f9ff; color: #0369a1; }
        .badge-kritis    { background: #fef2f2; color: #dc2626; }
        .badge-aman      { background: #f0fdf4; color: #166534; }
        .badge-pending   { background: #fffbeb; color: #b45309; }
        .badge-disetujui { background: #f0fdf4; color: #166534; }
        .badge-sebagian  { background: #eff6ff; color: #1d4ed8; }
        .badge-ditolak   { background: #fef2f2; color: #dc2626; }
        .badge-warn      { background: #fff7ed; color: #c2410c; }

        /* ===== LIST ITEM (stok kritis, batch, pengalihan) ===== */
        .list-item {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 14px 24px; border-bottom: 1px solid #f4f6f8;
        }
        .list-item:last-child { border-bottom: none; }
        .list-item:hover { background: #fafbfc; }

        .stok-bar { height: 6px; border-radius: 99px; background: #f1f5f9; overflow: hidden; margin-top: 6px; width: 100%; min-width: 110px; }
        .stok-bar > span { display: block; height: 100%; border-radius: 99px; background: #dc2626; }

        /* ===== QUICK ACTION ===== */
        .btn-quick {
            border-radius: 10px; font-size: .8rem; font-weight: 600; padding: 7px 14px;
        }

        /* ===== EMPTY STATE ===== */
        .empty-state { padding: 40px 24px; text-align: center; }
        .empty-state-icon {
            width: 56px; height: 56px; border-radius: 16px; background: #f1f5f9; color: #94a3b8;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.35rem; margin: 0 auto 14px;
        }
        .empty-state-icon.ok { background: #f0fdf4; color: #16a34a; }
            /* ===== FILTER & FORM ===== */
        .filter-body { padding: 18px 24px; background: #fafbfc; border-bottom: 1px solid #f1f5f9; }
        .filter-body .form-control, .filter-body .form-select {
            border-radius: 10px; font-size: .83rem; border: 1px solid #e2e8f0; padding: 9px 12px;
        }
        .filter-body label { font-size: .72rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 4px; }
        .btn-detail { background: #eff6ff; color: #1d4ed8; border: none; border-radius: 8px; font-size: .75rem; font-weight: 600; padding: 6px 12px; }
        .btn-detail:hover { background: #dbeafe; color: #1e40af; }
        .info-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: #94a3b8; }
        .info-value { font-size: .9rem; font-weight: 600; color: #1e293b; }
        .total-box { background: #f8fafc; border-top: 1.5px solid #eef2f6; }
    </style>
@endsection

@section('content')
    @php
        $rupiah = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
        $angka  = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, ',', '.'), '0'), ',');

        $badgeAcc = fn ($s) => match ($s) {
            'disetujui' => ['badge-disetujui', 'Disetujui'],
            'ditolak'   => ['badge-ditolak', 'Ditolak'],
            default     => ['badge-pending', 'Menunggu'],
        };
        $badgeBayar = fn ($s) => $s === 'lunas'
            ? ['badge-disetujui', 'Lunas']
            : ['badge-warn', 'Belum Dibayar'];
    @endphp

    <div class="container">

        {{-- ── Page Header ── --}}
        <div class="ph-card mb-4">
            <div class="ph-left">
                <div class="ph-icon"><i class="fas fa-wallet"></i></div>
                <div>
                    <h5 class="ph-title">Pembayaran Saya</h5>
                    <ol class="ph-breadcrumb mb-0">
                        <li><a href="{{ route('produsen.dashboard') }}">Dashboard</a></li>
                        <li><span class="bc-active">Pembayaran Saya</span></li>
                    </ol>
                </div>
            </div>
        </div>

        {{-- ── Stat Cards ── --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card red">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon red"><i class="fas fa-wallet"></i></div>
                        <div>
                            <div class="stat-value sm">{{ $rupiah($totalBelumDibayar) }}</div>
                            <div class="stat-label">Belum Dibayar</div>
                            <div class="stat-note">tagihan ke Roti Baru</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card green">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                        <div>
                            <div class="stat-value sm">{{ $rupiah($totalLunas) }}</div>
                            <div class="stat-label">Sudah Lunas</div>
                            <div class="stat-note">total pembayaran diterima</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card orange">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon orange"><i class="fas fa-hourglass-half"></i></div>
                        <div>
                            <div class="stat-value">{{ $jumlahMenunggu }}</div>
                            <div class="stat-label">Menunggu Persetujuan</div>
                            <div class="stat-note">tagihan belum disetujui</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Daftar ── --}}
        <div class="card filter-card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="mb-0"><i class="fas fa-list text-primary me-2"></i>Daftar Pembayaran</h5>
                <span class="text-muted" style="font-size:.78rem;">{{ $pembayarans->total() }} data ditemukan</span>
            </div>

            <form method="GET" action="{{ route('produsen.pembayaran.index') }}" class="filter-body">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-3 col-md-6">
                        <label>Nomor Transaksi</label>
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                            placeholder="Cari nomor transaksi...">
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label>Persetujuan</label>
                        <select name="status_acc" class="form-select">
                            <option value="">Semua</option>
                            @foreach (['menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak'] as $v => $l)
                                <option value="{{ $v }}" @selected(request('status_acc') === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label>Pembayaran</label>
                        <select name="status_bayar" class="form-select">
                            <option value="">Semua</option>
                            <option value="belum" @selected(request('status_bayar') === 'belum')>Belum Dibayar</option>
                            <option value="lunas" @selected(request('status_bayar') === 'lunas')>Lunas</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label>Dari Tanggal</label>
                        <input type="date" name="dari" value="{{ request('dari') }}" class="form-control">
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label>Sampai</label>
                        <input type="date" name="sampai" value="{{ request('sampai') }}" class="form-control">
                    </div>
                    <div class="col-lg-1 col-md-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-quick" title="Cari"><i class="fas fa-filter"></i></button>
                        <a href="{{ route('produsen.pembayaran.index') }}" class="btn btn-outline-secondary btn-quick" title="Reset"><i class="fas fa-undo"></i></a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Transaksi</th>
                            <th class="text-end">Jumlah</th>
                            <th>Persetujuan</th>
                            <th>Pembayaran</th>
                            <th>Tanggal Bayar</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pembayarans as $bayar)
                            @php
                                [$kelasAcc, $labelAcc] = $badgeAcc($bayar->status_acc);
                                [$kelasBayar, $labelBayar] = $badgeBayar($bayar->status_bayar);
                            @endphp
                            <tr>
                                <td>{{ $pembayarans->firstItem() + $loop->index }}</td>
                                <td>
                                    <span class="badge-kode">{{ $bayar->permintaanBahanBaku->nomor_transaksi ?? '-' }}</span>
                                    <div class="sub-text mt-1">
                                        tagihan {{ $bayar->created_at?->format('d/m/Y') }}
                                    </div>
                                </td>
                                <td class="text-end fw-bold">{{ $rupiah($bayar->jumlah_bayar) }}</td>
                                <td><span class="{{ $kelasAcc }}">{{ $labelAcc }}</span></td>
                                <td><span class="{{ $kelasBayar }}">{{ $labelBayar }}</span></td>
                                <td>
                                    @if ($bayar->tanggal_bayar)
                                        {{ $bayar->tanggal_bayar->format('d/m/Y') }}
                                        @if ($bayar->metode_bayar)
                                            <div class="sub-text">{{ $bayar->metode_bayar }}</div>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('produsen.pembayaran.show', $bayar->id) }}" class="btn btn-detail">
                                        <i class="fas fa-eye me-1"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-0">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"><i class="fas fa-wallet"></i></div>
                                        <div class="fw-semibold text-secondary mb-1">Belum ada pembayaran</div>
                                        <div class="text-muted" style="font-size:.8rem;">
                                            Tagihan muncul setelah admin mencatat penerimaan barang dari Anda
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($pembayarans->hasPages())
                <div class="p-3 d-flex justify-content-end">
                    {{ $pembayarans->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>

    </div>
@endsection
