@extends('layouts.user.user')

@section('title', 'Dashboard Pemilik')

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

        /* ===== LIST ITEM ===== */
        .list-item {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 14px 24px; border-bottom: 1px solid #f4f6f8;
        }
        .list-item:last-child { border-bottom: none; }
        .list-item:hover { background: #fafbfc; }

        .stok-bar { height: 6px; border-radius: 99px; background: #f1f5f9; overflow: hidden; margin-top: 6px; width: 100%; min-width: 110px; }
        .stok-bar > span { display: block; height: 100%; border-radius: 99px; background: #dc2626; }
        .stok-bar.blue > span { background: #1a73e8; }

        /* ===== QUICK ACTION ===== */
        .btn-quick { border-radius: 10px; font-size: .8rem; font-weight: 600; padding: 7px 14px; }

        /* ===== GRAFIK BATANG ===== */
        .chart-wrap {
            display: flex; align-items: flex-start; gap: 8px;
            padding: 36px 20px 18px;
        }
        .chart-col { flex: 1; min-width: 0; display: flex; flex-direction: column; align-items: center; }
        .chart-area {
            height: 190px; width: 100%;
            display: flex; align-items: flex-end; justify-content: center;
            border-bottom: 1.5px solid #eef2f6;
        }
        .chart-bar {
            position: relative; width: 100%; max-width: 40px; min-height: 3px;
            border-radius: 8px 8px 0 0; background: linear-gradient(180deg, #60a5fa, #1a73e8);
            transition: opacity .2s ease;
        }
        .chart-bar:hover { opacity: .8; }
        .chart-bar.now   { background: linear-gradient(180deg, #34d399, #16a34a); }
        .chart-bar.empty { background: #e2e8f0; }
        .chart-val {
            position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%);
            margin-bottom: 4px; font-size: .66rem; font-weight: 700; color: #475569; white-space: nowrap;
        }
        .chart-lbl { font-size: .7rem; color: #94a3b8; margin-top: 8px; white-space: nowrap; }
        .chart-lbl.now { color: #16a34a; font-weight: 700; }
        .chart-legend { font-size: .74rem; color: #94a3b8; padding: 0 24px 16px; display: flex; gap: 16px; flex-wrap: wrap; }
        .chart-legend i { display: inline-block; width: 10px; height: 10px; border-radius: 3px; margin-right: 5px; }

        /* ===== RANKING PRODUSEN ===== */
        .rank-no {
            width: 28px; height: 28px; border-radius: 8px; flex-shrink: 0;
            background: #f1f5f9; color: #64748b; font-size: .78rem; font-weight: 800;
            display: flex; align-items: center; justify-content: center;
        }
        .rank-no.top { background: #fef3c7; color: #b45309; }

        /* ===== LINIMASA AKTIVITAS ===== */
        .act-icon {
            width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: .85rem;
        }
        .act-icon.blue   { background: #dbeafe; color: #1a73e8; }
        .act-icon.green  { background: #d1fae5; color: #16a34a; }
        .act-icon.orange { background: #ffedd5; color: #ea580c; }

        /* ===== EMPTY STATE ===== */
        .empty-state { padding: 40px 24px; text-align: center; }
        .empty-state-icon {
            width: 56px; height: 56px; border-radius: 16px; background: #f1f5f9; color: #94a3b8;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.35rem; margin: 0 auto 14px;
        }
        .empty-state-icon.ok { background: #f0fdf4; color: #16a34a; }
    </style>
@endsection

@section('content')
    @php
        $angka  = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, ',', '.'), '0'), ',');
        $rupiah = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');

        // Angka ringkas untuk label grafik: 5,6 jt / 154 rb / 1,2 M
        $ringkas = function ($n) {
            $n = (float) $n;
            if ($n >= 1_000_000_000) return rtrim(rtrim(number_format($n / 1_000_000_000, 1, ',', '.'), '0'), ',') . ' M';
            if ($n >= 1_000_000)     return rtrim(rtrim(number_format($n / 1_000_000, 1, ',', '.'), '0'), ',') . ' jt';
            if ($n >= 1_000)         return rtrim(rtrim(number_format($n / 1_000, 0, ',', '.'), '0'), ',') . ' rb';
            return number_format($n, 0, ',', '.');
        };

        // ── Nama route sisi pemilik. Sesuaikan dengan routes/web.php bila berbeda.
        //    Bila route tidak ada, tautan/tombolnya otomatis disembunyikan.
        $namaRoute = [
            'pembayaran'        => 'pemilik.pembayaran.index',
            'pembayaran_detail' => 'pemilik.pembayaran.show',
            'bahan_baku'        => 'pemilik.bahan-baku.index',
            'produsen'          => 'pemilik.produsen.index',
        ];
        $url = fn (string $kunci, $param = []) =>
            \Illuminate\Support\Facades\Route::has($namaRoute[$kunci])
                ? route($namaRoute[$kunci], $param)
                : null;

        $nilaiMaksGrafik = max(1, (float) $grafikBulanan->max('total'));
    @endphp

    <div class="container">

        {{-- ── Page Header ── --}}
        <div class="ph-card mb-4">
            <div class="ph-left">
                <div class="ph-icon"><i class="fas fa-user-tie"></i></div>
                <div>
                    <h5 class="ph-title">Halo, {{ $namaPemilik }}</h5>
                    <ol class="ph-breadcrumb mb-0">
                        <li><span class="bc-active">Dashboard</span></li>
                        <li><span class="bc-active">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span></li>
                    </ol>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap align-items-center">
                @if ($jumlahMenungguAcc > 0)
                    <span class="badge-pending">{{ $jumlahMenungguAcc }} tagihan menunggu persetujuan</span>
                @else
                    <span class="badge-aman">Tidak ada tagihan tertunda</span>
                @endif
                @if ($url('pembayaran'))
                    <a href="{{ $url('pembayaran') }}" class="btn btn-primary btn-quick">
                        <i class="fas fa-wallet me-1"></i> Pembayaran
                    </a>
                @endif
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
            <div class="col-6 col-md-4">
                <div class="stat-card green">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon green"><i class="fas fa-money-bill-wave"></i></div>
                        <div>
                            <div class="stat-value sm">{{ $rupiah($pengeluaranBulanIni) }}</div>
                            <div class="stat-label">Pengeluaran Bulan Ini</div>
                            @if (! is_null($persenDariBulanLalu))
                                <div class="stat-note">
                                    {{ $persenDariBulanLalu >= 0 ? '▲' : '▼' }}
                                    {{ number_format(abs($persenDariBulanLalu), 1, ',', '.') }}% dari bulan lalu
                                </div>
                            @else
                                <div class="stat-note">pembayaran lunas bulan ini</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="stat-card blue">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon blue"><i class="fas fa-chart-line"></i></div>
                        <div>
                            <div class="stat-value sm">{{ $rupiah($pengeluaranTahunIni) }}</div>
                            <div class="stat-label">Pengeluaran Tahun Ini</div>
                            <div class="stat-note">sejak 1 Januari {{ now()->year }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="stat-card orange">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon orange"><i class="fas fa-hourglass-half"></i></div>
                        <div>
                            <div class="stat-value">{{ $jumlahMenungguAcc }}</div>
                            <div class="stat-label">Menunggu Persetujuan</div>
                            <div class="stat-note">senilai {{ $rupiah($nilaiMenungguAcc) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="stat-card red">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon red"><i class="fas fa-file-invoice-dollar"></i></div>
                        <div>
                            <div class="stat-value sm">{{ $rupiah($nilaiBelumLunas) }}</div>
                            <div class="stat-label">Belum Lunas</div>
                            <div class="stat-note">{{ $jumlahBelumLunas }} tagihan belum dibayar</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="stat-card purple">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon purple"><i class="fas fa-exclamation-triangle"></i></div>
                        <div>
                            <div class="stat-value">{{ $jumlahStokKritis }}</div>
                            <div class="stat-label">Stok Kritis</div>
                            <div class="stat-note">dari {{ $bahanBakuAktif }} bahan baku aktif</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="stat-card teal">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon teal"><i class="fas fa-calendar-times"></i></div>
                        <div>
                            <div class="stat-value">{{ $totalBatchKadaluarsa }}</div>
                            <div class="stat-label">Batch Hampir Kedaluwarsa</div>
                            <div class="stat-note">≤ {{ $batasHariKadaluarsa }} hari atau sudah lewat</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Baris 1: Grafik pengeluaran + Produsen teratas ── --}}
        <div class="row g-3 mb-3">

            {{-- Grafik pengeluaran per bulan --}}
            <div class="col-lg-8">
                <div class="card filter-card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="mb-0"><i class="fas fa-chart-bar text-primary me-2"></i>Pengeluaran per Bulan</h5>
                        <span class="text-muted" style="font-size:.78rem;">12 bulan terakhir · total {{ $rupiah($totalGrafik) }}</span>
                    </div>

                    <div class="chart-wrap">
                        @foreach ($grafikBulanan as $bulan)
                            @php
                                $tinggi = $bulan['total'] > 0 ? max(3, round($bulan['total'] / $nilaiMaksGrafik * 100)) : 0;
                            @endphp
                            <div class="chart-col">
                                <div class="chart-area">
                                    <div class="chart-bar {{ $bulan['bulanIni'] ? 'now' : '' }} {{ $bulan['total'] <= 0 ? 'empty' : '' }}"
                                        style="height: {{ $tinggi }}%;"
                                        title="{{ $bulan['lengkap'] }}: {{ $rupiah($bulan['total']) }}">
                                        @if ($bulan['total'] > 0)
                                            <span class="chart-val">{{ $ringkas($bulan['total']) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="chart-lbl {{ $bulan['bulanIni'] ? 'now' : '' }}">{{ $bulan['label'] }}</div>
                            </div>
                        @endforeach
                    </div>

                    <div class="chart-legend">
                        <span><i style="background:#1a73e8;"></i>Bulan sebelumnya</span>
                        <span><i style="background:#16a34a;"></i>Bulan ini</span>
                        <span>Hanya pembayaran berstatus lunas, dihitung per tanggal bayar</span>
                    </div>
                </div>
            </div>

            {{-- Produsen dengan nilai transaksi terbesar --}}
            <div class="col-lg-4">
                <div class="card filter-card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="mb-0"><i class="fas fa-industry text-warning me-2"></i>Produsen Terbesar</h5>
                        @if ($url('produsen'))
                            <a href="{{ $url('produsen') }}" class="link-semua">Lihat semua →</a>
                        @else
                            <span class="text-muted" style="font-size:.78rem;">12 bulan terakhir</span>
                        @endif
                    </div>

                    @forelse ($produsenTeratas as $i => $baris)
                        <div class="list-item">
                            <div class="d-flex align-items-center gap-3 flex-grow-1" style="min-width:0;">
                                <div class="rank-no {{ $i === 0 ? 'top' : '' }}">{{ $i + 1 }}</div>
                                <div class="flex-grow-1" style="min-width:0;">
                                    <div class="name-text text-truncate">{{ $baris->produsen->nama_produsen ?? 'Produsen dihapus' }}</div>
                                    <div class="sub-text">{{ $baris->jumlah_transaksi }} tagihan · {{ $rupiah($baris->total_nilai) }}</div>
                                    <div class="stok-bar blue">
                                        <span style="width: {{ $nilaiProdusenTerbesar > 0 ? round($baris->total_nilai / $nilaiProdusenTerbesar * 100) : 0 }}%;"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <div class="empty-state-icon"><i class="fas fa-industry"></i></div>
                            <div class="fw-semibold text-secondary mb-1">Belum ada transaksi</div>
                            <div class="text-muted" style="font-size:.8rem;">Ranking muncul setelah ada tagihan dari produsen</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ── Baris 2: Tagihan perlu perhatian + Aktivitas admin ── --}}
        <div class="row g-3 mb-3">

            {{-- Tagihan --}}
            <div class="col-lg-7">
                <div class="card filter-card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="mb-0"><i class="fas fa-file-invoice-dollar text-danger me-2"></i>Tagihan Perlu Perhatian</h5>
                        @if ($url('pembayaran'))
                            <a href="{{ $url('pembayaran') }}" class="link-semua">Lihat semua →</a>
                        @endif
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Transaksi</th>
                                    <th>Produsen</th>
                                    <th class="text-end">Jumlah</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tagihanPerhatian as $bayar)
                                    <tr>
                                        <td>
                                            <span class="badge-kode">{{ $bayar->permintaanBahanBaku->nomor_transaksi ?? '-' }}</span>
                                            <div class="sub-text mt-1">{{ $bayar->created_at?->format('d/m/Y') }}</div>
                                        </td>
                                        <td class="name-text">{{ $bayar->produsen->nama_produsen ?? '-' }}</td>
                                        <td class="text-end fw-semibold">{{ $rupiah($bayar->jumlah_bayar) }}</td>
                                        <td>
                                            @if ($bayar->status_acc === 'menunggu')
                                                <span class="badge-pending">Menunggu persetujuan</span>
                                            @else
                                                <span class="badge-sebagian">Disetujui, belum dibayar</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if ($url('pembayaran_detail', $bayar->id))
                                                <a href="{{ $url('pembayaran_detail', $bayar->id) }}"
                                                    class="btn btn-sm {{ $bayar->status_acc === 'menunggu' ? 'btn-primary' : 'btn-outline-primary' }} btn-quick">
                                                    {{ $bayar->status_acc === 'menunggu' ? 'Tinjau' : 'Detail' }}
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-0">
                                            <div class="empty-state">
                                                <div class="empty-state-icon ok"><i class="fas fa-check"></i></div>
                                                <div class="fw-semibold text-secondary mb-1">Tidak ada tagihan tertunda</div>
                                                <div class="text-muted" style="font-size:.8rem;">Semua tagihan sudah disetujui dan dibayar</div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Aktivitas admin terbaru --}}
            <div class="col-lg-5">
                <div class="card filter-card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="mb-0"><i class="fas fa-history text-primary me-2"></i>Aktivitas Admin Terbaru</h5>
                    </div>

                    @forelse ($aktivitasAdmin as $aktivitas)
                        <div class="list-item">
                            <div class="d-flex align-items-center gap-3" style="min-width:0;">
                                <div class="act-icon {{ $aktivitas['warna'] }}"><i class="fas {{ $aktivitas['ikon'] }}"></i></div>
                                <div style="min-width:0;">
                                    <div class="name-text">{{ $aktivitas['judul'] }}</div>
                                    <div class="sub-text">
                                        {{ $aktivitas['admin'] }}
                                        @if ($aktivitas['nomor'])
                                            · {{ $aktivitas['nomor'] }}
                                        @endif
                                        @if ($aktivitas['catatan'])
                                            · {{ $aktivitas['catatan'] }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="sub-text text-end" style="white-space:nowrap;" title="{{ $aktivitas['waktu']->format('d/m/Y H:i') }}">
                                {{ $aktivitas['waktu']->locale('id')->diffForHumans() }}
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <div class="empty-state-icon"><i class="fas fa-history"></i></div>
                            <div class="fw-semibold text-secondary mb-1">Belum ada aktivitas</div>
                            <div class="text-muted" style="font-size:.8rem;">Aktivitas admin akan tampil di sini</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ── Baris 3: Stok kritis + Batch kedaluwarsa ── --}}
        <div class="row g-3 mb-4">

            {{-- Stok kritis --}}
            <div class="col-lg-6">
                <div class="card filter-card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="mb-0"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Stok Kritis</h5>
                        @if ($url('bahan_baku'))
                            <a href="{{ $url('bahan_baku') }}" class="link-semua">Lihat semua →</a>
                        @else
                            <span class="text-muted" style="font-size:.78rem;">{{ $jumlahStokKritis }} bahan baku</span>
                        @endif
                    </div>

                    @forelse ($stokKritis as $bahan)
                        @php
                            $persen = (float) $bahan->stok_minimum > 0
                                ? min(100, round((float) $bahan->stok_saat_ini / (float) $bahan->stok_minimum * 100))
                                : 0;
                        @endphp
                        <div class="list-item">
                            <div class="flex-grow-1" style="min-width:0;">
                                <div class="name-text">{{ $bahan->nama_bahan_baku }}</div>
                                <div class="sub-text">
                                    <span class="badge-kode">{{ $bahan->kode_bahan_baku }}</span>
                                    @if ($bahan->kategoriBarang)
                                        <span class="badge-kategori">{{ $bahan->kategoriBarang->nama_kategori }}</span>
                                    @endif
                                </div>
                                <div class="stok-bar"><span style="width: {{ $persen }}%;"></span></div>
                            </div>
                            <div class="text-end">
                                <span class="badge-kritis">{{ $angka($bahan->stok_saat_ini) }} {{ $bahan->satuan->nama ?? '' }}</span>
                                <div class="sub-text mt-1">min. {{ $angka($bahan->stok_minimum) }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <div class="empty-state-icon ok"><i class="fas fa-check"></i></div>
                            <div class="fw-semibold text-secondary mb-1">Semua stok aman</div>
                            <div class="text-muted" style="font-size:.8rem;">Tidak ada bahan baku di bawah stok minimum</div>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Batch mendekati kedaluwarsa --}}
            <div class="col-lg-6">
                <div class="card filter-card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="mb-0"><i class="fas fa-calendar-times text-warning me-2"></i>Batch Mendekati Kedaluwarsa</h5>
                        <span class="text-muted" style="font-size:.78rem;">≤ {{ $batasHariKadaluarsa }} hari · {{ $totalBatchKadaluarsa }} batch</span>
                    </div>

                    @forelse ($batchKadaluarsa as $batch)
                        <div class="list-item">
                            <div>
                                <div class="name-text">{{ $batch->bahanBaku->nama_bahan_baku ?? '-' }}</div>
                                <div class="sub-text">
                                    {{ $batch->penerimaanBahanBaku->nomor_transaksi ?? '-' }}
                                    @if ($batch->penerimaanBahanBaku?->produsen)
                                        · {{ $batch->penerimaanBahanBaku->produsen->nama_produsen }}
                                    @endif
                                </div>
                                <div class="sub-text">
                                    sisa {{ $angka($batch->jumlah_tersisa) }} {{ $batch->bahanBaku->satuan->nama ?? '' }}
                                    · exp {{ $batch->tanggal_kadaluarsa->format('d/m/Y') }}
                                </div>
                            </div>
                            <div class="text-end">
                                @if ($batch->sisa_hari < 0)
                                    <span class="badge-kritis">Lewat {{ abs($batch->sisa_hari) }} hari</span>
                                @elseif ($batch->sisa_hari === 0)
                                    <span class="badge-kritis">Hari ini</span>
                                @elseif ($batch->sisa_hari <= 7)
                                    <span class="badge-kritis">{{ $batch->sisa_hari }} hari lagi</span>
                                @else
                                    <span class="badge-warn">{{ $batch->sisa_hari }} hari lagi</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <div class="empty-state-icon ok"><i class="fas fa-check"></i></div>
                            <div class="fw-semibold text-secondary mb-1">Tidak ada batch bermasalah</div>
                            <div class="text-muted" style="font-size:.8rem;">
                                Tidak ada batch yang kedaluwarsa dalam {{ $batasHariKadaluarsa }} hari ke depan
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
@endsection
