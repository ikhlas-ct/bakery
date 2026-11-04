@extends('layouts.user.user')

@section('title', 'Detail Pengalihan')

@section('styles')
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
    body, .card, .table, .btn, h4, h5 { font-family: 'Plus Jakarta Sans', sans-serif; }

    .stat-card { border: none; border-radius: 16px; padding: 20px; position: relative; overflow: hidden; transition: transform .2s ease, box-shadow .2s ease; box-shadow: 0 2px 12px rgba(0,0,0,.07); }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.12); }
    .stat-card::after { content: ''; position: absolute; right: -18px; top: -18px; width: 80px; height: 80px; border-radius: 50%; opacity: .12; }
    .stat-card.orange { background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); } .stat-card.orange::after { background: #ea580c; }
    .stat-card.green  { background: linear-gradient(135deg, #e6f9f0 0%, #d1fae5 100%); } .stat-card.green::after  { background: #16a34a; }
    .stat-card.blue   { background: linear-gradient(135deg, #e8f0fe 0%, #dbeafe 100%); } .stat-card.blue::after   { background: #1a73e8; }
    .stat-card.red    { background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); } .stat-card.red::after    { background: #dc2626; }
    .stat-card.slate  { background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); } .stat-card.slate::after  { background: #64748b; }
    .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; color: #fff; }
    .stat-icon.orange { background: #ea580c; } .stat-icon.green { background: #16a34a; } .stat-icon.blue { background: #1a73e8; } .stat-icon.red { background: #dc2626; } .stat-icon.slate { background: #64748b; }
    .stat-value { font-size: 1.85rem; font-weight: 800; line-height: 1; color: #1e293b; }
    .stat-label { font-size: .78rem; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; color: #64748b; margin-top: 3px; }

    .ph-card { background: #fff; border: 1px solid #e9ecef; border-radius: 14px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 1.25rem; position: relative; overflow: hidden; box-shadow: 0 1px 6px rgba(0,0,0,.05); }
    .ph-card::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; border-radius: 14px 0 0 14px; background: #1a73e8; }
    .ph-left { display: flex; align-items: center; gap: 12px; }
    .ph-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; background: #dbeafe; color: #1a73e8; }
    .ph-title { font-size: 1.15rem; font-weight: 800; color: #1e293b; margin: 0; }
    .ph-breadcrumb { list-style: none; display: flex; gap: 6px; padding: 0; margin: 2px 0 0; }
    .ph-breadcrumb li:not(:last-child)::after { content: '/'; margin-left: 6px; color: #cbd5e1; }
    .ph-breadcrumb a { color: #1a73e8; text-decoration: none; font-size: .78rem; }
    .ph-breadcrumb .bc-active { font-size: .78rem; color: #94a3b8; }

    .section-divider { background: #f8f9fa; border-left: 4px solid #1a73e8; padding: 8px 14px; border-radius: 0 6px 6px 0; font-weight: 600; font-size: .9rem; color: #1a73e8; margin-bottom: 1rem; }
    .info-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: #94a3b8; }
    .info-value { font-size: .9rem; font-weight: 600; color: #1e293b; }
    .required-mark { color: #dc3545; }
    label { font-size: .875rem; font-weight: 500; }

    .filter-card { border: none; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,.07); overflow: hidden; }
    .filter-card .card-header { background: #fff; border-bottom: 1px solid #f1f5f9; padding: 18px 24px; }
    .filter-card .card-header h5 { font-size: .95rem; font-weight: 700; color: #1e293b; }
    .filter-section { background: #fafbfc; border-bottom: 1px solid #f1f5f9; padding: 16px 24px; }

    .form-control, .form-select { border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: .85rem; padding: 8px 12px; color: #334155; background-color: #f8fafc; transition: border-color .2s, box-shadow .2s; }
    .form-control:focus, .form-select:focus { border-color: #1a73e8; background: #fff; box-shadow: 0 0 0 3px rgba(26,115,232,.12); }
    .form-control::placeholder { color: #94a3b8; }

    .table { font-size: .83rem; margin-bottom: 0; }
    .table thead th { background: #f8fafc; color: #475569; font-weight: 700; font-size: .72rem; text-transform: uppercase; letter-spacing: .4px; border-bottom: 1.5px solid #eef2f6; padding: 12px 16px; white-space: nowrap; }
    .table tbody td { padding: 12px 16px; vertical-align: middle; border-bottom: 1px solid #f4f6f8; }
    .table tbody tr:hover { background: #fafbfc; }
    .name-text { font-weight: 700; color: #1e293b; font-size: .85rem; }
    .sub-text { color: #94a3b8; font-size: .74rem; }

    .badge-kode { background: #eef2ff; color: #4338ca; font-weight: 600; font-size: .72rem; padding: 5px 10px; border-radius: 8px; }
    .chip { font-weight: 600; font-size: .72rem; padding: 5px 10px; border-radius: 8px; display: inline-block; }
    .chip-belum     { background: #f1f5f9; color: #475569; }
    .chip-pending   { background: #fff7ed; color: #c2410c; }
    .chip-disetujui { background: #f0fdf4; color: #166534; }
    .chip-sebagian  { background: #eff6ff; color: #1d4ed8; }
    .chip-ditolak   { background: #fef2f2; color: #dc2626; }

    .btn-action { width: 32px; height: 32px; border-radius: 8px; border: none; display: inline-flex; align-items: center; justify-content: center; font-size: .78rem; transition: all .15s; }
    .btn-lihat { background: #eff6ff; color: #1d4ed8; } .btn-lihat:hover { background: #1d4ed8; color: #fff; }
    .btn-edit  { background: #fff7ed; color: #c2410c; } .btn-edit:hover  { background: #c2410c; color: #fff; }
    .btn-hapus { background: #fef2f2; color: #dc2626; } .btn-hapus:hover { background: #dc2626; color: #fff; }
    .btn-mini { font-size: .7rem; font-weight: 600; padding: 3px 10px; border-radius: 8px; }

    .empty-state { padding: 48px 24px; }
    .empty-state-icon { width: 64px; height: 64px; border-radius: 16px; background: #f1f5f9; color: #94a3b8; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 16px; }

    .alert { border-radius: 12px; border: none; font-size: .85rem; }
    .alert-success { background: #dcfce7; color: #166534; }
    .alert-danger  { background: #fee2e2; color: #991b1b; }
    .alert-warning { background: #fff7ed; color: #9a3412; }
</style>
@endsection

@section('content')
@php
    $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, ',', '.'), '0'), ',');
    $header = $detail->permintaanBahanBaku;
    $satuan = $p->bahanBakuAsal->satuan->nama ?? '';
    $belum  = is_null($p->produsen_pengganti_id);
    [$cls, $label] = $belum
        ? ['chip-belum', 'Belum Ditentukan']
        : [
            'pending'            => ['chip-pending',   'Menunggu Respon'],
            'disetujui'          => ['chip-disetujui', 'Disetujui'],
            'disetujui_sebagian' => ['chip-sebagian',  'Disetujui Sebagian'],
            'ditolak'            => ['chip-ditolak',   'Ditolak'],
        ][$p->status_produsen_pengganti];
    $sisaBelumTerpenuhi = in_array($p->status_produsen_pengganti, ['disetujui', 'disetujui_sebagian', 'ditolak'], true)
        ? max(0, (float) $p->jumlah_dialihkan - (float) $p->jumlah_disetujui_pengganti) : null;
@endphp
<div class="container">

    <div class="ph-card">
        <div class="ph-left">
            <div class="ph-icon"><i class="fas fa-random"></i></div>
            <div>
                <h5 class="ph-title">Detail Pengalihan</h5>
                <ol class="ph-breadcrumb">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('pengalihan-bahan-baku.index') }}">Pengalihan Bahan Baku</a></li>
                    <li><span class="bc-active">Detail</span></li>
                </ol>
            </div>
        </div>
        <div class="d-flex gap-2">
            @if ($bisaDiubah)
                <a href="{{ route('pengalihan-bahan-baku.edit', $p->id) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-pencil-alt me-1"></i> {{ $belum ? 'Tentukan Pengganti' : 'Ubah Pengganti' }}
                </a>
            @endif
            <a href="{{ route('pengalihan-bahan-baku.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success d-flex align-items-center mb-3 gap-2"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger d-flex align-items-center mb-3 gap-2"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    <div class="row g-3">
        {{-- Item permintaan asal --}}
        <div class="col-lg-6">
            <div class="card shadow-sm h-100"><div class="card-body">
                <div class="section-divider"><i class="fas fa-file-lines me-2"></i>Permintaan Asal</div>
                <div class="row g-3">
                    <div class="col-6"><div class="info-label">Nomor Transaksi</div>
                        <div class="info-value"><span class="badge-kode">{{ $header->nomor_transaksi }}</span></div></div>
                    <div class="col-6"><div class="info-label">Tanggal Permintaan</div>
                        <div class="info-value">{{ $header->tanggal_permintaan->translatedFormat('d F Y') }}</div></div>
                    <div class="col-6"><div class="info-label">Bahan Baku</div>
                        <div class="info-value">{{ $p->bahanBakuAsal->nama_bahan_baku }}</div></div>
                    <div class="col-6"><div class="info-label">Produsen Asal</div>
                        <div class="info-value">{{ $detail->produsen->nama_produsen ?? '-' }}</div></div>
                    <div class="col-4"><div class="info-label">Diminta</div>
                        <div class="info-value">{{ $fmt($detail->jumlah_diminta) }} {{ $satuan }}</div></div>
                    <div class="col-4"><div class="info-label">Disetujui</div>
                        <div class="info-value">{{ $fmt($detail->jumlah_disetujui) }} {{ $satuan }}</div></div>
                    <div class="col-4"><div class="info-label">Kekurangan</div>
                        <div class="info-value text-danger">{{ $fmt($kekurangan) }} {{ $satuan }}</div></div>
                </div>
            </div></div>
        </div>

        {{-- Pengalihan --}}
        <div class="col-lg-6">
            <div class="card shadow-sm h-100"><div class="card-body">
                <div class="section-divider"><i class="fas fa-random me-2"></i>Pengalihan</div>
                <div class="row g-3">
                    <div class="col-6"><div class="info-label">Status</div>
                        <div class="info-value"><span class="chip {{ $cls }}">{{ $label }}</span></div></div>
                    <div class="col-6"><div class="info-label">Tanggal</div>
                        <div class="info-value">{{ $p->tanggal->translatedFormat('d F Y') }}</div></div>
                    <div class="col-6"><div class="info-label">Produsen Pengganti</div>
                        <div class="info-value">{{ $belum ? '—' : ($p->produsenPengganti->nama_produsen ?? '-') }}</div></div>
                    <div class="col-6"><div class="info-label">Bahan Baku Pengganti</div>
                        <div class="info-value">
                            @if ($belum) — @else {{ $p->bahan_baku_pengganti_id ? $p->bahanBakuPengganti->nama_bahan_baku : 'Sama dengan asal' }} @endif
                        </div></div>
                    <div class="col-4"><div class="info-label">Dialihkan</div>
                        <div class="info-value">{{ $fmt($p->jumlah_dialihkan) }} {{ $satuan }}</div></div>
                    <div class="col-4"><div class="info-label">Harga Satuan</div>
                        <div class="info-value">{{ $p->harga_satuan !== null ? 'Rp ' . number_format($p->harga_satuan, 0, ',', '.') : '—' }}</div></div>
                    <div class="col-4"><div class="info-label">Disetujui Pengganti</div>
                        <div class="info-value">{{ $p->jumlah_disetujui_pengganti !== null ? $fmt($p->jumlah_disetujui_pengganti) . ' ' . $satuan : '—' }}</div></div>
                    <div class="col-12"><div class="info-label">Alasan</div>
                        <div class="info-value" style="font-weight:500;">{{ $p->alasan_pengalihan }}</div></div>
                </div>
            </div></div>
        </div>
    </div>

    @if ($sisaBelumTerpenuhi)
        <div class="alert alert-warning d-flex align-items-start gap-2 mt-3">
            <i class="fas fa-triangle-exclamation mt-1"></i>
            <div>
                Masih ada <strong>{{ $fmt($sisaBelumTerpenuhi) }} {{ $satuan }}</strong> yang belum terpenuhi.
                @if ($p->status_produsen_pengganti === 'ditolak')
                    Pengganti menolak; Anda bisa memilih pengganti lain lewat tombol <em>Ubah Pengganti</em>.
                @else
                    Pengganti hanya menyetujui sebagian; sisanya bisa dibuatkan permintaan baru.
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
