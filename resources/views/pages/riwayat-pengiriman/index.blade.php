@extends('layouts.user.user')

@section('title', 'Riwayat Pengiriman')

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
        .btn-cetak { background: #f0fdf4; color: #16a34a; }
        .btn-cetak:hover { background: #16a34a; color: #fff; }
        .lbl-filter { font-size: .85rem; font-weight: 700; color: #334155; margin-bottom: 6px; display: block; }

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
        // Jenis laporan terpilih: dari request; link lama (?dari=&sampai=) dianggap "Antara Tanggal".
        $jenis = request('jenis') ?: (request()->filled('dari') || request()->filled('sampai') ? 'rentang' : 'semua');
        $adaFilter = request()->filled('nomor') || $jenis !== 'semua';
    @endphp
    <div class="container">

        {{-- ── Page Header ── --}}
        <div class="ph-card mb-4">
            <div class="ph-left">
                <div class="ph-icon"><i class="fas fa-truck"></i></div>
                <div>
                    <h5 class="ph-title">Riwayat Pengiriman</h5>
                    <ol class="ph-breadcrumb mb-0">
                        <li><a href="{{ route('produsen.dashboard') }}">Dashboard</a></li>
                        <li><span class="bc-active">Riwayat Pengiriman</span></li>
                    </ol>
                </div>
            </div>
        </div>

        {{-- ── Main Card ── --}}
        <div class="card filter-card">

            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="mb-0"><i class="fas fa-list text-primary me-2"></i>Daftar Pengiriman Diterima</h5>
                <span class="text-muted" style="font-size:.78rem;">{{ $penerimaans->total() }} data ditemukan</span>
            </div>

            {{-- Filter + Cetak: Jenis laporan -> periode -> No. Transaksi -> Tampilkan / Cetak --}}
            <div class="filter-section" id="panelFilter">
                <form method="GET" action="{{ route('produsen.pengiriman.index') }}" class="row g-3 align-items-end">

                    <div class="col-md-3 col-sm-12">
                        <label class="lbl-filter" for="filterJenis">Jenis laporan</label>
                        <select name="jenis" id="filterJenis" class="form-select form-select-sm">
                            @foreach (['semua' => 'Semua Periode', 'harian' => 'Harian', 'bulanan' => 'Bulanan', 'tahunan' => 'Tahunan', 'rentang' => 'Antara Tanggal'] as $val => $label)
                                <option value="{{ $val }}" {{ $jenis === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-12 opsi-periode" data-jenis="harian">
                        <label class="lbl-filter">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control form-control-sm"
                            value="{{ request('tanggal', now()->toDateString()) }}">
                    </div>
                    <div class="col-md-3 col-sm-12 opsi-periode" data-jenis="bulanan">
                        <label class="lbl-filter">Bulan</label>
                        <input type="month" name="bulan" class="form-control form-control-sm"
                            value="{{ request('bulan', now()->format('Y-m')) }}">
                    </div>
                    <div class="col-md-2 col-sm-12 opsi-periode" data-jenis="tahunan">
                        <label class="lbl-filter">Tahun</label>
                        <input type="number" name="tahun" min="2000" max="2100" class="form-control form-control-sm"
                            value="{{ request('tahun', now()->year) }}">
                    </div>
                    <div class="col-md-2 col-sm-6 opsi-periode" data-jenis="rentang">
                        <label class="lbl-filter">Dari tanggal</label>
                        <input type="date" name="dari" class="form-control form-control-sm" value="{{ request('dari') }}">
                    </div>
                    <div class="col-md-2 col-sm-6 opsi-periode" data-jenis="rentang">
                        <label class="lbl-filter">Sampai tanggal</label>
                        <input type="date" name="sampai" class="form-control form-control-sm" value="{{ request('sampai') }}">
                    </div>

                    <div class="col-md-3 col-sm-12">
                        <label class="lbl-filter" for="filterNomor">No. Transaksi</label>
                        <input type="text" name="nomor" id="filterNomor" class="form-control form-control-sm"
                            placeholder="Cari nomor transaksi..." value="{{ request('nomor') }}">
                    </div>

                    <div class="col-md-auto d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-3">
                            <i class="fas fa-search me-1"></i> Tampilkan
                        </button>
                        <button type="submit" formaction="{{ route('produsen.pengiriman.cetak') }}" formtarget="_blank"
                            class="btn btn-success btn-sm px-3">
                            <i class="fas fa-print me-1"></i> Cetak / PDF
                        </button>
                        @if ($adaFilter)
                            <a href="{{ route('produsen.pengiriman.index') }}" class="btn btn-outline-secondary btn-sm px-3">
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
                            <th>No. Transaksi</th>
                            <th>Tanggal Terima</th>
                            <th class="text-end">Jumlah Item</th>
                            <th class="text-end">Total Nilai</th>
                            <th width="90">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($penerimaans as $p)
                            <tr>
                                <td class="text-muted">{{ $penerimaans->firstItem() + $loop->index }}</td>
                                <td><span class="badge-kode">{{ $p->nomor_transaksi }}</span></td>
                                <td>{{ $p->tanggal_terima->translatedFormat('d M Y') }}</td>
                                <td class="text-end">{{ $p->penerimaan_bahan_baku_details_count }} item</td>
                                <td class="text-end name-text">{{ $rupiah($p->total_nilai) }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('produsen.pengiriman.show', $p->id) }}" class="btn-action btn-detail" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('produsen.pengiriman.cetak-transaksi', $p->id) }}" target="_blank"
                                            class="btn-action btn-cetak" title="Cetak transaksi ini">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-0 text-center">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"><i class="fas fa-truck"></i></div>
                                        <div class="fw-semibold text-secondary mb-1">Belum ada riwayat pengiriman</div>
                                        <div class="text-muted" style="font-size:.8rem;">
                                            {{ $adaFilter ? 'Coba ubah filter pencarian' : 'Pengiriman akan muncul setelah admin mencatat penerimaan barang dari Anda' }}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($penerimaans->hasPages())
                <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">
                        Menampilkan
                        <strong>{{ $penerimaans->firstItem() }}</strong>–<strong>{{ $penerimaans->lastItem() }}</strong>
                        dari <strong>{{ $penerimaans->total() }}</strong> data
                    </small>
                    {{ $penerimaans->links() }}
                </div>
            @endif

        </div>

    </div>
    <script>
        (function () {
            var jenis = document.getElementById('filterJenis');
            if (!jenis) return;

            function tampilkanOpsi() {
                document.querySelectorAll('#panelFilter .opsi-periode').forEach(function (el) {
                    var aktif = el.dataset.jenis === jenis.value;
                    el.style.display = aktif ? '' : 'none';
                    // input yang disembunyikan tidak ikut dikirim
                    el.querySelectorAll('input').forEach(function (inp) { inp.disabled = !aktif; });
                });
            }

            jenis.addEventListener('change', tampilkanOpsi);
            tampilkanOpsi();
        })();
    </script>
@endsection
