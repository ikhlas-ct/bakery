@extends('layouts.user.user')

@section('title', 'Dashboard Produsen')

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

        /* ===== LIST ITEM (stok kritis, batch, pengalihan) ===== */
        .list-item {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 14px 24px; border-bottom: 1px solid #f4f6f8;
        }
        .list-item:last-child { border-bottom: none; }
        .list-item:hover { background: #fafbfc; }

        .stok-bar { height: 6px; border-radius: 99px; background: #f1f5f9; overflow: hidden; margin-top: 6px; width: 100%; min-width: 110px; }
        .stok-bar > span { display: block; height: 100%; border-radius: 99px; background: #dc2626; }

        /* ===== QUICK ACTION ===== */
        .btn-quick {
            border-radius: 10px; font-size: .8rem; font-weight: 600; padding: 7px 14px;
        }

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
        $angka = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, ',', '.'), '0'), ',');
        $rupiah = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    @endphp

    <div class="container">

        {{-- ── Page Header ── --}}
        <div class="ph-card mb-4">
            <div class="ph-left">
                <div class="ph-icon"><i class="fas fa-industry"></i></div>
                <div>
                    <h5 class="ph-title">Halo, {{ $produsen->nama_produsen }}</h5>
                    <ol class="ph-breadcrumb mb-0">
                        <li><span class="bc-active">Dashboard</span></li>
                        <li><span class="bc-active">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span></li>
                    </ol>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap align-items-center">
                <span class="{{ $produsen->status_mitra === 'aktif' ? 'badge-aman' : 'badge-kritis' }}">
                    Mitra {{ $produsen->status_mitra === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                </span>
                <a href="{{ route('produsen.permintaan.index') }}" class="btn btn-primary btn-quick">
                    <i class="fas fa-inbox me-1"></i> Permintaan Masuk
                </a>
                <a href="{{ route('produsen.bahan-baku.create') }}" class="btn btn-outline-primary btn-quick">
                    <i class="fas fa-plus me-1"></i> Tambah Bahan Baku
                </a>
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
                <div class="stat-card blue">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon blue"><i class="fas fa-boxes"></i></div>
                        <div>
                            <div class="stat-value">{{ $jumlahBahanBaku }}</div>
                            <div class="stat-label">Bahan Baku Saya</div>
                            <div class="stat-note">yang saya sediakan</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="stat-card orange">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon orange"><i class="fas fa-inbox"></i></div>
                        <div>
                            <div class="stat-value">{{ $jumlahPermintaanMenunggu }}</div>
                            <div class="stat-label">Perlu Direspon</div>
                            <div class="stat-note">permintaan masuk baru</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="stat-card purple">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon purple"><i class="fas fa-random"></i></div>
                        <div>
                            <div class="stat-value">{{ $jumlahPermintaanTambahan }}</div>
                            <div class="stat-label">Permintaan Tambahan</div>
                            <div class="stat-note">menunggu tanggapan saya</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="stat-card teal">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon teal"><i class="fas fa-truck-loading"></i></div>
                        <div>
                            <div class="stat-value">{{ $jumlahMenungguPengiriman }}</div>
                            <div class="stat-label">Menunggu Pengiriman</div>
                            <div class="stat-note">item disetujui, belum terkirim penuh</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="stat-card red">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon red"><i class="fas fa-wallet"></i></div>
                        <div>
                            <div class="stat-value sm">{{ $rupiah($belumDibayar) }}</div>
                            <div class="stat-label">Belum Dibayar</div>
                            <div class="stat-note">tagihan ke Roti Baru</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="stat-card green">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                        <div>
                            <div class="stat-value sm">{{ $rupiah($sudahDibayar) }}</div>
                            <div class="stat-label">Sudah Dibayar</div>
                            <div class="stat-note">pembayaran lunas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Baris 1: Permintaan masuk + Permintaan tambahan ── --}}
        <div class="row g-3 mb-3">

            {{-- Permintaan masuk --}}
            <div class="col-lg-7">
                <div class="card filter-card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="mb-0"><i class="fas fa-inbox text-primary me-2"></i>Permintaan Masuk Perlu Direspon</h5>
                        <a href="{{ route('produsen.permintaan.index') }}" class="link-semua">Lihat semua →</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Transaksi</th>
                                    <th>Bahan Baku</th>
                                    <th>Nilai</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($permintaanMasuk as $permintaan)
                                    <tr>
                                        <td>
                                            <span class="badge-kode">{{ $permintaan->nomor_transaksi }}</span>
                                            <div class="sub-text mt-1">{{ $permintaan->tanggal_permintaan->format('d/m/Y') }}</div>
                                        </td>
                                        <td>
                                            <div class="name-text">{{ $permintaan->nama_bahan->first() ?? '-' }}</div>
                                            @if ($permintaan->total_item > 1)
                                                <div class="sub-text">+{{ $permintaan->total_item - 1 }} item lain</div>
                                            @endif
                                        </td>
                                        <td class="fw-semibold">{{ $rupiah($permintaan->total_nilai) }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('produsen.permintaan.show', $permintaan->id) }}"
                                                class="btn btn-sm btn-primary btn-quick">Respon</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="p-0">
                                            <div class="empty-state">
                                                <div class="empty-state-icon ok"><i class="fas fa-check"></i></div>
                                                <div class="fw-semibold text-secondary mb-1">Tidak ada permintaan yang menunggu</div>
                                                <div class="text-muted" style="font-size:.8rem;">Semua permintaan masuk sudah Anda respon</div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Permintaan tambahan --}}
            <div class="col-lg-5">
                <div class="card filter-card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="mb-0"><i class="fas fa-random text-primary me-2"></i>Permintaan Tambahan</h5>
                        <a href="{{ route('produsen.permintaan-tambahan.index') }}" class="link-semua">Lihat semua →</a>
                    </div>

                    @forelse ($permintaanTambahan as $pengalihan)
                        @php $tujuan = $pengalihan->bahan_baku_tujuan; @endphp
                        <a href="{{ route('produsen.permintaan-tambahan.show', $pengalihan->id) }}"
                            class="list-item text-decoration-none">
                            <div>
                                <div class="name-text">{{ $tujuan->nama_bahan_baku ?? '-' }}</div>
                                <div class="sub-text">
                                    {{ $pengalihan->permintaanBahanBakuDetail?->permintaanBahanBaku?->nomor_transaksi ?? '-' }}
                                    @if ($pengalihan->bahan_baku_pengganti_id)
                                        · pengganti {{ $pengalihan->bahanBakuAsal->nama_bahan_baku ?? '' }}
                                    @endif
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge-pending">Menunggu tanggapan</span>
                                <div class="sub-text mt-1">
                                    {{ $angka($pengalihan->jumlah_dialihkan) }} {{ $tujuan->satuan->nama ?? '' }}
                                    · {{ $rupiah($pengalihan->harga_satuan) }}/sat.
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="empty-state">
                            <div class="empty-state-icon ok"><i class="fas fa-check"></i></div>
                            <div class="fw-semibold text-secondary mb-1">Tidak ada permintaan tambahan</div>
                            <div class="text-muted" style="font-size:.8rem;">Belum ada pengalihan yang ditujukan ke Anda</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ── Baris 2: Menunggu pengiriman + Pembayaran ── --}}
        <div class="row g-3 mb-3">

            {{-- Menunggu pengiriman --}}
            <div class="col-lg-6">
                <div class="card filter-card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="mb-0"><i class="fas fa-truck-loading text-info me-2"></i>Menunggu Pengiriman</h5>
                        <span class="text-muted" style="font-size:.78rem;">{{ $jumlahMenungguPengiriman }} item</span>
                    </div>

                    @forelse ($menungguPengiriman as $item)
                        <div class="list-item">
                            <div>
                                <div class="name-text">{{ $item->bahanBaku->nama_bahan_baku ?? '-' }}</div>
                                <div class="sub-text">
                                    {{ $item->permintaanBahanBaku->nomor_transaksi ?? '-' }}
                                    · disetujui {{ $angka($item->jumlah_final) }} {{ $item->bahanBaku->satuan->nama ?? '' }}
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge-warn">Sisa {{ $angka($item->sisa_kirim) }} {{ $item->bahanBaku->satuan->nama ?? '' }}</span>
                                <div class="sub-text mt-1">terkirim {{ $angka($item->total_diterima ?? 0) }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <div class="empty-state-icon ok"><i class="fas fa-check"></i></div>
                            <div class="fw-semibold text-secondary mb-1">Tidak ada pengiriman tertunda</div>
                            <div class="text-muted" style="font-size:.8rem;">Semua item yang disetujui sudah diterima</div>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Pembayaran terbaru --}}
            <div class="col-lg-6">
                <div class="card filter-card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="mb-0"><i class="fas fa-wallet text-success me-2"></i>Pembayaran Terbaru</h5>
                        <a href="{{ route('produsen.pembayaran.index') }}" class="link-semua">Lihat semua →</a>
                    </div>

                    @forelse ($pembayaranTerbaru as $bayar)
                        <div class="list-item">
                            <div>
                                <div class="name-text">{{ $rupiah($bayar->jumlah_bayar) }}</div>
                                <div class="sub-text">
                                    {{ $bayar->permintaanBahanBaku->nomor_transaksi ?? '-' }}
                                    @if ($bayar->tanggal_bayar)
                                        · dibayar {{ $bayar->tanggal_bayar->format('d/m/Y') }}
                                    @endif
                                </div>
                            </div>
                            <div class="text-end">
                                @if ($bayar->status_bayar === 'lunas')
                                    <span class="badge-disetujui">Lunas</span>
                                @elseif ($bayar->status_acc === 'ditolak')
                                    <span class="badge-ditolak">Ditolak</span>
                                @elseif ($bayar->status_acc === 'disetujui')
                                    <span class="badge-sebagian">Disetujui, belum dibayar</span>
                                @else
                                    <span class="badge-pending">Menunggu persetujuan</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <div class="empty-state-icon"><i class="fas fa-wallet"></i></div>
                            <div class="fw-semibold text-secondary mb-1">Belum ada pembayaran</div>
                            <div class="text-muted" style="font-size:.8rem;">Tagihan muncul setelah barang diterima admin</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ── Bahan baku yang saya sediakan ── --}}
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card filter-card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="mb-0"><i class="fas fa-boxes text-primary me-2"></i>Bahan Baku yang Saya Sediakan</h5>
                        <a href="{{ route('produsen.bahan-baku.index') }}" class="link-semua">Kelola →</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Bahan Baku</th>
                                    <th>Kode</th>
                                    <th>Satuan</th>
                                    <th class="text-end">Harga</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($bahanBakuSaya as $bahan)
                                    <tr>
                                        <td class="name-text">{{ $bahan->nama_bahan_baku }}</td>
                                        <td><span class="badge-kode">{{ $bahan->kode_bahan_baku }}</span></td>
                                        <td>{{ $bahan->satuan->nama ?? '-' }}</td>
                                        <td class="text-end fw-semibold">{{ $rupiah($bahan->pivot->harga) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="p-0">
                                            <div class="empty-state">
                                                <div class="empty-state-icon"><i class="fas fa-boxes"></i></div>
                                                <div class="fw-semibold text-secondary mb-1">Belum ada bahan baku</div>
                                                <div class="text-muted" style="font-size:.8rem;">
                                                    Tambahkan bahan baku yang bisa Anda sediakan beserta harganya
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
