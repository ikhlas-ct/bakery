@extends('layouts.user.user')

@section('title', 'Kartu Stok Bahan Baku')

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
        $tgl = fn ($d) => $d ? $d->format('d/m/Y') : '-';
        $sat = $bahan ? mb_strtolower($bahan->satuan?->kode_satuan ?: ($bahan->satuan?->nama ?? '')) : '';
    @endphp
    <div class="container">

        {{-- ── Page Header ── --}}
        <div class="ph-card mb-4">
            <div class="ph-left">
                <div class="ph-icon"><i class="fas fa-book"></i></div>
                <div>
                    <h5 class="ph-title">Kartu Stok Bahan Baku</h5>
                    <ol class="ph-breadcrumb mb-0">
                        <li><a href="{{ $dashboardRoute }}">Dashboard</a></li>
                        <li><a href="{{ route('laporan.stok.index') }}">Laporan Stok</a></li>
                        <li><span class="bc-active">Kartu Stok</span></li>
                    </ol>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('laporan.stok.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Laporan Stok
                </a>
                @if ($bahan)
                    <a href="{{ route('laporan.kartu-stok.cetak', request()->except('page')) }}" target="_blank"
                        class="btn btn-success btn-sm">
                        <i class="fas fa-print me-1"></i> Cetak Kartu Stok
                    </a>
                @endif
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger d-flex align-items-center mb-3 gap-2">
                <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
            </div>
        @endif

        {{-- ── Filter ── --}}
        <div class="card filter-card mb-4">
            <div class="filter-section" style="border-bottom:0;">
                <form method="GET" action="{{ route('laporan.kartu-stok.index') }}" id="form-kartu"
                    class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label>Bahan Baku <span class="text-danger">*</span></label>
                        <select name="bahan_baku_id" class="form-select form-select-sm" required>
                            <option value="">-- Pilih Bahan Baku --</option>
                            @foreach ($daftarBahan as $item)
                                <option value="{{ $item->id }}" @selected(request('bahan_baku_id') == $item->id)>
                                    {{ $item->kode_bahan_baku }} — {{ $item->nama_bahan_baku }}{{ $item->is_aktif ? '' : ' (nonaktif)' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label>Dari Tanggal</label>
                        <input type="date" name="dari" id="input-dari" class="form-control form-control-sm"
                            value="{{ $dari->toDateString() }}">
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label>Sampai Tanggal</label>
                        <input type="date" name="sampai" id="input-sampai" class="form-control form-control-sm"
                            value="{{ $sampai->toDateString() }}">
                    </div>
                    <div class="col-md-auto d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-3">
                            <i class="fas fa-filter me-1"></i> Tampilkan
                        </button>
                    </div>
                    <div class="col-12 d-flex gap-2 flex-wrap pt-1">
                        <span class="sub-text align-self-center">Pintasan periode:</span>
                        <button type="button" class="btn btn-outline-secondary btn-sm preset" data-preset="bulan-ini">Bulan ini</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm preset" data-preset="bulan-lalu">Bulan lalu</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm preset" data-preset="tahun-ini">Tahun ini</button>
                    </div>
                </form>
            </div>
        </div>

        @if (! $bahan)
            <div class="card filter-card">
                <div class="empty-state text-center">
                    <div class="empty-state-icon"><i class="fas fa-book"></i></div>
                    <div class="fw-semibold text-secondary mb-1">Pilih bahan baku untuk melihat kartu stok</div>
                    <div class="text-muted" style="font-size:.8rem;">
                        Kartu stok menampilkan setiap barang masuk (penerimaan) dan keluar (pemakaian) beserta saldonya.
                    </div>
                </div>
            </div>
        @else
            {{-- ── Info bahan ── --}}
            <div class="ph-card mb-4">
                <div class="ph-left">
                    <div class="ph-icon"><i class="fas fa-boxes-stacked"></i></div>
                    <div>
                        <h5 class="ph-title">{{ $bahan->nama_bahan_baku }}</h5>
                        <div class="d-flex gap-2 flex-wrap mt-1">
                            <span class="badge-kode">{{ $bahan->kode_bahan_baku }}</span>
                            <span class="badge-kategori">{{ $bahan->kategoriBarang->nama_kategori ?? '-' }}</span>
                            <span class="sub-text align-self-center">
                                Satuan: {{ $bahan->satuan->nama ?? '-' }} · Stok minimum: {{ $qty($bahan->stok_minimum) }} {{ $sat }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="text-end">
                    <div class="sub-text">Periode</div>
                    <div class="fw-bold" style="color:#1e293b;">{{ $dari->format('d/m/Y') }} – {{ $sampai->format('d/m/Y') }}</div>
                </div>
            </div>

            {{-- ── Stat Cards ── --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="stat-card blue">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon blue"><i class="fas fa-flag-checkered"></i></div>
                            <div>
                                <div class="stat-value">{{ $qty($kartu['saldo_awal']) }} <small>{{ $sat }}</small></div>
                                <div class="stat-label">Saldo Awal</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card green">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon green"><i class="fas fa-arrow-down"></i></div>
                            <div>
                                <div class="stat-value">{{ $qty($kartu['total_masuk']) }} <small>{{ $sat }}</small></div>
                                <div class="stat-label">Total Masuk</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card orange">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon orange"><i class="fas fa-arrow-up"></i></div>
                            <div>
                                <div class="stat-value">{{ $qty($kartu['total_keluar']) }} <small>{{ $sat }}</small></div>
                                <div class="stat-label">Total Keluar</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card {{ $kartu['saldo_akhir'] <= (float) $bahan->stok_minimum ? 'red' : 'blue' }}">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon {{ $kartu['saldo_akhir'] <= (float) $bahan->stok_minimum ? 'red' : 'blue' }}">
                                <i class="fas fa-scale-balanced"></i>
                            </div>
                            <div>
                                <div class="stat-value">{{ $qty($kartu['saldo_akhir']) }} <small>{{ $sat }}</small></div>
                                <div class="stat-label">Saldo Akhir</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── A. Kartu stok ── --}}
            <div class="card filter-card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h5 class="mb-0"><i class="fas fa-book text-primary me-2"></i>Mutasi Stok</h5>
                    <span class="text-muted" style="font-size:.78rem;">{{ $kartu['rows']->count() }} mutasi pada periode ini</span>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>No. Transaksi</th>
                                <th>Keterangan</th>
                                <th>Batch Penerimaan</th>
                                <th class="num">Masuk</th>
                                <th class="num">Keluar</th>
                                <th class="num">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="row-saldo">
                                <td colspan="6">Saldo awal per {{ $dari->format('d/m/Y') }}</td>
                                <td class="num">{{ $qty($kartu['saldo_awal']) }} {{ $sat }}</td>
                            </tr>

                            @forelse ($kartu['rows'] as $row)
                                <tr>
                                    <td class="text-nowrap">{{ $tgl($row->tanggal) }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $row->nomor }}</div>
                                        <span class="{{ $row->tipe === 'masuk' ? 'badge-masuk' : 'badge-keluar' }}">
                                            {{ $row->tipe === 'masuk' ? 'Masuk' : 'Keluar' }}
                                        </span>
                                    </td>
                                    <td>{{ $row->keterangan }}</td>
                                    <td>
                                        @if ($row->batch_nomor)
                                            <div class="fw-semibold">
                                                {{ $row->batch_nomor }}
                                                @if ($row->tipe === 'masuk')
                                                    <span class="badge-fefo">Batch baru</span>
                                                @endif
                                            </div>
                                            <div class="sub-text">Kadaluarsa: {{ $tgl($row->batch_kadaluarsa) }}</div>
                                        @else
                                            <span class="sub-text">Tidak terhubung ke batch</span>
                                        @endif
                                    </td>
                                    <td class="num {{ $row->masuk > 0 ? 'txt-masuk' : 'text-muted' }}">
                                        {{ $row->masuk > 0 ? $qty($row->masuk) : '-' }}
                                    </td>
                                    <td class="num {{ $row->keluar > 0 ? 'txt-keluar' : 'text-muted' }}">
                                        {{ $row->keluar > 0 ? $qty($row->keluar) : '-' }}
                                    </td>
                                    <td class="num fw-bold">{{ $qty($row->saldo) }} {{ $sat }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-0 text-center">
                                        <div class="empty-state" style="padding:28px 24px;">
                                            <div class="fw-semibold text-secondary mb-1">Tidak ada mutasi pada periode ini</div>
                                            <div class="text-muted" style="font-size:.8rem;">Coba perluas rentang tanggal</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse

                            <tr class="row-total">
                                <td colspan="4" class="text-end">Total periode / Saldo akhir</td>
                                <td class="num">{{ $qty($kartu['total_masuk']) }}</td>
                                <td class="num">{{ $qty($kartu['total_keluar']) }}</td>
                                <td class="num">{{ $qty($kartu['saldo_akhir']) }} {{ $sat }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── B. Posisi batch FEFO ── --}}
            <div class="card filter-card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h5 class="mb-0">
                        <i class="fas fa-layer-group text-primary me-2"></i>Stok per Batch Penerimaan (FEFO)
                        <span class="text-muted fw-normal" style="font-size:.78rem;">— posisi {{ $sampai->format('d/m/Y') }}</span>
                    </h5>
                    <span class="text-muted" style="font-size:.78rem;">Kadaluarsa terdekat dipakai lebih dulu</span>
                </div>

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
                            @forelse ($batches as $bt)
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
                            @empty
                                <tr>
                                    <td colspan="10" class="p-0 text-center">
                                        <div class="empty-state" style="padding:28px 24px;">
                                            <div class="fw-semibold text-secondary mb-1">Tidak ada batch yang bersisa</div>
                                            <div class="text-muted" style="font-size:.8rem;">
                                                Semua penerimaan sudah terpakai pada {{ $sampai->format('d/m/Y') }}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse

                            @if ($batches->isNotEmpty())
                                <tr class="row-total">
                                    <td colspan="8" class="text-end">Total sisa semua batch</td>
                                    <td class="num">{{ $qty($batches->sum('sisa')) }} {{ $sat }}</td>
                                    <td></td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                @if ($batches->isNotEmpty() && abs($kartu['saldo_akhir'] - (float) $batches->sum('sisa')) > 0.004)
                    <div class="card-footer">
                        <div class="batch-note mt-0">
                            <i class="fas fa-circle-info me-1"></i>
                            Total sisa batch ({{ $qty($batches->sum('sisa')) }} {{ $sat }}) berbeda dari saldo akhir mutasi
                            ({{ $qty($kartu['saldo_akhir']) }} {{ $sat }}). Kemungkinan ada pemakaian yang tidak
                            dikaitkan ke batch penerimaan tertentu.
                        </div>
                    </div>
                @endif
            </div>
        @endif

    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            const pad = n => String(n).padStart(2, '0');
            const fmt = d => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

            document.querySelectorAll('.preset').forEach(btn => {
                btn.addEventListener('click', function () {
                    const now = new Date();
                    let dari, sampai;

                    switch (this.dataset.preset) {
                        case 'bulan-lalu':
                            dari = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                            sampai = new Date(now.getFullYear(), now.getMonth(), 0);
                            break;
                        case 'tahun-ini':
                            dari = new Date(now.getFullYear(), 0, 1);
                            sampai = now;
                            break;
                        default: // bulan-ini
                            dari = new Date(now.getFullYear(), now.getMonth(), 1);
                            sampai = now;
                    }

                    document.getElementById('input-dari').value = fmt(dari);
                    document.getElementById('input-sampai').value = fmt(sampai);
                });
            });
        })();
    </script>
@endsection
