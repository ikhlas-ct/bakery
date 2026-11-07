@extends('layouts.user.user')

@section('title', 'Laporan Stok Bahan Baku')

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
    </style>
@endsection

@section('content')
    @php
        $dashboardRoute = auth()->user()->role === 'admin' ? route('admin.dashboard') : route('pemilik.dashboard');
        $qty = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');
        $satuanOf = fn ($b) => mb_strtolower($b->satuan?->kode_satuan ?: ($b->satuan?->nama ?? ''));
        $tgl = fn ($d) => $d ? $d->format('d/m/Y') : '-';
        $filterAktif = request()->hasAny(['search', 'kategori_barang_id', 'status', 'stok']) || request()->filled('tanggal');
    @endphp
    <div class="container">

        {{-- ── Page Header ── --}}
        <div class="ph-card mb-4">
            <div class="ph-left">
                <div class="ph-icon"><i class="fas fa-clipboard-list"></i></div>
                <div>
                    <h5 class="ph-title">Laporan Stok Bahan Baku</h5>
                    <ol class="ph-breadcrumb mb-0">
                        <li><a href="{{ $dashboardRoute }}">Dashboard</a></li>
                        <li><span class="bc-active">Laporan Stok</span></li>
                    </ol>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('laporan.kartu-stok.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-book me-1"></i> Kartu Stok
                </a>
                <a href="{{ route('laporan.stok.cetak', request()->except('page')) }}" target="_blank"
                    class="btn btn-success btn-sm">
                    <i class="fas fa-print me-1"></i> Cetak Laporan
                </a>
            </div>
        </div>

        {{-- ── Peringatan data tidak sinkron ── --}}
        @if ($ringkasan['tidak_sinkron'] > 0)
            <div class="alert alert-warning d-flex align-items-start mb-3 gap-2">
                <i class="fas fa-triangle-exclamation mt-1"></i>
                <div>
                    <strong>{{ $ringkasan['tidak_sinkron'] }} bahan baku</strong> memiliki nilai <em>stok saat ini</em>
                    yang berbeda dengan hasil hitung (total penerimaan − total pemakaian). Laporan ini memakai hasil hitung
                    dari riwayat transaksi. Cek kolom <em>Stok</em> yang bertanda "Cache".
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
                            <div class="stat-value">{{ $ringkasan['total'] }}</div>
                            <div class="stat-label">Bahan Baku</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card orange">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon orange"><i class="fas fa-triangle-exclamation"></i></div>
                        <div>
                            <div class="stat-value">{{ $ringkasan['kritis'] }}</div>
                            <div class="stat-label">Stok Kritis / Habis</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card yellow">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon yellow"><i class="fas fa-hourglass-half"></i></div>
                        <div>
                            <div class="stat-value">{{ $ringkasan['segera'] }}</div>
                            <div class="stat-label">Batch ≤ {{ \App\Services\StokService::BATAS_SEGERA_KADALUARSA }} Hari</div>
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
                            <div class="stat-label">Batch Kadaluarsa</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Main Card ── --}}
        <div class="card filter-card">

            {{-- Header --}}
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="mb-0"><i class="fas fa-list text-primary me-2"></i>Stok per Bahan Baku
                    <span class="text-muted fw-normal" style="font-size:.78rem;">— posisi {{ $per->format('d/m/Y') }}</span>
                </h5>
                <span class="text-muted" style="font-size:.78rem;">{{ $bahanBakus->total() }} data ditemukan</span>
            </div>

            {{-- Filter --}}
            <div class="filter-section">
                <form method="GET" action="{{ route('laporan.stok.index') }}" class="row g-2 align-items-end">
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
                    <div class="col-md-2 col-sm-6">
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
                        <label>Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua Status</option>
                            <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                            <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label>Kondisi Stok</label>
                        <select name="stok" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            <option value="kritis" @selected(request('stok') === 'kritis')>Kritis / habis</option>
                            <option value="habis" @selected(request('stok') === 'habis')>Habis</option>
                            <option value="expiring" @selected(request('stok') === 'expiring')>Ada batch segera kadaluarsa</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label>Posisi per Tanggal</label>
                        <input type="date" name="tanggal" class="form-control form-control-sm"
                            max="{{ today()->toDateString() }}" value="{{ $per->toDateString() }}">
                    </div>
                    <div class="col-md-auto d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-3">
                            <i class="fas fa-filter me-1"></i> Tampilkan
                        </button>
                        @if ($filterAktif)
                            <a href="{{ route('laporan.stok.index') }}" class="btn btn-outline-secondary btn-sm px-3">
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
                            <th>Kategori</th>
                            <th class="num">Stok</th>
                            <th class="num">Minimum</th>
                            <th>Kondisi</th>
                            <th>Batch (FEFO)</th>
                            <th width="90">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bahanBakus as $i => $r)
                            @php
                                $b = $r->bahan;
                                $sat = $satuanOf($b);
                            @endphp
                            <tr>
                                <td class="text-muted">{{ $bahanBakus->firstItem() + $i }}</td>
                                <td>
                                    <div class="name-text">{{ $b->nama_bahan_baku }}</div>
                                    <span class="badge-kode">{{ $b->kode_bahan_baku }}</span>
                                    @unless ($b->is_aktif)
                                        <span class="badge-nonaktif">Nonaktif</span>
                                    @endunless
                                </td>
                                <td><span class="badge-kategori">{{ $b->kategoriBarang->nama_kategori ?? '-' }}</span></td>
                                <td class="num">
                                    <div class="{{ in_array($r->status_stok, ['kritis', 'habis']) ? 'text-danger fw-bold' : 'fw-semibold' }}">
                                        {{ $qty($r->stok) }} {{ $sat }}
                                    </div>
                                    @if (abs($r->selisih_cache) > 0.004)
                                        <div class="sub-text text-danger" title="Nilai kolom stok_saat_ini di data bahan baku">
                                            Cache: {{ $qty($b->stok_saat_ini) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="num">{{ $qty($r->minimum) }} {{ $sat }}</td>
                                <td>
                                    @if ($r->status_stok === 'habis')
                                        <span class="badge-habis">Habis</span>
                                    @elseif ($r->status_stok === 'kritis')
                                        <span class="badge-kritis">Kritis</span>
                                    @else
                                        <span class="badge-aman">Aman</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($r->jumlah_batch === 0)
                                        <span class="sub-text">Tidak ada batch</span>
                                    @else
                                        <div class="fw-semibold">{{ $r->jumlah_batch }} batch</div>
                                        <div class="sub-text">
                                            Kadaluarsa terdekat: {{ $tgl($r->exp_terdekat) }}
                                        </div>
                                        @if ($r->batch_kadaluarsa > 0)
                                            <span class="badge-kritis">{{ $r->batch_kadaluarsa }} kadaluarsa</span>
                                        @endif
                                        @if ($r->batch_segera > 0)
                                            <span class="badge-segera">{{ $r->batch_segera }} segera</span>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-action btn-detail btn-toggle-batch"
                                            data-target="batch-{{ $b->id }}" title="Lihat batch penerimaan">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                        <a href="{{ route('laporan.kartu-stok.index', ['bahan_baku_id' => $b->id]) }}"
                                            class="btn btn-action btn-kartu" title="Kartu stok">
                                            <i class="fas fa-book"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            {{-- Rincian batch FEFO --}}
                            <tr id="batch-{{ $b->id }}" class="d-none">
                                <td colspan="8" class="p-0">
                                    <div class="batch-panel">
                                        <div class="batch-title">
                                            <i class="fas fa-layer-group me-2 text-primary"></i>
                                            Batch dari penerimaan bahan baku — urutan FEFO
                                            <span class="text-muted fw-normal">(kadaluarsa terdekat dipakai lebih dulu)</span>
                                        </div>

                                        @if ($r->batches->isEmpty())
                                            <div class="text-muted" style="font-size:.8rem;">
                                                Tidak ada batch yang masih bersisa pada tanggal {{ $per->format('d/m/Y') }}.
                                            </div>
                                        @else
                                            <div class="table-responsive">
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th>FEFO</th>
                                                            <th>No. Penerimaan</th>
                                                            <th>Produsen</th>
                                                            <th>Tgl Terima</th>
                                                            <th>Kadaluarsa</th>
                                                            <th>Sisa Hari</th>
                                                            <th class="num">Diterima</th>
                                                            <th class="num">Terpakai</th>
                                                            <th class="num">Sisa</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($r->batches as $bt)
                                                            <tr>
                                                                <td>
                                                                    @if ($bt->urutan === 1)
                                                                        <span class="badge-fefo">Dipakai pertama</span>
                                                                    @else
                                                                        <span class="text-muted">#{{ $bt->urutan }}</span>
                                                                    @endif
                                                                </td>
                                                                <td class="fw-semibold">{{ $bt->nomor_penerimaan }}</td>
                                                                <td>{{ $bt->produsen }}</td>
                                                                <td>{{ $tgl($bt->tanggal_terima) }}</td>
                                                                <td>{{ $tgl($bt->tanggal_kadaluarsa) }}</td>
                                                                <td>
                                                                    @if ($bt->sisa_hari === null)
                                                                        -
                                                                    @elseif ($bt->sisa_hari < 0)
                                                                        <span class="text-danger">{{ abs($bt->sisa_hari) }} hari lewat</span>
                                                                    @elseif ($bt->sisa_hari === 0)
                                                                        <span class="text-danger">Hari ini</span>
                                                                    @else
                                                                        {{ $bt->sisa_hari }} hari
                                                                    @endif
                                                                </td>
                                                                <td class="num">{{ $qty($bt->diterima) }} {{ $sat }}</td>
                                                                <td class="num">{{ $qty($bt->dipakai) }} {{ $sat }}</td>
                                                                <td class="num fw-bold">{{ $qty($bt->sisa) }} {{ $sat }}</td>
                                                                <td>
                                                                    @switch($bt->status)
                                                                        @case('kadaluarsa')
                                                                            <span class="badge-kritis">Kadaluarsa</span>
                                                                        @break
                                                                        @case('segera')
                                                                            <span class="badge-segera">Segera kadaluarsa</span>
                                                                        @break
                                                                        @case('aman')
                                                                            <span class="badge-aman">Aman</span>
                                                                        @break
                                                                        @default
                                                                            <span class="badge-nonaktif">Tanpa kadaluarsa</span>
                                                                    @endswitch
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif

                                        @if (abs($r->selisih_batch) > 0.004)
                                            <div class="batch-note">
                                                <i class="fas fa-circle-info me-1"></i>
                                                Total sisa batch ({{ $qty($r->stok - $r->selisih_batch) }} {{ $sat }})
                                                berbeda {{ $qty(abs($r->selisih_batch)) }} {{ $sat }} dari stok
                                                ({{ $qty($r->stok) }} {{ $sat }}). Kemungkinan ada pemakaian yang tidak
                                                dikaitkan ke batch penerimaan tertentu.
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-0 text-center">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"><i class="fas fa-boxes-stacked"></i></div>
                                        <div class="fw-semibold text-secondary mb-1">Tidak ada data stok</div>
                                        <div class="text-muted" style="font-size:.8rem;">
                                            Coba ubah filter atau tanggal posisi stok
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($bahanBakus->hasPages())
                <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">
                        Menampilkan
                        <strong>{{ $bahanBakus->firstItem() }}</strong>–<strong>{{ $bahanBakus->lastItem() }}</strong>
                        dari <strong>{{ $bahanBakus->total() }}</strong> data
                    </small>
                    {{ $bahanBakus->links() }}
                </div>
            @endif

        </div>{{-- end .card --}}

    </div>
@endsection

@section('scripts')
    <script>
        document.querySelectorAll('.btn-toggle-batch').forEach(btn => {
            btn.addEventListener('click', function () {
                const row = document.getElementById(this.dataset.target);
                if (!row) return;
                row.classList.toggle('d-none');
                this.classList.toggle('open', !row.classList.contains('d-none'));
            });
        });
    </script>
@endsection
