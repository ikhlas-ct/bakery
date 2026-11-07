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
            border: none; border-radius: 16px; padding: 20px; position: relative; overflow: hidden;
            transition: transform .2s ease, box-shadow .2s ease; box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
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

        .stat-icon {
            width: 48px; height: 48px; border-radius: 12px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.25rem; flex-shrink: 0;
        }
        .stat-icon.blue   { background: #1a73e8; color: #fff; }
        .stat-icon.green  { background: #16a34a; color: #fff; }
        .stat-icon.orange { background: #ea580c; color: #fff; }

        .stat-value { font-size: 1.5rem; font-weight: 800; line-height: 1.1; color: #1e293b; }
        .stat-label {
            font-size: .78rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: .5px; color: #64748b; margin-top: 3px;
        }

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

        /* ===== FILTER & TABLE CARD ===== */
        .filter-card { border: none; border-radius: 16px; box-shadow: 0 2px 12px rgba(0, 0, 0, .07); overflow: hidden; }
        .filter-card .card-header { background: #fff; border-bottom: 1px solid #f1f5f9; padding: 18px 24px; }
        .filter-card .card-header h5 { font-size: .95rem; font-weight: 700; color: #1e293b; }
        .filter-section { background: #fafbfc; border-bottom: 1px solid #f1f5f9; padding: 16px 24px; }

        .form-control, .form-select {
            border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: .83rem;
            padding: 7px 12px; color: #334155; background-color: #f8fafc;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #1a73e8; background: #fff; box-shadow: 0 0 0 3px rgba(26, 115, 232, .12);
        }

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

        .badge-kode {
            background: #eef2ff; color: #4338ca; font-weight: 600;
            font-size: .72rem; padding: 5px 10px; border-radius: 8px;
        }
        .badge-lunas, .badge-belum, .badge-ditolak, .badge-metode {
            font-weight: 600; font-size: .72rem; padding: 5px 10px; border-radius: 8px; display: inline-block;
        }
        .badge-lunas { background: #f0fdf4; color: #166534; }
        .badge-belum { background: #fef3c7; color: #92400e; }
        .badge-ditolak { background: #fef2f2; color: #dc2626; }
        .badge-metode { background: #f0f9ff; color: #0369a1; }

        .bukti-thumb {
            width: 40px; height: 40px; object-fit: cover; border-radius: 8px;
            border: 1.5px solid #e2e8f0; background: #f8fafc; display: block;
        }

        .btn-action {
            width: 32px; height: 32px; border-radius: 8px; border: none;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: .78rem; transition: all .15s;
        }
        .btn-detail { background: #eff6ff; color: #1a73e8; }
        .btn-detail:hover { background: #1a73e8; color: #fff; }

        /* ===== EMPTY STATE ===== */
        .empty-state { padding: 48px 24px; }
        .empty-state-icon {
            width: 64px; height: 64px; border-radius: 16px; background: #f1f5f9; color: #94a3b8;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; margin: 0 auto 16px;
        }

        /* ===== ALERT ===== */
        .alert { border-radius: 12px; border: none; font-size: .85rem; }
        .alert-success { background: #dcfce7; color: #166534; }
        .alert-danger { background: #fee2e2; color: #991b1b; }
    </style>
@endsection

@section('content')
    @php
        $rupiah = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
        $adaFilter = request()->hasAny(['status_bayar', 'status_acc', 'metode_bayar', 'dari', 'sampai']);
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

        {{-- ── Flash Messages ── --}}
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

        {{-- ── Stat Cards ── --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="stat-card orange">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon orange"><i class="fas fa-hourglass-half"></i></div>
                        <div>
                            <div class="stat-value">{{ $rupiah($totalBelumDibayar) }}</div>
                            <div class="stat-label">Total Belum Dibayar</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="stat-card green">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                        <div>
                            <div class="stat-value">{{ $rupiah($totalLunas) }}</div>
                            <div class="stat-label">Total Lunas</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="stat-card blue">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon blue"><i class="fas fa-user-clock"></i></div>
                        <div>
                            <div class="stat-value">{{ $menungguPersetujuan }}</div>
                            <div class="stat-label">Menunggu Persetujuan</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Main Card ── --}}
        <div class="card filter-card">

            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="mb-0"><i class="fas fa-list text-primary me-2"></i>Daftar Pembayaran</h5>
                <span class="text-muted" style="font-size:.78rem;">{{ $pembayarans->total() }} data ditemukan</span>
            </div>

            {{-- Filter --}}
            <div class="filter-section">
                <form method="GET" action="{{ route('produsen.pembayaran.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-2 col-sm-6">
                        <select name="status_bayar" class="form-select form-select-sm">
                            <option value="">Semua Status Bayar</option>
                            <option value="belum" @selected(request('status_bayar') === 'belum')>Belum dibayar</option>
                            <option value="lunas" @selected(request('status_bayar') === 'lunas')>Lunas</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <select name="status_acc" class="form-select form-select-sm">
                            <option value="">Semua Status ACC</option>
                            <option value="menunggu" @selected(request('status_acc') === 'menunggu')>Menunggu</option>
                            <option value="disetujui" @selected(request('status_acc') === 'disetujui')>Disetujui</option>
                            <option value="ditolak" @selected(request('status_acc') === 'ditolak')>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <select name="metode_bayar" class="form-select form-select-sm">
                            <option value="">Semua Metode</option>
                            <option value="Tunai" @selected(request('metode_bayar') === 'Tunai')>Tunai</option>
                            <option value="Transfer" @selected(request('metode_bayar') === 'Transfer')>Transfer</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <input type="date" name="dari" class="form-control form-control-sm"
                            value="{{ request('dari') }}" title="Dari tanggal (tanggal bayar / tanggal tagihan)">
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <input type="date" name="sampai" class="form-control form-control-sm"
                            value="{{ request('sampai') }}" title="Sampai tanggal (tanggal bayar / tanggal tagihan)">
                    </div>
                    <div class="col-md-auto d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-3">
                            <i class="fas fa-filter me-1"></i> Cari
                        </button>
                        @if ($adaFilter)
                            <a href="{{ route('produsen.pembayaran.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                                <i class="fas fa-times me-1"></i> Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="40">#</th>
                            <th>No. Permintaan</th>
                            <th class="text-end">Jumlah</th>
                            <th>Metode</th>
                            <th>Status</th>
                            <th>Tanggal Bayar</th>
                            <th>Bukti</th>
                            <th width="70">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pembayarans as $p)
                            <tr>
                                <td class="text-muted">{{ $pembayarans->firstItem() + $loop->index }}</td>
                                <td>
                                    <span class="badge-kode">{{ $p->permintaanBahanBaku->nomor_transaksi ?? '-' }}</span>
                                    @if ($p->permintaanBahanBaku?->tanggal_permintaan)
                                        <div class="sub-text mt-1">{{ \Carbon\Carbon::parse($p->permintaanBahanBaku->tanggal_permintaan)->translatedFormat('d M Y') }}</div>
                                    @endif
                                </td>
                                <td class="text-end name-text">{{ $rupiah($p->jumlah_bayar) }}</td>
                                <td>
                                    @if ($p->metode_bayar)
                                        <span class="badge-metode">{{ $p->metode_bayar }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <span class="{{ $p->status_bayar === 'lunas' ? 'badge-lunas' : 'badge-belum' }}">
                                            {{ $p->status_bayar === 'lunas' ? 'Lunas' : 'Belum dibayar' }}
                                        </span>
                                        <span class="{{ $p->status_acc === 'disetujui' ? 'badge-lunas' : ($p->status_acc === 'ditolak' ? 'badge-ditolak' : 'badge-belum') }}">
                                            ACC: {{ ucfirst($p->status_acc) }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    @if ($p->tanggal_bayar)
                                        {{ $p->tanggal_bayar->translatedFormat('d M Y') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($p->bukti_bayar)
                                        <a href="{{ asset('storage/' . $p->bukti_bayar) }}" target="_blank" rel="noopener" title="Lihat bukti pembayaran">
                                            <img src="{{ asset('storage/' . $p->bukti_bayar) }}" alt="Bukti pembayaran" class="bukti-thumb">
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('produsen.pembayaran.show', $p->id) }}" class="btn-action btn-detail" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-0 text-center">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"><i class="fas fa-wallet"></i></div>
                                        <div class="fw-semibold text-secondary mb-1">Belum ada data pembayaran</div>
                                        <div class="text-muted" style="font-size:.8rem;">
                                            {{ $adaFilter ? 'Coba ubah filter pencarian' : 'Tagihan akan muncul setelah admin mencatat penerimaan barang dari Anda' }}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($pembayarans->hasPages())
                <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">
                        Menampilkan
                        <strong>{{ $pembayarans->firstItem() }}</strong>–<strong>{{ $pembayarans->lastItem() }}</strong>
                        dari <strong>{{ $pembayarans->total() }}</strong> data
                    </small>
                    {{ $pembayarans->links() }}
                </div>
            @endif

        </div>

    </div>
@endsection
