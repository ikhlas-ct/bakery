@extends('layouts.user.user')

@section('title', 'Tanggapi Permintaan')

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
    <style>
        .qty-box { max-width: 260px; }
        .qty-actions { display: flex; gap: 6px; margin-top: 6px; }
    </style>
@endsection

@section('content')
@php
    $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, ',', '.'), '0'), ',');
    $badge = [
        'pending'            => ['chip-pending',   'Menunggu'],
        'disetujui'          => ['chip-disetujui', 'Disetujui'],
        'disetujui_sebagian' => ['chip-sebagian',  'Disetujui Sebagian'],
        'ditolak'            => ['chip-ditolak',   'Ditolak'],
    ];
    [$cls, $label] = $badge[$p->status_produsen_pengganti];
    $satuan  = $p->bahanBakuAsal->satuan->nama ?? '';
    $tujuan  = $p->bahan_baku_tujuan;
    $pending = $p->status_produsen_pengganti === 'pending';
@endphp
<div class="container">

    <div class="ph-card">
        <div class="ph-left">
            <div class="ph-icon"><i class="fas fa-reply"></i></div>
            <div>
                <h5 class="ph-title">Tanggapi Permintaan</h5>
                <ol class="ph-breadcrumb">
                    <li><a href="{{ route('produsen.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('produsen.permintaan.index') }}">Permintaan Masuk</a></li>
                    <li><span class="bc-active">{{ $p->permintaanBahanBakuDetail->permintaanBahanBaku->nomor_transaksi }}</span></li>
                </ol>
            </div>
        </div>
        <a href="{{ route('produsen.permintaan.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success d-flex align-items-center mb-3 gap-2"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger d-flex align-items-center mb-3 gap-2">
            <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="section-divider"><i class="fas fa-boxes-stacked me-2"></i>Rincian Permintaan</div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4 col-6"><div class="info-label">Nomor Transaksi</div>
                            <div class="info-value"><span class="badge-kode">{{ $p->permintaanBahanBakuDetail->permintaanBahanBaku->nomor_transaksi }}</span></div></div>
                        <div class="col-md-4 col-6"><div class="info-label">Tanggal</div>
                            <div class="info-value">{{ $p->tanggal->translatedFormat('d F Y') }}</div></div>
                        <div class="col-md-4 col-6"><div class="info-label">Status</div>
                            <div class="info-value"><span class="chip {{ $cls }}">{{ $label }}</span></div></div>
                        <div class="col-md-4 col-6"><div class="info-label">Bahan Baku</div>
                            <div class="info-value">{{ $tujuan->nama_bahan_baku ?? '-' }}
                                <div class="sub-text">{{ $tujuan->kode_bahan_baku ?? '' }}</div></div></div>
                        <div class="col-md-4 col-6"><div class="info-label">Harga Satuan</div>
                            <div class="info-value">Rp {{ number_format((float) $p->harga_satuan, 0, ',', '.') }}
                                <div class="sub-text">per {{ $satuan }}</div></div></div>
                        <div class="col-md-4 col-6"><div class="info-label">Jumlah Diminta</div>
                            <div class="info-value">{{ $fmt($p->jumlah_dialihkan) }} {{ $satuan }}</div></div>
                    </div>

                    @if ($pending)
                        <form id="form-respon" action="{{ route('produsen.permintaan-tambahan.respon', $p->id) }}" method="POST" novalidate>
                            @csrf
                            @method('PUT')

                            <label class="form-label">Jumlah yang sanggup Anda sediakan <span class="required-mark">*</span></label>
                            <div class="qty-box">
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="{{ $p->jumlah_dialihkan }}"
                                        name="jumlah_disetujui_pengganti" id="qty"
                                        class="form-control @error('jumlah_disetujui_pengganti') is-invalid @enderror"
                                        value="{{ old('jumlah_disetujui_pengganti') }}" placeholder="0.00">
                                    <span class="input-group-text">{{ $satuan }}</span>
                                    @error('jumlah_disetujui_pengganti')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="qty-actions">
                                    <button type="button" class="btn btn-outline-success btn-mini" id="btn-penuh"><i class="fas fa-check me-1"></i>Penuh</button>
                                    <button type="button" class="btn btn-outline-danger btn-mini" id="btn-tolak"><i class="fas fa-times me-1"></i>Tolak</button>
                                </div>
                            </div>
                            <div class="form-text" style="font-size:.75rem;">Isi 0 bila tidak sanggup. Tanggapan tidak dapat diubah setelah dikirim.</div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary flex-fill"><i class="fas fa-paper-plane me-2"></i> Kirim Tanggapan</button>
                                <a href="{{ route('produsen.permintaan.index') }}" class="btn btn-outline-secondary flex-fill">Batal</a>
                            </div>
                        </form>
                    @else
                        <div class="info-label">Tanggapan Anda</div>
                        <div class="info-value">{{ $fmt($p->jumlah_disetujui_pengganti) }} {{ $satuan }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const form = document.getElementById('form-respon');
    if (form) {
        const qty = document.getElementById('qty');
        document.getElementById('btn-penuh').addEventListener('click', () => qty.value = {{ (float) $p->jumlah_dialihkan }});
        document.getElementById('btn-tolak').addEventListener('click', () => qty.value = 0);

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (qty.value.trim() === '') {
                swal({ title: 'Belum diisi', text: 'Isi jumlah yang sanggup Anda sediakan (0 bila tidak sanggup).', icon: 'warning' });
                return;
            }
            swal({
                title: 'Kirim Tanggapan?',
                text: 'Tanggapan tidak dapat diubah setelah dikirim.',
                icon: 'info',
                buttons: { cancel: 'Periksa Lagi', confirm: { text: 'Ya, Kirim' } },
            }).then(ok => { if (ok) form.submit(); });
        });
    }
</script>
@endsection
