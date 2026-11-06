@extends('layouts.user.user')

@section('title', 'Detail Pemakaian – ' . $pemakaian->nomor_transaksi)

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

    /* ===== INFO GRID (halaman detail) ===== */
    .info-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: #94a3b8; }
    .info-value { font-size: .9rem; font-weight: 600; color: #1e293b; }

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
    </style>
@endsection

@section('content')
    @php
        $fmt = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');
        $satuanDari = fn ($bb) => $bb->satuan->kode_satuan ?? ($bb->satuan->nama ?? '');
    @endphp
    <div class="container">

        {{-- ── Page Header ── --}}
        <div class="ph-card">
            <div class="ph-left">
                <div class="ph-icon"><i class="fas fa-utensils"></i></div>
                <div>
                    <h5 class="ph-title">Detail Pemakaian Bahan Baku</h5>
                    <ol class="ph-breadcrumb">
                        <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li><a href="{{ route('pemakaian-bahan-baku.index') }}">Pemakaian Bahan Baku</a></li>
                        <li><span class="bc-active">{{ $pemakaian->nomor_transaksi }}</span></li>
                    </ol>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('pemakaian-bahan-baku.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
                <a href="{{ route('pemakaian-bahan-baku.edit', $pemakaian->id) }}" class="btn btn-warning btn-sm text-white">
                    <i class="fas fa-pencil-alt me-1"></i> Edit
                </a>
                <button type="button" class="btn btn-danger btn-sm" id="btn-hapus">
                    <i class="fas fa-trash-alt me-1"></i> Hapus
                </button>
                <form id="form-hapus" action="{{ route('pemakaian-bahan-baku.destroy', $pemakaian->id) }}" method="POST" class="d-none">
                    @csrf @method('DELETE')
                </form>
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

        {{-- ── Info Transaksi ── --}}
        <div class="card filter-card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-file-alt text-primary me-2"></i>Informasi Transaksi</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="info-label">Nomor Transaksi</div>
                        <div class="info-value"><span class="badge-kode">{{ $pemakaian->nomor_transaksi }}</span></div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-label">Tanggal Pakai</div>
                        <div class="info-value">{{ $pemakaian->tanggal_pakai->format('d/m/Y') }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-label">Dicatat Oleh</div>
                        <div class="info-value">{{ $pemakaian->admin->user->username ?? '-' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-label">Dibuat</div>
                        <div class="info-value">{{ $pemakaian->created_at?->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="col-12">
                        <div class="info-label">Keterangan</div>
                        <div class="info-value">{{ $pemakaian->keterangan ?: '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Ringkasan per bahan baku ── --}}
        <div class="card filter-card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-boxes-stacked text-primary me-2"></i>Ringkasan Pemakaian</h5>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="40">#</th>
                            <th>Bahan Baku</th>
                            <th>Total Dipakai</th>
                            <th>Diambil dari</th>
                            <th>Stok Saat Ini</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ringkasan as $i => $r)
                            <tr>
                                <td class="text-muted">{{ $i + 1 }}</td>
                                <td>
                                    <div class="name-text">{{ $r->bahanBaku->nama_bahan_baku }}</div>
                                    <span class="badge-kode">{{ $r->bahanBaku->kode_bahan_baku }}</span>
                                </td>
                                <td class="fw-bold">{{ $fmt($r->total) }} {{ $satuanDari($r->bahanBaku) }}</td>
                                <td><span class="badge-info">{{ $r->jumlahBatch }} batch</span></td>
                                <td>{{ $fmt($r->bahanBaku->stok_saat_ini) }} {{ $satuanDari($r->bahanBaku) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── Rincian per batch (jejak FEFO) ── --}}
        <div class="card filter-card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="mb-0"><i class="fas fa-layer-group text-primary me-2"></i>Rincian Batch (Jejak FEFO)</h5>
                <span class="text-muted" style="font-size:.78rem;">Diurutkan berdasarkan tanggal penerimaan terlama</span>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="40">#</th>
                            <th>Bahan Baku</th>
                            <th>Batch Penerimaan</th>
                            <th>Tgl Terima</th>
                            <th>Kadaluarsa</th>
                            <th class="text-end">Jumlah Dipakai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $urut = $pemakaian->pemakaianBahanBakuDetails->sortBy([
                                fn ($a, $b) => $a->bahan_baku_id <=> $b->bahan_baku_id,
                                fn ($a, $b) => ($a->penerimaanBahanBakuDetail?->penerimaanBahanBaku?->tanggal_terima?->timestamp ?? 0)
                                            <=> ($b->penerimaanBahanBakuDetail?->penerimaanBahanBaku?->tanggal_terima?->timestamp ?? 0),
                                fn ($a, $b) => $a->id <=> $b->id,
                            ]);
                        @endphp
                        @forelse ($urut as $i => $d)
                            @php
                                $batch      = $d->penerimaanBahanBakuDetail;
                                $penerimaan = $batch?->penerimaanBahanBaku;
                                $exp        = $batch?->tanggal_kadaluarsa;
                            @endphp
                            <tr>
                                <td class="text-muted">{{ $loop->iteration }}</td>
                                <td class="name-text">{{ $d->bahanBaku->nama_bahan_baku }}</td>
                                <td>
                                    @if ($penerimaan)
                                        <a href="{{ route('penerimaan-bahan-baku.show', $penerimaan->id) }}" class="badge-kode text-decoration-none">
                                            {{ $penerimaan->nomor_transaksi }}
                                        </a>
                                        <div class="sub-text">{{ $penerimaan->produsen->nama_produsen ?? '' }}</div>
                                    @else
                                        <span class="text-muted">–</span>
                                    @endif
                                </td>
                                <td>{{ $penerimaan?->tanggal_terima?->format('d/m/Y') ?? '–' }}</td>
                                <td>
                                    @if ($exp)
                                        {{ $exp->format('d/m/Y') }}
                                        @if ($exp->lt(today()))
                                            <span class="badge-kritis" style="padding:2px 6px;font-size:.65rem;">Kadaluarsa</span>
                                        @endif
                                    @else
                                        –
                                    @endif
                                </td>
                                <td class="text-end fw-bold">{{ $fmt($d->jumlah_dipakai) }} {{ $satuanDari($d->bahanBaku) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada rincian.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.getElementById('btn-hapus').addEventListener('click', function () {
            swal({
                title: 'Hapus Pemakaian?',
                text: 'Transaksi {{ $pemakaian->nomor_transaksi }} akan dihapus permanen dan stok dikembalikan ke batch asal.',
                icon: 'warning',
                buttons: { cancel: 'Batal', confirm: { text: 'Ya, Hapus!', className: 'btn-danger' } },
                dangerMode: true,
            }).then(confirmed => {
                if (confirmed) document.getElementById('form-hapus').submit();
            });
        });
    </script>
@endsection
