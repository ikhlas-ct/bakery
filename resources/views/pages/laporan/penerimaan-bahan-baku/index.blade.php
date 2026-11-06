@extends('layouts.user.user')

@section('title', 'Laporan Penerimaan Bahan Baku')

@section('styles')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        body, .card, .table, .btn, h4, h5 { font-family: 'Plus Jakarta Sans', sans-serif; }

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

        /* ===== STAT CARDS ===== */
        .stat-card {
            border: none; border-radius: 16px; padding: 20px; box-shadow: 0 2px 12px rgba(0,0,0,.07); height: 100%;
        }
        .stat-card.blue   { background: linear-gradient(135deg, #e8f0fe 0%, #dbeafe 100%); }
        .stat-card.green  { background: linear-gradient(135deg, #e6f9f0 0%, #d1fae5 100%); }
        .stat-card.orange { background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); }
        .stat-icon {
            width: 48px; height: 48px; border-radius: 12px; display: inline-flex; align-items: center;
            justify-content: center; font-size: 1.25rem; flex-shrink: 0; color: #fff;
        }
        .stat-icon.blue { background: #1a73e8; } .stat-icon.green { background: #16a34a; } .stat-icon.orange { background: #ea580c; }
        .stat-value { font-size: 1.35rem; font-weight: 800; line-height: 1.15; color: #1e293b; }
        .stat-label { font-size: .78rem; font-weight: 600; letter-spacing: .3px; color: #64748b; margin-top: 3px; }

        /* ===== FILTER & TABLE CARD ===== */
        .filter-card { border: none; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,.07); overflow: hidden; }
        .filter-card .card-header { background: #fff; border-bottom: 1px solid #f1f5f9; padding: 18px 24px; }
        .filter-card .card-header h5 { font-size: .95rem; font-weight: 700; color: #1e293b; }
        .filter-section { background: #fafbfc; border-bottom: 1px solid #f1f5f9; padding: 16px 24px; }
        .filter-section label { font-size: .72rem; font-weight: 700; color: #64748b; margin-bottom: 4px; }

        .form-control, .form-select {
            border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: .83rem;
            padding: 7px 12px; color: #334155; background-color: #f8fafc;
        }
        .form-control:focus, .form-select:focus {
            border-color: #1a73e8; background: #fff; box-shadow: 0 0 0 3px rgba(26,115,232,.12);
        }

        /* ===== TABEL LAPORAN ===== */
        .rpt-table { width: 100%; font-size: .83rem; border-collapse: collapse; }
        .rpt-table thead th {
            background: #f8fafc; color: #475569; font-weight: 700; font-size: .72rem;
            text-transform: uppercase; letter-spacing: .4px; border-bottom: 1.5px solid #eef2f6;
            padding: 12px 16px; white-space: nowrap;
        }
        .rpt-table tbody td, .rpt-table tfoot td { padding: 11px 16px; border-bottom: 1px solid #f4f6f8; vertical-align: middle; }
        .rpt-table tr.rpt-group td { background: #eef2ff; color: #3730a3; border-bottom: 1px solid #e0e7ff; }
        .rpt-table tfoot td { background: #f8fafc; border-top: 1.5px solid #e2e8f0; }
        .rpt-muted { color: #94a3b8; font-weight: 500; }
        .rpt-empty { text-align: center; color: #94a3b8; padding: 48px 24px !important; }
    </style>
@endsection

@section('content')
    @php
        $dashboardRoute = auth()->user()->role === 'admin' ? route('admin.dashboard') : route('pemilik.dashboard');
    @endphp
    <div class="container">

        {{-- ── Page Header ── --}}
        <div class="ph-card mb-4">
            <div class="ph-left">
                <div class="ph-icon"><i class="fas fa-file-alt"></i></div>
                <div>
                    <h5 class="ph-title">Laporan Penerimaan Bahan Baku</h5>
                    <ol class="ph-breadcrumb mb-0">
                        <li><a href="{{ $dashboardRoute }}">Dashboard</a></li>
                        <li><span class="bc-active">Laporan</span></li>
                        <li><span class="bc-active">Penerimaan Barang</span></li>
                    </ol>
                </div>
            </div>
            @if (auth()->user()->role === 'admin')
                <a href="{{ route('penerimaan-bahan-baku.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Penerimaan
                </a>
            @endif
        </div>

        {{-- ── Stat Cards ── --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="stat-card blue">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon blue"><i class="fas fa-truck-loading"></i></div>
                        <div>
                            <div class="stat-value">{{ $ringkasan['jumlah_transaksi'] }}</div>
                            <div class="stat-label">Transaksi penerimaan</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="stat-card green">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon green"><i class="fas fa-boxes"></i></div>
                        <div>
                            <div class="stat-value">{{ $ringkasan['total_diterima'] }}</div>
                            <div class="stat-label">Total diterima</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="stat-card orange">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon orange"><i class="fas fa-money-bill-wave"></i></div>
                        <div>
                            <div class="stat-value">{{ $ringkasan['total_nilai'] }}</div>
                            <div class="stat-label">Total nilai</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Filter + Hasil ── --}}
        <div class="card filter-card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="mb-0"><i class="fas fa-calendar-alt text-primary me-2"></i>{{ $periode['label_jenis'] }}</h5>
                <span class="text-muted" style="font-size:.78rem;">
                    {{ $periode['label_periode'] }}: <strong>{{ $periode['label'] }}</strong>
                </span>
            </div>

            <div class="filter-section">
                <form method="GET" id="form-laporan" action="{{ route('laporan.penerimaan-bahan-baku.index') }}"
                    class="row g-2 align-items-end">

                    <div class="col-md-2 col-sm-6">
                        <label for="jenis">Jenis laporan</label>
                        <select name="jenis" id="jenis" class="form-select form-select-sm">
                            <option value="harian"  @selected($filter['jenis'] === 'harian')>Harian</option>
                            <option value="bulanan" @selected($filter['jenis'] === 'bulanan')>Bulanan</option>
                            <option value="tahunan" @selected($filter['jenis'] === 'tahunan')>Tahunan</option>
                            <option value="rentang" @selected($filter['jenis'] === 'rentang')>Pilih rentang tanggal</option>
                        </select>
                    </div>

                    <div class="col-md-2 col-sm-6 f-periode" data-jenis="harian">
                        <label for="tanggal">Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal" class="form-control form-control-sm"
                            value="{{ $filter['tanggal'] }}">
                    </div>

                    <div class="col-md-2 col-sm-6 f-periode" data-jenis="bulanan">
                        <label for="bulan">Bulan</label>
                        <input type="month" name="bulan" id="bulan" class="form-control form-control-sm"
                            value="{{ $filter['bulan'] }}">
                    </div>

                    <div class="col-md-2 col-sm-6 f-periode" data-jenis="tahunan">
                        <label for="tahun">Tahun</label>
                        <input type="number" name="tahun" id="tahun" class="form-control form-control-sm"
                            min="2000" max="2100" value="{{ $filter['tahun'] }}">
                    </div>

                    <div class="col-md-2 col-sm-6 f-periode" data-jenis="rentang">
                        <label for="dari">Dari tanggal</label>
                        <input type="date" name="dari" id="dari" class="form-control form-control-sm"
                            value="{{ $filter['dari'] }}">
                    </div>
                    <div class="col-md-2 col-sm-6 f-periode" data-jenis="rentang">
                        <label for="sampai">Sampai tanggal</label>
                        <input type="date" name="sampai" id="sampai" class="form-control form-control-sm"
                            value="{{ $filter['sampai'] }}">
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <label for="produsen_id">Supplier</label>
                        <select name="produsen_id" id="produsen_id" class="form-select form-select-sm">
                            <option value="">Semua Supplier</option>
                            @foreach ($produsens as $p)
                                <option value="{{ $p->id }}" @selected($filter['produsen_id'] == $p->id)>
                                    {{ $p->nama_produsen }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-auto d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-3">
                            <i class="fas fa-search me-1"></i> Tampilkan
                        </button>
                        <button type="submit" class="btn btn-success btn-sm px-3"
                            formaction="{{ route('laporan.penerimaan-bahan-baku.cetak') }}" formtarget="_blank">
                            <i class="fas fa-print me-1"></i> Cetak / PDF
                        </button>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                @php
                    $qty = fn ($n) => number_format($n, 2, ',', '.');
                    $rp  = fn ($n) => 'Rp ' . number_format($n, fmod($n, 1) == 0 ? 0 : 2, ',', '.');
                    $tgl = fn ($d, $f = 'j M Y') => $d ? $d->copy()->locale('id')->translatedFormat($f) : '-';
                    $totalJumlah = $transaksis->flatMap(fn ($t) => $t->items)->groupBy('satuan')
                        ->map(fn ($g, $s) => trim($qty($g->sum('jumlah')) . ' ' . $s))->implode(' + ');
                @endphp

                <table class="rpt-table">
                    <thead>
                        <tr>
                            <th>Bahan baku</th>
                            <th>Kode</th>
                            <th class="text-end">Jumlah diterima</th>
                            <th class="text-end">Harga satuan</th>
                            <th class="text-end">Subtotal</th>
                            <th>Kadaluarsa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transaksis as $t)
                            <tr class="rpt-group">
                                <td colspan="5">
                                    <strong>{{ $t->nomor }}</strong>
                                    @if ($periode['jenis'] !== 'harian')
                                        <span class="rpt-muted"> · {{ $tgl($t->tanggal) }}</span>
                                    @endif
                                    <span class="rpt-muted"> · Supplier: {{ $t->produsen }}</span>
                                </td>
                                <td class="text-end"><strong>{{ $rp($t->subtotal) }}</strong></td>
                            </tr>
                            @foreach ($t->items as $item)
                                <tr>
                                    <td>{{ $item->nama }}</td>
                                    <td>{{ $item->kode }}</td>
                                    <td class="text-end">{{ $qty($item->jumlah) }} {{ $item->satuan }}</td>
                                    <td class="text-end">{{ $rp($item->harga) }}</td>
                                    <td class="text-end">{{ $rp($item->subtotal) }}</td>
                                    <td>{{ $tgl($item->kadaluarsa) }}</td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="6" class="rpt-empty">Tidak ada penerimaan bahan baku pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($transaksis->isNotEmpty())
                        <tfoot>
                            <tr>
                                <td colspan="2"><strong>Total</strong></td>
                                <td class="text-end"><strong>{{ $totalJumlah }}</strong></td>
                                <td></td>
                                <td class="text-end"><strong>{{ $rp($totalNilai) }}</strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        const jenis = document.getElementById('jenis');

        function syncPeriode() {
            document.querySelectorAll('.f-periode').forEach(el => {
                const aktif = el.dataset.jenis === jenis.value;
                el.classList.toggle('d-none', !aktif);
                // input yang disembunyikan di-disable supaya tidak ikut ke URL
                el.querySelectorAll('input').forEach(i => i.disabled = !aktif);
            });
        }

        jenis.addEventListener('change', syncPeriode);
        syncPeriode();
    </script>
@endsection
