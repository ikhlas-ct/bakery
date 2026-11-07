@extends('layouts.user.user')

@section('title', 'Bahan Baku Mendekati Kedaluwarsa')

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
        .stat-card.yellow { background: linear-gradient(135deg, #fefce8 0%, #fef9c3 100%); }
        .stat-card.yellow::after { background: #ca8a04; }
        .stat-card.red    { background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); }
        .stat-card.red::after    { background: #dc2626; }

        .stat-icon {
            width: 48px; height: 48px; border-radius: 12px; display: inline-flex;
            align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;
        }
        .stat-icon.blue   { background: #1a73e8; color: #fff; }
        .stat-icon.green  { background: #16a34a; color: #fff; }
        .stat-icon.orange { background: #ea580c; color: #fff; }
        .stat-icon.yellow { background: #ca8a04; color: #fff; }
        .stat-icon.red    { background: #dc2626; color: #fff; }

        .stat-value { font-size: 1.6rem; font-weight: 800; line-height: 1.1; color: #1e293b; }
        .stat-value small { font-size: .8rem; font-weight: 600; color: #64748b; }
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
            width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center;
            justify-content: center; font-size: 1rem; flex-shrink: 0; background: #dbeafe; color: #1a73e8;
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
        .filter-section label { font-size: .72rem; font-weight: 600; color: #64748b; margin-bottom: 3px; }

        .form-control, .form-select {
            border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: .83rem;
            padding: 7px 12px; color: #334155; background-color: #f8fafc;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #1a73e8; background: #fff; box-shadow: 0 0 0 3px rgba(26, 115, 232, .12);
        }
        .form-control::placeholder { color: #94a3b8; }

        /* ===== TABLE ===== */
        .table { font-size: .83rem; margin-bottom: 0; }
        .table thead th {
            background: #f8fafc; color: #475569; font-weight: 700; font-size: .72rem;
            text-transform: uppercase; letter-spacing: .4px; border-bottom: 1.5px solid #eef2f6;
            padding: 12px 16px; white-space: nowrap;
        }
        .table tbody td { padding: 12px 16px; vertical-align: middle; border-bottom: 1px solid #f4f6f8; }
        .table tbody tr:hover { background: #fafbfc; }
        .table .num { text-align: right; white-space: nowrap; }

        .name-text { font-weight: 700; color: #1e293b; font-size: .85rem; }
        .sub-text { color: #94a3b8; font-size: .74rem; }

        /* ===== BADGES ===== */
        [class^="badge-"], [class*=" badge-"] {
            font-weight: 600; font-size: .72rem; padding: 5px 10px; border-radius: 8px;
            display: inline-block; white-space: nowrap;
        }
        .badge-kode      { background: #eef2ff; color: #4338ca; }
        .badge-kategori  { background: #f0f9ff; color: #0369a1; }
        .badge-aktif, .badge-aman { background: #f0fdf4; color: #166534; }
        .badge-nonaktif  { background: #f1f5f9; color: #64748b; }
        .badge-kritis    { background: #fef2f2; color: #dc2626; }
        .badge-habis     { background: #1e293b; color: #fff; }
        .badge-segera    { background: #fefce8; color: #a16207; }
        .badge-fefo      { background: #dbeafe; color: #1d4ed8; }
        .badge-masuk     { background: #f0fdf4; color: #166534; }
        .badge-keluar    { background: #fff7ed; color: #c2410c; }

        .txt-masuk  { color: #16a34a; font-weight: 700; }
        .txt-keluar { color: #ea580c; font-weight: 700; }

        /* ===== ACTION BUTTONS ===== */
        .btn-action {
            width: 32px; height: 32px; border-radius: 8px; border: none; display: inline-flex;
            align-items: center; justify-content: center; font-size: .78rem; transition: all .15s;
        }
        .btn-detail { background: #eff6ff; color: #1d4ed8; }
        .btn-detail:hover { background: #1d4ed8; color: #fff; }
        .btn-detail i { transition: transform .2s; }
        .btn-detail.open i { transform: rotate(180deg); }
        .btn-kartu { background: #f0fdf4; color: #166534; }
        .btn-kartu:hover { background: #166534; color: #fff; }

        /* ===== PANEL BATCH FEFO ===== */
        .batch-panel { background: #f8fafc; padding: 16px 24px 20px; border-bottom: 1px solid #eef2f6; }
        .batch-title { font-size: .8rem; font-weight: 700; color: #1e293b; margin-bottom: 10px; }
        .batch-panel .table { background: #fff; border-radius: 10px; overflow: hidden; }
        .batch-panel .table thead th { background: #f1f5f9; }
        .batch-note { font-size: .75rem; color: #a16207; background: #fefce8; border-radius: 8px; padding: 8px 12px; margin-top: 10px; }

        /* ===== LEDGER ===== */
        .row-saldo td { background: #f8fafc; font-weight: 700; color: #334155; }
        .row-total td { background: #f1f5f9; font-weight: 800; color: #1e293b; }

        /* ===== EMPTY STATE ===== */
        .empty-state { padding: 48px 24px; }
        .empty-state-icon {
            width: 64px; height: 64px; border-radius: 16px; background: #f1f5f9; color: #94a3b8;
            display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 16px;
        }

        /* ===== ALERT ===== */
        .alert { border-radius: 12px; border: none; font-size: .85rem; }
        .alert-success { background: #dcfce7; color: #166534; }
        .alert-danger { background: #fee2e2; color: #991b1b; }
        .alert-warning { background: #fef9c3; color: #854d0e; }

        /* ===== LAPORAN KEDALUWARSA ===== */
        .badge-mendesak { background: #fff7ed; color: #c2410c; }
        .row-kadaluarsa td { background: #fef2f2; }
        .row-kadaluarsa:hover td { background: #fee2e2; }
    </style>
@endsection

@section('content')
    @php
        $dashboardRoute = auth()->user()->role === 'admin' ? route('admin.dashboard') : route('pemilik.dashboard');
        $qty = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');
        $satuanOf = fn ($b) => mb_strtolower($b->satuan?->kode_satuan ?: ($b->satuan?->nama ?? ''));
        $tgl = fn ($d) => $d ? $d->format('d/m/Y') : '-';
        $batasMendesak = \App\Services\StokService::BATAS_MENDESAK;
        $filterAktif = request()->hasAny(['search', 'kategori_barang_id', 'hari', 'tampil']);
    @endphp
    <div class="container">

        {{-- ── Page Header ── --}}
        <div class="ph-card mb-4">
            <div class="ph-left">
                <div class="ph-icon"><i class="fas fa-hourglass-half"></i></div>
                <div>
                    <h5 class="ph-title">Bahan Baku Mendekati Kedaluwarsa</h5>
                    <ol class="ph-breadcrumb mb-0">
                        <li><a href="{{ $dashboardRoute }}">Dashboard</a></li>
                        <li><span class="bc-active">Laporan Kedaluwarsa</span></li>
                    </ol>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('laporan.stok.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-warehouse me-1"></i> Laporan Stok
                </a>
                <a href="{{ route('laporan.kedaluwarsa.cetak', request()->except('page')) }}" target="_blank"
                    class="btn btn-success btn-sm">
                    <i class="fas fa-print me-1"></i> Cetak Laporan
                </a>
            </div>
        </div>

        {{-- ── Peringatan batch yang sudah kedaluwarsa ── --}}
        @if ($ringkasan['kadaluarsa'] > 0)
            <div class="alert alert-danger d-flex align-items-start mb-3 gap-2">
                <i class="fas fa-triangle-exclamation mt-1"></i>
                <div>
                    Ada <strong>{{ $ringkasan['kadaluarsa'] }} batch</strong> yang sudah kedaluwarsa tetapi masih tercatat
                    bersisa di stok. Periksa fisiknya dan jangan digunakan untuk produksi.
                </div>
            </div>
        @endif

        {{-- ── Stat Cards ── --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card blue">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon blue"><i class="fas fa-boxes-stacked"></i></div>
                        <div>
                            <div class="stat-value">{{ $ringkasan['bahan'] }}</div>
                            <div class="stat-label">Bahan Baku Terdampak</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card red">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon red"><i class="fas fa-ban"></i></div>
                        <div>
                            <div class="stat-value">{{ $ringkasan['kadaluarsa'] }}</div>
                            <div class="stat-label">Batch Kedaluwarsa</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card orange">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon orange"><i class="fas fa-bolt"></i></div>
                        <div>
                            <div class="stat-value">{{ $ringkasan['mendesak'] }}</div>
                            <div class="stat-label">Mendesak (≤ {{ $batasMendesak }} Hari)</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card yellow">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon yellow"><i class="fas fa-hourglass-half"></i></div>
                        <div>
                            <div class="stat-value">{{ $ringkasan['mendekati'] }}</div>
                            <div class="stat-label">Mendekati (≤ {{ $hari }} Hari)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Main Card ── --}}
        <div class="card filter-card">

            {{-- Header --}}
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="mb-0"><i class="fas fa-list text-primary me-2"></i>Daftar Batch
                    <span class="text-muted fw-normal" style="font-size:.78rem;">— posisi {{ today()->format('d/m/Y') }}</span>
                </h5>
                <span class="text-muted" style="font-size:.78rem;">{{ $items->total() }} batch ditemukan</span>
            </div>

            {{-- Filter --}}
            <div class="filter-section">
                <form method="GET" action="{{ route('laporan.kedaluwarsa.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-3 col-sm-6">
                        <label>Cari</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text border-end-0 bg-white"
                                style="border-radius:10px 0 0 10px;border:1.5px solid #e2e8f0;">
                                <i class="fas fa-search text-muted" style="font-size:.75rem;"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0"
                                style="border-radius:0 10px 10px 0;" placeholder="Kode atau nama…"
                                value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label>Kategori</label>
                        <select name="kategori_barang_id" class="form-select form-select-sm">
                            <option value="">Semua Kategori</option>
                            @foreach ($kategoris as $kategori)
                                <option value="{{ $kategori->id }}" @selected(request('kategori_barang_id') == $kategori->id)>
                                    {{ $kategori->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label>Kedaluwarsa Dalam</label>
                        <select name="hari" class="form-select form-select-sm">
                            @foreach ($pilihanHari as $h)
                                <option value="{{ $h }}" @selected($hari === $h)>≤ {{ $h }} hari</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label>Tampilkan</label>
                        <select name="tampil" class="form-select form-select-sm">
                            @foreach ($pilihanTampil as $key => $label)
                                <option value="{{ $key }}" @selected($tampil === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-auto d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-3">
                            <i class="fas fa-filter me-1"></i> Tampilkan
                        </button>
                        @if ($filterAktif)
                            <a href="{{ route('laporan.kedaluwarsa.index') }}" class="btn btn-outline-secondary btn-sm px-3">
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
                            <th>Bahan Baku</th>
                            <th>Batch Penerimaan</th>
                            <th>Tgl Terima</th>
                            <th>Kedaluwarsa</th>
                            <th>Sisa Hari</th>
                            <th class="num">Sisa Stok</th>
                            <th>Status</th>
                            <th width="60">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $i => $r)
                            @php
                                $b = $r->bahan;
                                $bt = $r->batch;
                                $sat = $satuanOf($b);
                            @endphp
                            <tr class="{{ $r->level === 'kadaluarsa' ? 'row-kadaluarsa' : '' }}">
                                <td class="text-muted">{{ $items->firstItem() + $i }}</td>
                                <td>
                                    <div class="name-text">{{ $b->nama_bahan_baku }}</div>
                                    <span class="badge-kode">{{ $b->kode_bahan_baku }}</span>
                                    <span class="badge-kategori">{{ $b->kategoriBarang->nama_kategori ?? '-' }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $bt->nomor_penerimaan }}</div>
                                    <div class="sub-text">{{ $bt->produsen }}</div>
                                    @if ($bt->urutan === 1)
                                        <span class="badge-fefo">Dipakai pertama (FEFO)</span>
                                    @endif
                                </td>
                                <td>{{ $tgl($bt->tanggal_terima) }}</td>
                                <td class="fw-semibold">{{ $tgl($bt->tanggal_kadaluarsa) }}</td>
                                <td>
                                    @if ($bt->sisa_hari < 0)
                                        <span class="text-danger fw-semibold">{{ abs($bt->sisa_hari) }} hari lewat</span>
                                    @elseif ($bt->sisa_hari === 0)
                                        <span class="text-danger fw-semibold">Hari ini</span>
                                    @else
                                        <span class="{{ $r->level === 'mendesak' ? 'text-danger fw-semibold' : '' }}">
                                            {{ $bt->sisa_hari }} hari lagi
                                        </span>
                                    @endif
                                </td>
                                <td class="num fw-bold">{{ $qty($bt->sisa) }} {{ $sat }}</td>
                                <td>
                                    @if ($r->level === 'kadaluarsa')
                                        <span class="badge-kritis">Sudah kedaluwarsa</span>
                                    @elseif ($r->level === 'mendesak')
                                        <span class="badge-mendesak">Mendesak</span>
                                    @else
                                        <span class="badge-segera">Mendekati</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('laporan.kartu-stok.index', ['bahan_baku_id' => $b->id]) }}"
                                        class="btn btn-action btn-kartu" title="Kartu stok">
                                        <i class="fas fa-book"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-0 text-center">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"><i class="fas fa-circle-check"></i></div>
                                        <div class="fw-semibold text-secondary mb-1">
                                            Tidak ada bahan baku yang mendekati kedaluwarsa
                                        </div>
                                        <div class="text-muted" style="font-size:.8rem;">
                                            Coba perluas batas hari atau ubah filter
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($items->hasPages())
                <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">
                        Menampilkan
                        <strong>{{ $items->firstItem() }}</strong>–<strong>{{ $items->lastItem() }}</strong>
                        dari <strong>{{ $items->total() }}</strong> data
                    </small>
                    {{ $items->links() }}
                </div>
            @endif

        </div>{{-- end .card --}}

    </div>
@endsection
