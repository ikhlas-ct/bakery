@extends('layouts.user.user')

@section('title', 'Pemakaian Bahan Baku')

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
        width: 42px; height: 42px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; flex-shrink: 0; background: #dbeafe; color: #1a73e8;
    }
    .ph-title { font-size: 1.15rem; font-weight: 800; color: #1e293b; margin: 0; }
    .ph-breadcrumb { list-style: none; display: flex; gap: 6px; padding: 0; margin: 2px 0 0; }
    .ph-breadcrumb li:not(:last-child)::after { content: '/'; margin-left: 6px; color: #cbd5e1; }
    .ph-breadcrumb a { color: #1a73e8; text-decoration: none; font-size: .78rem; }
    .ph-breadcrumb a:hover { text-decoration: underline; }
    .ph-breadcrumb .bc-active { font-size: .78rem; color: #94a3b8; }

    /* ===== TABLE ===== */
    .filter-card { border: none; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,.07); overflow: hidden; }
    .filter-card .card-header { background: #fff; border-bottom: 1px solid #f1f5f9; padding: 18px 24px; }
    .filter-card .card-header h5 { font-size: .95rem; font-weight: 700; color: #1e293b; }
    .filter-section { background: #fafbfc; border-bottom: 1px solid #f1f5f9; padding: 16px 24px; }

    .table { font-size: .83rem; margin-bottom: 0; }
    .table thead th {
        background: #f8fafc; color: #475569; font-weight: 700; font-size: .72rem;
        text-transform: uppercase; letter-spacing: .4px; border-bottom: 1.5px solid #eef2f6;
        padding: 12px 16px; white-space: nowrap;
    }
    .table tbody td { padding: 12px 16px; vertical-align: middle; border-bottom: 1px solid #f4f6f8; }
    .table tbody tr:hover { background: #fafbfc; }

    .name-text { font-weight: 700; color: #1e293b; font-size: .85rem; }
    .sub-text { color: #94a3b8; font-size: .74rem; }

    .badge-kode, .badge-info, .badge-kritis, .badge-aman, .badge-muted {
        font-weight: 600; font-size: .72rem; padding: 5px 10px; border-radius: 8px; display: inline-block;
    }
    .badge-kode   { background: #eef2ff; color: #4338ca; }
    .badge-info   { background: #f0f9ff; color: #0369a1; }
    .badge-kritis { background: #fef2f2; color: #dc2626; }
    .badge-aman   { background: #f0fdf4; color: #166534; }
    .badge-muted  { background: #f1f5f9; color: #64748b; }

    .btn-action {
        width: 32px; height: 32px; border-radius: 8px; border: none;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: .78rem; transition: all .15s; text-decoration: none;
    }
    .btn-detail { background: #eff6ff; color: #1d4ed8; }
    .btn-detail:hover { background: #1d4ed8; color: #fff; }
    .btn-edit { background: #fff7ed; color: #c2410c; }
    .btn-edit:hover { background: #c2410c; color: #fff; }
    .btn-hapus { background: #fef2f2; color: #dc2626; }
    .btn-hapus:hover { background: #dc2626; color: #fff; }

    /* ===== EMPTY STATE & ALERT ===== */
    .empty-state { padding: 48px 24px; }
    .empty-state-icon {
        width: 64px; height: 64px; border-radius: 16px; background: #f1f5f9; color: #94a3b8;
        display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 16px;
    }
    .alert { border-radius: 12px; border: none; font-size: .85rem; }
    .alert-success { background: #dcfce7; color: #166534; }
    .alert-danger  { background: #fee2e2; color: #991b1b; }
    .alert-info-soft { background: #eff6ff; color: #1e40af; }

        .stat-card {
            border: none; border-radius: 16px; padding: 20px; position: relative; overflow: hidden;
            transition: transform .2s ease, box-shadow .2s ease; box-shadow: 0 2px 12px rgba(0,0,0,.07);
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.12); }
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
            width: 48px; height: 48px; border-radius: 12px; display: inline-flex;
            align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;
        }
        .stat-icon.blue   { background: #1a73e8; color: #fff; }
        .stat-icon.green  { background: #16a34a; color: #fff; }
        .stat-icon.orange { background: #ea580c; color: #fff; }
        .stat-value { font-size: 1.85rem; font-weight: 800; line-height: 1; color: #1e293b; }
        .stat-label {
            font-size: .78rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: .5px; color: #64748b; margin-top: 3px;
        }
    </style>
@endsection

@section('content')
    @php
        $fmt = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');
    @endphp
    <div class="container">

        {{-- ── Page Header ── --}}
        <div class="ph-card mb-4">
            <div class="ph-left">
                <div class="ph-icon"><i class="fas fa-utensils"></i></div>
                <div>
                    <h5 class="ph-title">Pemakaian Bahan Baku</h5>
                    <ol class="ph-breadcrumb mb-0">
                        <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li><span class="bc-active">Pemakaian Bahan Baku</span></li>
                    </ol>
                </div>
            </div>
            <a href="{{ route('pemakaian-bahan-baku.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Catat Pemakaian
            </a>
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
                <div class="stat-card blue">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon blue"><i class="fas fa-receipt"></i></div>
                        <div>
                            <div class="stat-value">{{ $totalTransaksi }}</div>
                            <div class="stat-label">Total Transaksi</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="stat-card green">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon green"><i class="fas fa-calendar-alt"></i></div>
                        <div>
                            <div class="stat-value">{{ $transaksiBulanIni }}</div>
                            <div class="stat-label">Bulan Ini</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="stat-card orange">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon orange"><i class="fas fa-calendar-day"></i></div>
                        <div>
                            <div class="stat-value">{{ $transaksiHariIni }}</div>
                            <div class="stat-label">Hari Ini</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Main Card ── --}}
        <div class="card filter-card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="mb-0"><i class="fas fa-list text-primary me-2"></i>Riwayat Pemakaian</h5>
                <span class="text-muted" style="font-size:.78rem;">{{ $pemakaians->total() }} data ditemukan</span>
            </div>

            {{-- Filter --}}
            <div class="filter-section">
                <form method="GET" action="{{ route('pemakaian-bahan-baku.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-3 col-sm-6">
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Cari nomor / keterangan…" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <select name="bahan_baku_id" class="form-select form-select-sm">
                            <option value="">Semua Bahan Baku</option>
                            @foreach ($bahanBakus as $b)
                                <option value="{{ $b->id }}" @selected(request('bahan_baku_id') == $b->id)>
                                    {{ $b->kode_bahan_baku }} – {{ $b->nama_bahan_baku }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <input type="date" name="dari" class="form-control form-control-sm"
                            value="{{ request('dari') }}" title="Dari tanggal">
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <input type="date" name="sampai" class="form-control form-control-sm"
                            value="{{ request('sampai') }}" title="Sampai tanggal">
                    </div>
                    <div class="col-md-auto d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-3">
                            <i class="fas fa-filter me-1"></i> Cari
                        </button>
                        @if (request()->hasAny(['search', 'bahan_baku_id', 'dari', 'sampai']))
                            <a href="{{ route('pemakaian-bahan-baku.index') }}" class="btn btn-outline-secondary btn-sm px-3">
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
                            <th>Transaksi</th>
                            <th>Tanggal Pakai</th>
                            <th>Bahan Baku Dipakai</th>
                            <th>Dicatat Oleh</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pemakaians as $i => $p)
                            @php
                                $perBahan = $p->pemakaianBahanBakuDetails->groupBy('bahan_baku_id');
                            @endphp
                            <tr>
                                <td class="text-muted">{{ $pemakaians->firstItem() + $i }}</td>
                                <td>
                                    <span class="badge-kode">{{ $p->nomor_transaksi }}</span>
                                    @if ($p->keterangan)
                                        <div class="sub-text mt-1">{{ \Illuminate\Support\Str::limit($p->keterangan, 50) }}</div>
                                    @endif
                                </td>
                                <td>{{ $p->tanggal_pakai->format('d/m/Y') }}</td>
                                <td>
                                    @foreach ($perBahan->take(2) as $baris)
                                        <div>
                                            <span class="name-text">{{ $baris->first()->bahanBaku->nama_bahan_baku ?? '-' }}</span>
                                            <span class="text-muted">
                                                – {{ $fmt($baris->sum(fn ($d) => (float) $d->jumlah_dipakai)) }}
                                                {{ $baris->first()->bahanBaku->satuan->kode_satuan ?? ($baris->first()->bahanBaku->satuan->nama ?? '') }}
                                            </span>
                                        </div>
                                    @endforeach
                                    @if ($perBahan->count() > 2)
                                        <span class="badge-muted">+{{ $perBahan->count() - 2 }} bahan lainnya</span>
                                    @endif
                                </td>
                                <td>{{ $p->admin->user->username ?? '-' }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('pemakaian-bahan-baku.show', $p->id) }}"
                                            class="btn btn-action btn-detail" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('pemakaian-bahan-baku.edit', $p->id) }}"
                                            class="btn btn-action btn-edit" title="Edit">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                        <button type="button" class="btn btn-action btn-hapus" data-id="{{ $p->id }}"
                                            data-nomor="{{ $p->nomor_transaksi }}" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                        <form id="form-hapus-{{ $p->id }}"
                                            action="{{ route('pemakaian-bahan-baku.destroy', $p->id) }}" method="POST"
                                            class="d-none">
                                            @csrf @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-0 text-center">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"><i class="fas fa-utensils"></i></div>
                                        <div class="fw-semibold text-secondary mb-1">Belum ada data pemakaian bahan baku</div>
                                        <div class="text-muted" style="font-size:.8rem;">
                                            Coba ubah filter pencarian atau catat pemakaian baru
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
                        Menampilkan <strong>{{ $pemakaians->firstItem() }}</strong>–<strong>{{ $pemakaians->lastItem() }}</strong>
                        dari <strong>{{ $pemakaians->total() }}</strong> data
                    </small>
                    {{ $pemakaians->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.querySelectorAll('.btn-hapus').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                const nomor = this.dataset.nomor;
                swal({
                    title: 'Hapus Pemakaian?',
                    text: `Transaksi ${nomor} akan dihapus permanen dan stok dikembalikan ke batch asal.`,
                    icon: 'warning',
                    buttons: {
                        cancel: 'Batal',
                        confirm: { text: 'Ya, Hapus!', className: 'btn-danger' }
                    },
                    dangerMode: true,
                }).then(confirmed => {
                    if (confirmed) document.getElementById('form-hapus-' + id).submit();
                });
            });
        });
    </script>
@endsection
