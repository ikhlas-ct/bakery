@extends('layouts.user.user')

@section('title', 'Laporan Pemakaian Bahan Baku')

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
        .stat-value.sm { font-size: 1.1rem; }
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
        .filter-label { font-size: .72rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 3px; }

        .form-control, .form-select {
            border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: .83rem;
            padding: 7px 12px; color: #334155; background-color: #f8fafc;
        }
        .form-control:focus, .form-select:focus {
            border-color: #16a34a; background: #fff; box-shadow: 0 0 0 3px rgba(22, 163, 74, .12);
        }

        .periode-chip {
            display: inline-flex; align-items: center; gap: 6px;
            background: #f0fdf4; color: #166534; font-weight: 700; font-size: .78rem;
            padding: 6px 12px; border-radius: 999px;
        }

        .table { font-size: .83rem; margin-bottom: 0; }
        .table thead th {
            background: #f8fafc; color: #475569; font-weight: 700;
            font-size: .72rem; text-transform: uppercase; letter-spacing: .4px;
            border-bottom: 1.5px solid #eef2f6; padding: 12px 16px; white-space: nowrap;
        }
        .table tbody td { padding: 12px 16px; vertical-align: top; border-bottom: 1px solid #f4f6f8; }
        .table tbody tr:hover { background: #fafbfc; }

        .name-text { font-weight: 700; color: #1e293b; font-size: .85rem; }
        .sub-text { color: #94a3b8; font-size: .74rem; }
        .badge-kode {
            background: #eef2ff; color: #4338ca; font-weight: 600;
            font-size: .72rem; padding: 5px 10px; border-radius: 8px;
            display: inline-block; white-space: nowrap; margin: 1px 0;
        }
        .nowrap { white-space: nowrap; }

        .bahan-block + .bahan-block { margin-top: 10px; padding-top: 10px; border-top: 1px dashed #e2e8f0; }
        .batch-line { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-top: 4px; }

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
        $qty = fn ($v) => number_format((float) $v, 2, ',', '.');
        $satuanOf = fn ($bahan) => mb_strtolower($bahan?->satuan?->kode_satuan ?: ($bahan?->satuan?->nama ?? ''));
        $totalDipakaiText = $totalPerSatuan->isEmpty()
            ? '0'
            : $totalPerSatuan->map(fn ($total, $sat) => $qty($total) . ' ' . $sat)->implode(' + ');
        $adaFilterLain = $filter['bahan_baku_id'] || $filter['search'] !== '';
    @endphp
    <div class="container">

        {{-- ── Page Header ── --}}
        <div class="ph-card">
            <div class="ph-left">
                <div class="ph-icon"><i class="fas fa-clipboard-list"></i></div>
                <div>
                    <h5 class="ph-title">Laporan Pemakaian Bahan Baku</h5>
                    <ol class="ph-breadcrumb mb-0">
                        <li><a href="{{ $dashboardRoute }}">Dashboard</a></li>
                        <li><span class="bc-active">Laporan</span></li>
                        <li><span class="bc-active">Pemakaian Bahan Baku</span></li>
                    </ol>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('laporan.pemakaian-bahan-baku.cetak', $cetakParams) }}" target="_blank" rel="noopener"
                    class="btn btn-outline-success btn-sm px-3">
                    <i class="fas fa-eye me-1"></i> Lihat Laporan
                </a>
                <a href="{{ route('laporan.pemakaian-bahan-baku.cetak', $cetakParams + ['cetak' => 1]) }}" target="_blank"
                    rel="noopener" class="btn btn-success btn-sm px-3">
                    <i class="fas fa-print me-1"></i> Cetak
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

        {{-- ── Stat Cards ── --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4">
                <div class="stat-card blue">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon blue"><i class="fas fa-file-invoice"></i></div>
                        <div>
                            <div class="stat-value">{{ $pemakaians->total() }}</div>
                            <div class="stat-label">Transaksi Pemakaian</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="stat-card green">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon green"><i class="fas fa-boxes"></i></div>
                        <div>
                            <div class="stat-value">{{ $rekap->count() }}</div>
                            <div class="stat-label">Jenis Bahan Baku</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="stat-card orange">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon orange"><i class="fas fa-weight-hanging"></i></div>
                        <div>
                            <div class="stat-value {{ $totalPerSatuan->count() > 1 ? 'sm' : '' }}">{{ $totalDipakaiText }}</div>
                            <div class="stat-label">Total Dipakai</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Main Card: Rincian ── --}}
        <div class="card filter-card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="mb-0"><i class="fas fa-list text-success me-2"></i>Rincian Pemakaian</h5>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <span class="periode-chip"><i class="far fa-calendar-alt"></i> {{ $periode['label'] }}</span>
                    <span class="text-muted" style="font-size:.78rem;">{{ $pemakaians->total() }} transaksi ditemukan</span>
                </div>
            </div>

            {{-- Filter --}}
            <div class="filter-section">
                <form method="GET" action="{{ route('laporan.pemakaian-bahan-baku.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-2 col-sm-6">
                        <div class="filter-label">Periode</div>
                        <select name="jenis" id="jenis" class="form-select form-select-sm">
                            <option value="harian" @selected($periode['jenis'] === 'harian')>Harian</option>
                            <option value="bulanan" @selected($periode['jenis'] === 'bulanan')>Bulanan</option>
                            <option value="tahunan" @selected($periode['jenis'] === 'tahunan')>Tahunan</option>
                            <option value="rentang" @selected($periode['jenis'] === 'rentang')>Antara Tanggal</option>
                        </select>
                    </div>

                    {{-- Harian --}}
                    <div class="col-md-2 col-sm-6" data-jenis="harian">
                        <div class="filter-label">Tanggal</div>
                        <input type="date" name="tanggal" class="form-control form-control-sm" value="{{ $periode['tanggal'] }}">
                    </div>

                    {{-- Bulanan --}}
                    <div class="col-md-2 col-sm-6" data-jenis="bulanan">
                        <div class="filter-label">Bulan</div>
                        <select name="bulan" class="form-select form-select-sm">
                            @foreach ($bulans as $no => $nama)
                                <option value="{{ $no }}" @selected($periode['bulan'] === $no)>{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Bulanan & Tahunan --}}
                    <div class="col-md-2 col-sm-6" data-jenis="bulanan tahunan">
                        <div class="filter-label">Tahun</div>
                        <select name="tahun" class="form-select form-select-sm">
                            @foreach ($tahuns as $th)
                                <option value="{{ $th }}" @selected($periode['tahun'] === $th)>{{ $th }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Antara tanggal --}}
                    <div class="col-md-2 col-sm-6" data-jenis="rentang">
                        <div class="filter-label">Dari</div>
                        <input type="date" name="dari" class="form-control form-control-sm" value="{{ $periode['dari'] }}">
                    </div>
                    <div class="col-md-2 col-sm-6" data-jenis="rentang">
                        <div class="filter-label">Sampai</div>
                        <input type="date" name="sampai" class="form-control form-control-sm" value="{{ $periode['sampai'] }}">
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="filter-label">Bahan Baku</div>
                        <select name="bahan_baku_id" class="form-select form-select-sm">
                            <option value="">Semua Bahan Baku</option>
                            @foreach ($bahanBakus as $bahan)
                                <option value="{{ $bahan->id }}" @selected($filter['bahan_baku_id'] === $bahan->id)>
                                    {{ $bahan->kode_bahan_baku }} — {{ $bahan->nama_bahan_baku }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="filter-label">Cari</div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text border-end-0 bg-white"
                                style="border-radius:10px 0 0 10px;border:1.5px solid #e2e8f0;">
                                <i class="fas fa-search text-muted" style="font-size:.75rem;"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0"
                                style="border-radius:0 10px 10px 0;" placeholder="No. transaksi / keterangan…"
                                value="{{ $filter['search'] }}">
                        </div>
                    </div>

                    <div class="col-md-auto d-flex gap-2">
                        <button type="submit" class="btn btn-success btn-sm px-3">
                            <i class="fas fa-filter me-1"></i> Tampilkan
                        </button>
                        @if (request()->hasAny(['jenis', 'tanggal', 'bulan', 'tahun', 'dari', 'sampai', 'bahan_baku_id', 'search']))
                            <a href="{{ route('laporan.pemakaian-bahan-baku.index') }}" class="btn btn-outline-secondary btn-sm px-3">
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
                            <th>No. Transaksi</th>
                            <th>Tanggal Pakai</th>
                            <th>Keterangan</th>
                            <th style="min-width:360px;">Bahan Baku &amp; Batch Penerimaan (FEFO)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pemakaians as $pmk)
                            <tr>
                                <td><span class="badge-kode">{{ $pmk->nomor_transaksi }}</span></td>
                                <td class="nowrap">{{ $pmk->tanggal_pakai->translatedFormat('d M Y') }}</td>
                                <td>{{ $pmk->keterangan ?: '-' }}</td>
                                <td>
                                    @foreach ($pmk->pemakaianBahanBakuDetails->groupBy('bahan_baku_id') as $rows)
                                        @php
                                            $bahan = $rows->first()->bahanBaku;
                                            $sat   = $satuanOf($bahan);
                                        @endphp
                                        <div class="bahan-block">
                                            <div class="d-flex justify-content-between align-items-center gap-3">
                                                <span class="name-text">{{ $bahan->nama_bahan_baku ?? '-' }}</span>
                                                <span class="fw-bold nowrap">{{ $qty($rows->sum('jumlah_dipakai')) }} {{ $sat }}</span>
                                            </div>
                                            @foreach ($rows as $row)
                                                @php
                                                    $batch = $row->penerimaanBahanBakuDetail;
                                                    $pen   = $batch?->penerimaanBahanBaku;
                                                @endphp
                                                <div class="batch-line">
                                                    <div class="sub-text">
                                                        <span class="badge-kode">{{ $pen->nomor_transaksi ?? '-' }}</span>
                                                        Terima {{ $pen?->tanggal_terima?->format('d/m/Y') ?? '-' }}
                                                        · Exp {{ $batch?->tanggal_kadaluarsa?->format('d/m/Y') ?? '-' }}
                                                    </div>
                                                    <div class="sub-text nowrap">{{ $qty($row->jumlah_dipakai) }} {{ $sat }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-0 text-center">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"><i class="fas fa-clipboard-list"></i></div>
                                        <div class="fw-semibold text-secondary mb-1">Tidak ada pemakaian bahan baku</div>
                                        <div class="text-muted" style="font-size:.8rem;">
                                            Belum ada transaksi pemakaian pada periode {{ $periode['label'] }}
                                            @if ($adaFilterLain) dengan filter yang dipilih @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($pemakaians->hasPages())
                <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">
                        Menampilkan
                        <strong>{{ $pemakaians->firstItem() }}</strong>–<strong>{{ $pemakaians->lastItem() }}</strong>
                        dari <strong>{{ $pemakaians->total() }}</strong> transaksi
                    </small>
                    {{ $pemakaians->links() }}
                </div>
            @endif
        </div>

        {{-- ── Rekapitulasi per Bahan Baku (seluruh periode) ── --}}
        <div class="card filter-card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="mb-0"><i class="fas fa-chart-bar text-success me-2"></i>Rekapitulasi per Bahan Baku</h5>
                <span class="text-muted" style="font-size:.78rem;">Seluruh transaksi pada periode {{ $periode['label'] }}</span>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="60">No</th>
                            <th>Kode</th>
                            <th>Nama Bahan Baku</th>
                            <th class="text-center">Jumlah Transaksi</th>
                            <th class="text-end">Total Dipakai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rekap as $i => $r)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td><span class="badge-kode">{{ $r->bahanBaku->kode_bahan_baku ?? '-' }}</span></td>
                                <td><div class="name-text">{{ $r->bahanBaku->nama_bahan_baku ?? '-' }}</div></td>
                                <td class="text-center">{{ $r->jumlah_transaksi }}</td>
                                <td class="text-end fw-bold nowrap">{{ $qty($r->total_dipakai) }} {{ $satuanOf($r->bahanBaku) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Tidak ada data untuk direkap</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($rekap->isNotEmpty())
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-end">Total</th>
                                <th class="text-end nowrap">{{ $totalDipakaiText }}</th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- Tampilkan input filter sesuai jenis periode yang dipilih --}}
    <script>
        (function () {
            var jenis = document.getElementById('jenis');
            var groups = document.querySelectorAll('[data-jenis]');

            function sync() {
                groups.forEach(function (g) {
                    var aktif = g.getAttribute('data-jenis').split(' ').indexOf(jenis.value) !== -1;
                    g.classList.toggle('d-none', !aktif);
                    // input yang disembunyikan tidak ikut terkirim, supaya URL tetap bersih
                    g.querySelectorAll('input, select').forEach(function (el) { el.disabled = !aktif; });
                });
            }

            jenis.addEventListener('change', sync);
            sync();
        })();
    </script>
@endsection
