@extends('layouts.user.user')

@section('title', 'Dashboard Admin')

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
                <div class="ph-icon"><i class="fas fa-tachometer-alt"></i></div>
                <div>
                    <h5 class="ph-title">Halo, {{ $namaAdmin }}</h5>
                    <ol class="ph-breadcrumb mb-0">
                        <li><span class="bc-active">Dashboard</span></li>
                        <li><span class="bc-active">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span></li>
                    </ol>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('permintaan-bahan-baku.create') }}" class="btn btn-primary btn-quick">
                    <i class="fas fa-plus me-1"></i> Permintaan Baru
                </a>
                <a href="{{ route('penerimaan-bahan-baku.create') }}" class="btn btn-outline-primary btn-quick">
                    <i class="fas fa-truck-loading me-1"></i> Catat Penerimaan
                </a>
                <a href="{{ route('pemakaian-bahan-baku.create') }}" class="btn btn-outline-primary btn-quick">
                    <i class="fas fa-utensils me-1"></i> Catat Pemakaian
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
                            <div class="stat-value">{{ $totalBahanBaku }}</div>
                            <div class="stat-label">Total Bahan Baku</div>
                            <div class="stat-note">{{ $bahanBakuAktif }} aktif dipakai</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="stat-card red">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
                        <div>
                            <div class="stat-value">{{ $bahanBakuKritis }}</div>
                            <div class="stat-label">Stok Kritis</div>
                            <div class="stat-note">di bawah / sama dengan minimum</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="stat-card green">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon green"><i class="fas fa-industry"></i></div>
                        <div>
                            <div class="stat-value">{{ $produsenAktif }}</div>
                            <div class="stat-label">Produsen Aktif</div>
                            <div class="stat-note">mitra pemasok</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="stat-card purple">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon purple"><i class="fas fa-file-signature"></i></div>
                        <div>
                            <div class="stat-value">{{ $permintaanBulanIni }}</div>
                            <div class="stat-label">Permintaan Bulan Ini</div>
                            <div class="stat-note">{{ now()->locale('id')->translatedFormat('F Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="stat-card orange">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon orange"><i class="fas fa-random"></i></div>
                        <div>
                            <div class="stat-value">{{ $pengalihanPending }}</div>
                            <div class="stat-label">Pengalihan Pending</div>
                            <div class="stat-note">menunggu tindak lanjut admin</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="stat-card teal">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon teal"><i class="fas fa-wallet"></i></div>
                        <div>
                            <div class="stat-value sm">{{ $rupiah($tagihanBelumLunas) }}</div>
                            <div class="stat-label">Belum Lunas</div>
                            <div class="stat-note">{{ $jumlahTagihanBelumLunas }} pembayaran</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Baris 1: Permintaan terbaru + Stok kritis ── --}}
        <div class="row g-3 mb-3">

            {{-- Permintaan terbaru --}}
            <div class="col-lg-7">
                <div class="card filter-card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="mb-0"><i class="fas fa-file-signature text-primary me-2"></i>Permintaan Terbaru</h5>
                        <a href="{{ route('permintaan-bahan-baku.index') }}" class="link-semua">Lihat semua →</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Transaksi</th>
                                    <th>Produsen</th>
                                    <th>Nilai</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($permintaanTerbaru as $permintaan)
                                    <tr>
                                        <td>
                                            <a href="{{ route('permintaan-bahan-baku.show', $permintaan->id) }}"
                                                class="text-decoration-none">
                                                <span class="badge-kode">{{ $permintaan->nomor_transaksi }}</span>
                                            </a>
                                            <div class="sub-text mt-1">
                                                {{ $permintaan->tanggal_permintaan->format('d/m/Y') }}
                                                · {{ $permintaan->permintaanBahanBakuDetails->count() }} item
                                            </div>
                                        </td>
                                        <td>
                                            <div class="name-text">{{ $permintaan->daftar_produsen->first() ?? '-' }}</div>
                                            @if ($permintaan->daftar_produsen->count() > 1)
                                                <div class="sub-text">+{{ $permintaan->daftar_produsen->count() - 1 }} produsen lain</div>
                                            @endif
                                        </td>
                                        <td class="fw-semibold">{{ $rupiah($permintaan->total_nilai) }}</td>
                                        <td>
                                            <span class="badge-{{ $permintaan->status_kelas }}">{{ $permintaan->status_label }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="p-0">
                                            <div class="empty-state">
                                                <div class="empty-state-icon"><i class="fas fa-file-signature"></i></div>
                                                <div class="fw-semibold text-secondary mb-1">Belum ada permintaan</div>
                                                <div class="text-muted" style="font-size:.8rem;">
                                                    Buat permintaan bahan baku pertama ke produsen
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

            {{-- Stok kritis --}}
            <div class="col-lg-5">
                <div class="card filter-card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="mb-0"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Stok Kritis</h5>
                        <a href="{{ route('bahan-baku.index', ['stok' => 'kritis']) }}" class="link-semua">Lihat semua →</a>
                    </div>

                    @forelse ($stokKritis as $bahan)
                        @php
                            $persen = $bahan->stok_minimum > 0
                                ? min(100, max(4, ($bahan->stok_saat_ini / $bahan->stok_minimum) * 100))
                                : 4;
                        @endphp
                        <div class="list-item">
                            <div class="flex-grow-1">
                                <div class="name-text">{{ $bahan->nama_bahan_baku }}</div>
                                <span class="badge-kode">{{ $bahan->kode_bahan_baku }}</span>
                                <div class="stok-bar"><span style="width: {{ $persen }}%"></span></div>
                            </div>
                            <div class="text-end">
                                <div class="text-danger fw-bold" style="font-size:.9rem;">
                                    {{ $angka($bahan->stok_saat_ini) }} {{ $bahan->satuan->nama ?? '' }}
                                </div>
                                <div class="sub-text">min. {{ $angka($bahan->stok_minimum) }}</div>
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
        </div>

        {{-- ── Baris 2: Batch kedaluwarsa + Pengalihan pending ── --}}
        <div class="row g-3 mb-4">

            {{-- Batch mendekati kedaluwarsa --}}
            <div class="col-lg-6">
                <div class="card filter-card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="mb-0"><i class="fas fa-hourglass-half text-warning me-2"></i>Mendekati Kedaluwarsa</h5>
                        <span class="text-muted" style="font-size:.78rem;">
                            ≤ {{ $batasHariKadaluarsa }} hari · {{ $totalBatchKadaluarsa }} batch
                        </span>
                    </div>

                    @forelse ($batchKadaluarsa as $batch)
                        <a href="{{ route('penerimaan-bahan-baku.show', $batch->penerimaan_bahan_baku_id) }}"
                            class="list-item text-decoration-none">
                            <div>
                                <div class="name-text">{{ $batch->bahanBaku->nama_bahan_baku ?? '-' }}</div>
                                <div class="sub-text">
                                    {{ $batch->penerimaanBahanBaku->nomor_transaksi ?? '-' }}
                                    · {{ $batch->penerimaanBahanBaku->produsen->nama_produsen ?? '-' }}
                                </div>
                            </div>
                            <div class="text-end">
                                @if ($batch->sisa_hari < 0)
                                    <span class="badge-kritis">Kedaluwarsa</span>
                                @elseif ($batch->sisa_hari <= 7)
                                    <span class="badge-kritis">{{ $batch->sisa_hari }} hari lagi</span>
                                @else
                                    <span class="badge-warn">{{ $batch->sisa_hari }} hari lagi</span>
                                @endif
                                <div class="sub-text mt-1">
                                    sisa {{ $angka($batch->jumlah_tersisa) }} {{ $batch->bahanBaku->satuan->nama ?? '' }}
                                    · {{ $batch->tanggal_kadaluarsa->format('d/m/Y') }}
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="empty-state">
                            <div class="empty-state-icon ok"><i class="fas fa-check"></i></div>
                            <div class="fw-semibold text-secondary mb-1">Tidak ada batch yang mendekati kedaluwarsa</div>
                            <div class="text-muted" style="font-size:.8rem;">Dalam {{ $batasHariKadaluarsa }} hari ke depan</div>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Pengalihan pending --}}
            <div class="col-lg-6">
                <div class="card filter-card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="mb-0"><i class="fas fa-random text-primary me-2"></i>Pengalihan Pending</h5>
                        <a href="{{ route('pengalihan-bahan-baku.index') }}" class="link-semua">Lihat semua →</a>
                    </div>

                    @forelse ($pengalihanMenunggu as $pengalihan)
                        @php
                            $detail = $pengalihan->permintaanBahanBakuDetail;
                            $tujuan = $pengalihan->bahan_baku_tujuan;
                            $sudahDitentukan = (bool) $pengalihan->produsen_pengganti_id;
                        @endphp
                        <a href="{{ route('pengalihan-bahan-baku.edit', $pengalihan->id) }}"
                            class="list-item text-decoration-none">
                            <div>
                                <div class="name-text">{{ $pengalihan->bahanBakuAsal->nama_bahan_baku ?? '-' }}</div>
                                <div class="sub-text">
                                    {{ $detail?->permintaanBahanBaku?->nomor_transaksi ?? '-' }}
                                    · asal {{ $detail?->produsen?->nama_produsen ?? '-' }}
                                </div>
                                <div class="sub-text">
                                    @if ($sudahDitentukan)
                                        Pengganti: {{ $pengalihan->produsenPengganti->nama_produsen ?? '-' }}
                                        @if ($pengalihan->bahan_baku_pengganti_id)
                                            ({{ $tujuan->nama_bahan_baku ?? '-' }})
                                        @endif
                                    @else
                                        <span class="text-danger fw-semibold">Pengganti belum ditentukan</span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="{{ $sudahDitentukan ? 'badge-pending' : 'badge-warn' }}">
                                    {{ $sudahDitentukan ? 'Menunggu respon' : 'Perlu tindakan' }}
                                </span>
                                <div class="sub-text mt-1">
                                    {{ $angka($pengalihan->jumlah_dialihkan) }}
                                    {{ $pengalihan->bahanBakuAsal->satuan->nama ?? '' }}
                                    · {{ $pengalihan->tanggal->format('d/m/Y') }}
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="empty-state">
                            <div class="empty-state-icon ok"><i class="fas fa-check"></i></div>
                            <div class="fw-semibold text-secondary mb-1">Tidak ada pengalihan pending</div>
                            <div class="text-muted" style="font-size:.8rem;">Semua pengalihan sudah ditentukan dan direspon</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
@endsection
