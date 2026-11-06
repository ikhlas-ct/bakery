@extends('layouts.user.user')

@section('title', 'Receipt Pembayaran')

@section('styles')
    <style>
        .stat-card {
            border: none; border-radius: 16px; padding: 20px; position: relative; overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
        }
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
            width: 48px; height: 48px; border-radius: 12px; flex-shrink: 0;
            display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem;
        }
        .stat-icon.blue   { background: #1a73e8; color: #fff; }
        .stat-icon.green  { background: #16a34a; color: #fff; }
        .stat-icon.orange { background: #ea580c; color: #fff; }
        .stat-value { font-size: 1.5rem; font-weight: 800; line-height: 1.1; color: #1e293b; }
        .stat-label {
            font-size: .78rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: .5px; color: #64748b; margin-top: 3px;
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
            border-radius: 14px 0 0 14px; background: #16a34a;
        }
        .ph-left { display: flex; align-items: center; gap: 12px; }
        .ph-icon {
            width: 42px; height: 42px; border-radius: 10px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; background: #dcfce7; color: #16a34a;
        }
        .ph-title { font-size: 1.15rem; font-weight: 800; color: #1e293b; margin: 0; }
        .ph-breadcrumb { list-style: none; display: flex; gap: 6px; padding: 0; margin: 2px 0 0; }
        .ph-breadcrumb li:not(:last-child)::after { content: '/'; margin-left: 6px; color: #cbd5e1; }
        .ph-breadcrumb a { color: #1a73e8; text-decoration: none; font-size: .78rem; }
        .ph-breadcrumb .bc-active { font-size: .78rem; color: #94a3b8; }

        .filter-card { border: none; border-radius: 16px; box-shadow: 0 2px 12px rgba(0, 0, 0, .07); overflow: hidden; }
        .filter-card .card-header { background: #fff; border-bottom: 1px solid #f1f5f9; padding: 18px 24px; }
        .filter-card .card-header h5 { font-size: .95rem; font-weight: 700; color: #1e293b; }
        .filter-section { background: #fafbfc; border-bottom: 1px solid #f1f5f9; padding: 16px 24px; }

        .form-control, .form-select {
            border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: .83rem;
            padding: 7px 12px; color: #334155; background-color: #f8fafc;
        }
        .form-control:focus, .form-select:focus {
            border-color: #16a34a; background: #fff; box-shadow: 0 0 0 3px rgba(22, 163, 74, .12);
        }


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
            display: inline-block; white-space: nowrap; margin: 1px 0;
        }
        .nowrap { white-space: nowrap; }
        .badge-lunas {
            background: #f0fdf4; color: #166534; font-weight: 600;
            font-size: .72rem; padding: 5px 10px; border-radius: 8px;
        }
        .badge-sebagian {
            background: #fef3c7; color: #92400e; font-weight: 600;
            font-size: .72rem; padding: 5px 10px; border-radius: 8px;
        }

        .btn-action {
            width: 32px; height: 32px; border-radius: 8px; border: none;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: .78rem; transition: all .15s;
        }
        .btn-detail { background: #eff6ff; color: #1a73e8; }
        .btn-detail:hover { background: #1a73e8; color: #fff; }
        .btn-cetak { background: #f0fdf4; color: #16a34a; }
        .btn-cetak:hover { background: #16a34a; color: #fff; }

        .empty-state { padding: 48px 24px; }
        .empty-state-icon {
            width: 64px; height: 64px; border-radius: 16px; background: #f1f5f9; color: #94a3b8;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; margin: 0 auto 16px;
        }

        .alert { border-radius: 12px; border: none; font-size: .85rem; }
        .alert-success { background: #dcfce7; color: #166534; }
        .alert-danger { background: #fee2e2; color: #991b1b; }
    </style>
@endsection

@section('content')
    @php
        $dashboardRoute = auth()->user()->role === 'admin' ? route('admin.dashboard') : route('pemilik.dashboard');
        $rupiah = fn ($v) => 'Rp ' . number_format($v, 0, ',', '.');
    @endphp
    <div class="container">

        {{-- ── Page Header ── --}}
        <div class="ph-card">
            <div class="ph-left">
                <div class="ph-icon"><i class="fas fa-receipt"></i></div>
                <div>
                    <h5 class="ph-title">Receipt Pembayaran</h5>
                    <ol class="ph-breadcrumb mb-0">
                        <li><a href="{{ $dashboardRoute }}">Dashboard</a></li>
                        <li><span class="bc-active">Laporan</span></li>
                        <li><span class="bc-active">Receipt Pembayaran</span></li>
                    </ol>
                </div>
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

        {{-- ── Stat Cards ── --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4">
                <div class="stat-card blue">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon blue"><i class="fas fa-receipt"></i></div>
                        <div>
                            <div class="stat-value">{{ $totalReceipt }}</div>
                            <div class="stat-label">Receipt Ditemukan</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="stat-card green">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon green"><i class="fas fa-money-bill-wave"></i></div>
                        <div>
                            <div class="stat-value">{{ $rupiah($totalNominal) }}</div>
                            <div class="stat-label">Total Dibayar</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="stat-card orange">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon orange"><i class="fas fa-calendar-check"></i></div>
                        <div>
                            <div class="stat-value">{{ $receiptBulanIni }}</div>
                            <div class="stat-label">Dibayar Bulan Ini</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Main Card ── --}}
        <div class="card filter-card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="mb-0"><i class="fas fa-list text-success me-2"></i>Daftar Receipt (Pembayaran Lunas)</h5>
                <span class="text-muted" style="font-size:.78rem;">{{ $grups->total() }} data ditemukan</span>
            </div>

            {{-- Filter --}}
            <div class="filter-section">
                <form method="GET" action="{{ route('laporan.receipt-pembayaran.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-3 col-sm-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text border-end-0 bg-white"
                                style="border-radius:10px 0 0 10px;border:1.5px solid #e2e8f0;">
                                <i class="fas fa-search text-muted" style="font-size:.75rem;"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0"
                                style="border-radius:0 10px 10px 0;" placeholder="Cari no. bukti / penerimaan / produsen…"
                                value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <select name="produsen_id" class="form-select form-select-sm">
                            <option value="">Semua Produsen</option>
                            @foreach ($produsens as $produsen)
                                <option value="{{ $produsen->id }}" @selected(request('produsen_id') == $produsen->id)>
                                    {{ $produsen->nama_produsen }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <select name="metode_bayar" class="form-select form-select-sm">
                            <option value="">Semua Metode</option>
                            @foreach ($metodes as $metode)
                                <option value="{{ $metode }}" @selected(request('metode_bayar') == $metode)>{{ $metode }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <input type="date" name="dari" class="form-control form-control-sm"
                            value="{{ request('dari') }}" title="Tanggal bayar dari">
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <input type="date" name="sampai" class="form-control form-control-sm"
                            value="{{ request('sampai') }}" title="Tanggal bayar sampai">
                    </div>
                    <div class="col-md-auto d-flex gap-2">
                        <button type="submit" class="btn btn-success btn-sm px-3">
                            <i class="fas fa-filter me-1"></i> Cari
                        </button>
                        @if (request()->hasAny(['search', 'produsen_id', 'metode_bayar', 'dari', 'sampai']))
                            <a href="{{ route('laporan.receipt-pembayaran.index') }}" class="btn btn-outline-secondary btn-sm px-3">
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
                            <th>No. Bukti</th>
                            <th>Tanggal Bayar</th>
                            <th>Produsen</th>
                            <th>Penerimaan</th>
                            <th>Permintaan</th>
                            <th>Metode</th>
                            <th class="text-end">Total Dibayar</th>
                            <th>Status</th>
                            <th width="90">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($grups as $g)
                            @php
                                $pen = $g['penerimaan'];
                                $metodeBayar = $g['lunas']->pluck('metode_bayar')->filter()->unique()->implode(' / ');
                            @endphp
                            <tr>
                                <td><span class="badge-kode">{{ $g['nomor'] }}</span></td>
                                <td class="nowrap">{{ $g['tanggal']->translatedFormat('d M Y') }}</td>
                                <td><div class="name-text">{{ $g['produsen']->nama_produsen ?? '-' }}</div></td>
                                <td><span class="badge-kode">{{ $pen->nomor_transaksi }}</span></td>
                                <td>
                                    @foreach ($g['permintaanNomors'] as $nomorPermintaan)
                                        <span class="badge-kode">{{ $nomorPermintaan }}</span>
                                    @endforeach
                                </td>
                                <td>{{ $metodeBayar ?: '-' }}</td>
                                <td class="text-end fw-bold nowrap">{{ $rupiah($g['total']) }}</td>
                                <td>
                                    @if ($g['belum']->isEmpty())
                                        <span class="badge-lunas">Lunas</span>
                                    @else
                                        <span class="badge-sebagian" title="Ada tagihan lain yang belum lunas">Sebagian lunas</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('laporan.receipt-pembayaran.show', $pen->id) }}"
                                            target="_blank" rel="noopener" class="btn btn-action btn-detail" title="Lihat Receipt">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('laporan.receipt-pembayaran.show', ['penerimaan' => $pen->id, 'cetak' => 1]) }}"
                                            target="_blank" rel="noopener" class="btn btn-action btn-cetak" title="Cetak Receipt">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-0 text-center">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"><i class="fas fa-receipt"></i></div>
                                        <div class="fw-semibold text-secondary mb-1">Belum ada receipt pembayaran</div>
                                        <div class="text-muted" style="font-size:.8rem;">
                                            Receipt muncul setelah tagihan produsen dilunasi
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($grups->hasPages())
                <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">
                        Menampilkan
                        <strong>{{ $grups->firstItem() }}</strong>–<strong>{{ $grups->lastItem() }}</strong>
                        dari <strong>{{ $grups->total() }}</strong> data
                    </small>
                    {{ $grups->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
