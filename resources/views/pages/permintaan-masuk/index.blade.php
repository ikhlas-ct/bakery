@extends('layouts.user.user')

@section('title', 'Permintaan Bahan Baku Masuk')

@section('styles')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        body, .card, .table, .btn, h4, h5 { font-family: 'Plus Jakarta Sans', sans-serif; }

        .stat-card {
            border: none; border-radius: 16px; padding: 20px; position: relative; overflow: hidden;
            transition: transform .2s ease, box-shadow .2s ease; box-shadow: 0 2px 12px rgba(0,0,0,.07);
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.12); }
        .stat-card::after {
            content: ''; position: absolute; right: -18px; top: -18px;
            width: 80px; height: 80px; border-radius: 50%; opacity: .12;
        }
        .stat-card.orange { background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); }
        .stat-card.orange::after { background: #ea580c; }
        .stat-card.green  { background: linear-gradient(135deg, #e6f9f0 0%, #d1fae5 100%); }
        .stat-card.green::after  { background: #16a34a; }
        .stat-card.blue   { background: linear-gradient(135deg, #e8f0fe 0%, #dbeafe 100%); }
        .stat-card.blue::after   { background: #1a73e8; }
        .stat-card.red    { background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); }
        .stat-card.red::after    { background: #dc2626; }

        .stat-icon {
            width: 48px; height: 48px; border-radius: 12px; display: inline-flex;
            align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; color: #fff;
        }
        .stat-icon.orange { background: #ea580c; } .stat-icon.green { background: #16a34a; }
        .stat-icon.blue { background: #1a73e8; }   .stat-icon.red { background: #dc2626; }
        .stat-value { font-size: 1.85rem; font-weight: 800; line-height: 1; color: #1e293b; }
        .stat-label { font-size: .78rem; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; color: #64748b; margin-top: 3px; }

        .ph-card {
            background: #fff; border: 1px solid #e9ecef; border-radius: 14px; padding: 16px 20px;
            display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;
            margin-bottom: 1.25rem; position: relative; overflow: hidden; box-shadow: 0 1px 6px rgba(0,0,0,.05);
        }
        .ph-card::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; border-radius: 14px 0 0 14px; background: #1a73e8; }
        .ph-left { display: flex; align-items: center; gap: 12px; }
        .ph-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; background: #dbeafe; color: #1a73e8; }
        .ph-title { font-size: 1.15rem; font-weight: 800; color: #1e293b; margin: 0; }
        .ph-breadcrumb { list-style: none; display: flex; gap: 6px; padding: 0; margin: 2px 0 0; }
        .ph-breadcrumb li:not(:last-child)::after { content: '/'; margin-left: 6px; color: #cbd5e1; }
        .ph-breadcrumb a { color: #1a73e8; text-decoration: none; font-size: .78rem; }
        .ph-breadcrumb .bc-active { font-size: .78rem; color: #94a3b8; }

        .filter-card { border: none; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,.07); overflow: hidden; }
        .filter-card .card-header { background: #fff; border-bottom: 1px solid #f1f5f9; padding: 18px 24px; }
        .filter-card .card-header h5 { font-size: .95rem; font-weight: 700; color: #1e293b; }
        .filter-section { background: #fafbfc; border-bottom: 1px solid #f1f5f9; padding: 16px 24px; }

        .form-control, .form-select {
            border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: .83rem; padding: 7px 12px;
            color: #334155; background-color: #f8fafc; transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus, .form-select:focus { border-color: #1a73e8; background: #fff; box-shadow: 0 0 0 3px rgba(26,115,232,.12); }
        .form-control::placeholder { color: #94a3b8; }

        .table { font-size: .83rem; margin-bottom: 0; }
        .table thead th {
            background: #f8fafc; color: #475569; font-weight: 700; font-size: .72rem; text-transform: uppercase;
            letter-spacing: .4px; border-bottom: 1.5px solid #eef2f6; padding: 12px 16px; white-space: nowrap;
        }
        .table tbody td { padding: 12px 16px; vertical-align: middle; border-bottom: 1px solid #f4f6f8; }
        .table tbody tr:hover { background: #fafbfc; }
        .name-text { font-weight: 700; color: #1e293b; font-size: .85rem; }
        .sub-text { color: #94a3b8; font-size: .74rem; }

        .badge-kode { background: #eef2ff; color: #4338ca; font-weight: 600; font-size: .72rem; padding: 5px 10px; border-radius: 8px; }
        .chip { font-weight: 600; font-size: .7rem; padding: 4px 9px; border-radius: 8px; display: inline-block; margin: 0 4px 4px 0; }
        .chip-pending   { background: #fff7ed; color: #c2410c; }
        .chip-disetujui { background: #f0fdf4; color: #166534; }
        .chip-sebagian  { background: #eff6ff; color: #1d4ed8; }
        .chip-ditolak   { background: #fef2f2; color: #dc2626; }

        .btn-tanggapi { border-radius: 8px; font-size: .76rem; font-weight: 600; padding: 6px 12px; }

        .empty-state { padding: 48px 24px; }
        .empty-state-icon { width: 64px; height: 64px; border-radius: 16px; background: #f1f5f9; color: #94a3b8; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 16px; }

        .alert { border-radius: 12px; border: none; font-size: .85rem; }
        .alert-success { background: #dcfce7; color: #166534; }
        .alert-danger { background: #fee2e2; color: #991b1b; }
    </style>
@endsection

@section('content')
    <div class="container">

        {{-- ── Page Header ── --}}
        <div class="ph-card mb-4">
            <div class="ph-left">
                <div class="ph-icon"><i class="fas fa-inbox"></i></div>
                <div>
                    <h5 class="ph-title">Permintaan Bahan Baku Masuk</h5>
                    <ol class="ph-breadcrumb mb-0">
                        <li><a href="{{ route('produsen.dashboard') }}">Dashboard</a></li>
                        <li><span class="bc-active">Permintaan Masuk</span></li>
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

        {{-- ── Stat Cards (per item) ── --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card orange">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon orange"><i class="fas fa-hourglass-half"></i></div>
                        <div><div class="stat-value">{{ $totalPending }}</div><div class="stat-label">Menunggu</div></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card green">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon green"><i class="fas fa-check"></i></div>
                        <div><div class="stat-value">{{ $totalSetuju }}</div><div class="stat-label">Disetujui</div></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card blue">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon blue"><i class="fas fa-adjust"></i></div>
                        <div><div class="stat-value">{{ $totalSebagian }}</div><div class="stat-label">Sebagian</div></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card red">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon red"><i class="fas fa-times"></i></div>
                        <div><div class="stat-value">{{ $totalTolak }}</div><div class="stat-label">Ditolak</div></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Main Card ── --}}
        <div class="card filter-card">

            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="mb-0"><i class="fas fa-list text-primary me-2"></i>Daftar Permintaan</h5>
                <span class="text-muted" style="font-size:.78rem;">{{ $rows->total() }} permintaan ditemukan</span>
            </div>

            {{-- Filter --}}
            <div class="filter-section">
                <form method="GET" action="{{ route('produsen.permintaan.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-5 col-sm-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text border-end-0 bg-white"
                                style="border-radius:10px 0 0 10px;border:1.5px solid #e2e8f0;">
                                <i class="fas fa-search text-muted" style="font-size:.75rem;"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0"
                                style="border-radius:0 10px 10px 0;" placeholder="Cari nomor transaksi…"
                                value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua Status</option>
                            <option value="pending" @selected(request('status') === 'pending')>Menunggu</option>
                            <option value="disetujui" @selected(request('status') === 'disetujui')>Disetujui</option>
                            <option value="disetujui_sebagian" @selected(request('status') === 'disetujui_sebagian')>Disetujui Sebagian</option>
                            <option value="ditolak" @selected(request('status') === 'ditolak')>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-auto d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-3">
                            <i class="fas fa-filter me-1"></i> Cari
                        </button>
                        @if (request()->hasAny(['search', 'status']))
                            <a href="{{ route('produsen.permintaan.index') }}" class="btn btn-outline-secondary btn-sm px-3">
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
                            <th>Permintaan</th>
                            <th>Tanggal</th>
                            <th>Item Untuk Anda</th>
                            <th>Status Item</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $i => $row)
                            <tr>
                                <td class="text-muted">{{ $rows->firstItem() + $i }}</td>
                                <td>
                                    <span class="badge-kode">{{ $row->nomor }}</span>
                                    <div class="sub-text mt-1">Dari: {{ $row->dari }}</div>
                                </td>
                                <td>{{ $row->tanggal->translatedFormat('d M Y') }}</td>
                                <td>
                                    <div class="name-text">{{ $row->jumlahItem }} item</div>
                                    <div class="sub-text">{{ $row->ringkasan }}</div>
                                </td>
                                <td>
                                    @if ($row->pending)
                                        <span class="chip chip-pending">{{ $row->pending }} menunggu</span>
                                    @endif
                                    @if ($row->disetujui)
                                        <span class="chip chip-disetujui">{{ $row->disetujui }} disetujui</span>
                                    @endif
                                    @if ($row->sebagian)
                                        <span class="chip chip-sebagian">{{ $row->sebagian }} sebagian</span>
                                    @endif
                                    @if ($row->ditolak)
                                        <span class="chip chip-ditolak">{{ $row->ditolak }} ditolak</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ $row->url }}"
                                        class="btn btn-tanggapi {{ $row->pending ? 'btn-primary' : 'btn-outline-secondary' }}">
                                        <i class="fas {{ $row->pending ? 'fa-reply' : 'fa-eye' }} me-1"></i>
                                        {{ $row->pending ? 'Tanggapi' : 'Lihat' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-0 text-center">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"><i class="fas fa-inbox"></i></div>
                                        <div class="fw-semibold text-secondary mb-1">Belum ada permintaan masuk</div>
                                        <div class="text-muted" style="font-size:.8rem;">
                                            Permintaan dari admin bakery akan muncul di sini
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($rows->hasPages())
                <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">
                        Menampilkan <strong>{{ $rows->firstItem() }}</strong>–<strong>{{ $rows->lastItem() }}</strong>
                        dari <strong>{{ $rows->total() }}</strong> data
                    </small>
                    {{ $rows->links() }}
                </div>
            @endif

        </div>
    </div>
@endsection
