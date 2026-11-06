@extends('layouts.user.user')

@section('title', 'Detail Penerimaan – ' . $penerimaan->nomor_transaksi)

@section('styles')
<style>
    .section-divider {
        background: #f8f9fa;
        border-left: 4px solid #1a73e8;
        padding: 8px 14px;
        border-radius: 0 6px 6px 0;
        font-weight: 600;
        font-size: .9rem;
        color: #1a73e8;
        margin-bottom: 1rem;
    }

    .ph-card {
        background: #fff; border: 1px solid #e9ecef; border-radius: 14px;
        padding: 16px 20px; display: flex; align-items: center;
        justify-content: space-between; gap: 16px; flex-wrap: wrap;
        margin-bottom: 1.25rem; position: relative; overflow: hidden;
        box-shadow: 0 1px 6px rgba(0,0,0,.05);
    }
    .ph-card::before {
        content: ''; position: absolute;
        left: 0; top: 0; bottom: 0; width: 4px;
        border-radius: 14px 0 0 14px; background: #1a73e8;
    }
    .ph-left { display: flex; align-items: center; gap: 12px; }
    .ph-icon {
        width: 42px; height: 42px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; flex-shrink: 0;
        background: #dbeafe; color: #1a73e8;
    }
    .ph-title { font-size: 1.15rem; font-weight: 800; color: #1e293b; margin: 0; }
    .ph-breadcrumb { list-style: none; display: flex; gap: 6px; padding: 0; margin: 2px 0 0; }
    .ph-breadcrumb li:not(:last-child)::after { content: '/'; margin-left: 6px; color: #cbd5e1; }
    .ph-breadcrumb a { color: #1a73e8; text-decoration: none; font-size: .78rem; }
    .ph-breadcrumb a:hover { text-decoration: underline; }
    .ph-breadcrumb .bc-active { font-size: .75rem; color: #94a3b8; }

    .info-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: #94a3b8; }
    .info-value { font-size: .9rem; font-weight: 600; color: #1e293b; }

    .table-item { font-size: .83rem; margin-bottom: 0; }
    .table-item thead th {
        background: #f8fafc; color: #475569; font-weight: 700;
        font-size: .72rem; text-transform: uppercase; letter-spacing: .4px;
        border-bottom: 1.5px solid #eef2f6; padding: 10px 12px; white-space: nowrap;
    }
    .table-item tbody td { padding: 10px 12px; vertical-align: middle; border-bottom: 1px solid #f4f6f8; }
    .table-item tfoot td { padding: 10px 12px; font-weight: 800; color: #1e293b; background: #f8fafc; }

    .badge-kode {
        background: #eef2ff; color: #4338ca; font-weight: 600;
        font-size: .72rem; padding: 5px 10px; border-radius: 8px;
    }
    .badge-status { font-weight: 600; font-size: .72rem; padding: 5px 10px; border-radius: 8px; }
    .bs-hijau { background: #f0fdf4; color: #166534; }
    .bs-kuning { background: #fef3c7; color: #92400e; }
    .bs-merah { background: #fef2f2; color: #dc2626; }
    .name-text { font-weight: 700; color: #1e293b; font-size: .85rem; }
    .sub-text { color: #94a3b8; font-size: .74rem; }

    .pay-card {
        border: 1px solid #e9ecef; border-radius: 12px; padding: 14px 16px; background: #fafbfc;
    }
    .pay-amount { font-size: 1.15rem; font-weight: 800; color: #1e293b; }

    .btn-cetak-receipt { background: #16a34a; border-color: #16a34a; color: #fff; }
    .btn-cetak-receipt:hover { background: #15803d; border-color: #15803d; color: #fff; }

    .alert { border-radius: 12px; border: none; font-size: .85rem; }
    .alert-success { background: #dcfce7; color: #166534; }
    .alert-danger { background: #fee2e2; color: #991b1b; }
    .alert-warning { background: #fef3c7; color: #92400e; }
</style>
@endsection

@section('content')
@php
    $dashboardRoute = auth()->user()->role === 'admin' ? route('admin.dashboard') : route('pemilik.dashboard');
    $angka = fn ($v) => rtrim(rtrim(number_format($v, 2, ',', '.'), '0'), ',');
    $rupiah = fn ($v) => 'Rp ' . number_format($v, 0, ',', '.');
    $totalNota = 0;
@endphp
<div class="container">

    {{-- ── Page Header ── --}}
    <div class="ph-card">
        <div class="ph-left">
            <div class="ph-icon"><i class="fas fa-truck-loading"></i></div>
            <div>
                <h5 class="ph-title">Detail Penerimaan {{ $penerimaan->nomor_transaksi }}</h5>
                <ol class="ph-breadcrumb">
                    <li><a href="{{ $dashboardRoute }}">Dashboard</a></li>
                    <li><a href="{{ route('penerimaan-bahan-baku.index') }}">Penerimaan Bahan Baku</a></li>
                    <li><span class="bc-active">Detail</span></li>
                </ol>
            </div>
        </div>
        <div class="d-flex gap-2">
            {{-- Satu receipt untuk seluruh tagihan lunas pada penerimaan ini --}}
            @php
                $adaLunas = collect($tagihans)->contains(fn ($t) => ($t['pembayaran']->status_bayar ?? null) === 'lunas');
            @endphp
            @if ($adaLunas)
                <a href="{{ route('laporan.receipt-pembayaran.show', ['penerimaan' => $penerimaan->id, 'cetak' => 1]) }}"
                    target="_blank" rel="noopener" class="btn btn-cetak-receipt btn-sm">
                    <i class="fas fa-print me-1"></i> Cetak Receipt
                </a>
            @endif
            @unless ($alasanTerkunci)
                <a href="{{ route('penerimaan-bahan-baku.edit', $penerimaan->id) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-pencil-alt me-1"></i> Edit
                </a>
                <button type="button" class="btn btn-danger btn-sm" id="btn-hapus">
                    <i class="fas fa-trash-alt me-1"></i> Hapus
                </button>
                <form id="form-hapus" action="{{ route('penerimaan-bahan-baku.destroy', $penerimaan->id) }}"
                    method="POST" class="d-none">
                    @csrf @method('DELETE')
                </form>
            @endunless
            <a href="{{ route('penerimaan-bahan-baku.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
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
    @if ($errors->any())
        <div class="alert alert-danger mb-3">
            @foreach (array_unique($errors->all()) as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif
    @if ($alasanTerkunci)
        <div class="alert alert-warning mb-3">
            <i class="fas fa-lock me-1"></i> {{ $alasanTerkunci }}
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="section-divider"><i class="fas fa-truck me-2"></i>Informasi Penerimaan</div>
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="info-label">No. Transaksi</div>
                    <div class="info-value">{{ $penerimaan->nomor_transaksi }}</div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Tanggal Terima</div>
                    <div class="info-value">{{ $penerimaan->tanggal_terima->translatedFormat('d F Y') }}</div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Produsen</div>
                    <div class="info-value">{{ $penerimaan->produsen->nama_produsen ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="info-label">Catatan</div>
                    <div class="info-value">{{ $penerimaan->catatan ?: '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="section-divider"><i class="fas fa-boxes-stacked me-2"></i>Item Diterima</div>
            <div class="table-responsive">
                <table class="table-item table">
                    <thead>
                        <tr>
                            <th>Permintaan</th>
                            <th>Bahan Baku</th>
                            <th class="text-end">Diterima</th>
                            <th class="text-end">Tersisa (Batch)</th>
                            <th>Kadaluarsa</th>
                            <th class="text-end">Harga</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($penerimaan->penerimaanBahanBakuDetails as $detail)
                            @php
                                $line = $baris[$detail->permintaan_bahan_baku_detail_id] ?? null;
                                $harga = $line['harga'] ?? 0;
                                $subtotal = (float) $detail->jumlah_diterima * $harga;
                                $totalNota += $subtotal;
                                $satuan = $detail->bahanBaku->satuan->kode_satuan
                                    ?: ($detail->bahanBaku->satuan->nama ?? '');
                            @endphp
                            <tr>
                                <td>
                                    <span class="badge-kode">
                                        {{ $detail->permintaanBahanBakuDetail->permintaanBahanBaku->nomor_transaksi ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="name-text">{{ $detail->bahanBaku->nama_bahan_baku }}</div>
                                    <div class="sub-text">{{ $detail->bahanBaku->kode_bahan_baku }}</div>
                                </td>
                                <td class="text-end">{{ $angka($detail->jumlah_diterima) }} {{ $satuan }}</td>
                                <td class="text-end">{{ $angka($detail->jumlah_tersisa) }} {{ $satuan }}</td>
                                <td>{{ $detail->tanggal_kadaluarsa?->translatedFormat('d M Y') ?? '-' }}</td>
                                <td class="text-end">{{ $rupiah($harga) }}</td>
                                <td class="text-end fw-bold">{{ $rupiah($subtotal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" class="text-end">Total Nota</td>
                            <td class="text-end">{{ $rupiah($totalNota) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="section-divider"><i class="fas fa-money-bill-wave me-2"></i>Tagihan Pembayaran ke Produsen</div>

            @php
                $bisaBayar = \Illuminate\Support\Facades\Route::has('penerimaan-bahan-baku.bayar');
                $bisaBatal = \Illuminate\Support\Facades\Route::has('penerimaan-bahan-baku.batal-bayar');
            @endphp

            <div class="row g-3">
                @forelse ($tagihans as $t)
                    @php $bayar = $t['pembayaran']; @endphp
                    <div class="col-lg-6">
                        <div class="pay-card h-100">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <div class="sub-text mb-1">Permintaan</div>
                                    <span class="badge-kode">{{ $t['nomor'] }}</span>
                                </div>
                                <div class="text-end">
                                    <div class="sub-text mb-1">Total Tagihan</div>
                                    <div class="pay-amount">{{ $rupiah($bayar->jumlah_bayar ?? $t['total']) }}</div>
                                </div>
                            </div>

                            <div class="table-responsive mt-3">
                                <table class="table-item table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Nota</th>
                                            <th>Bahan Baku</th>
                                            <th class="text-end">Jumlah</th>
                                            <th class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($t['rincian'] as $r)
                                            <tr>
                                                <td>
                                                    <div class="sub-text">{{ $r['nomor'] }}</div>
                                                    <div class="sub-text">{{ $r['tanggal']->translatedFormat('d M Y') }}</div>
                                                </td>
                                                <td>
                                                    <div class="name-text">{{ $r['nama'] }}</div>
                                                    <div class="sub-text">@ {{ $rupiah($r['harga']) }}</div>
                                                </td>
                                                <td class="text-end">{{ $angka($r['jumlah']) }} {{ $r['satuan'] }}</td>
                                                <td class="text-end fw-bold">{{ $rupiah($r['subtotal']) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if ($t['sisa_belum'] > 0)
                                <div class="sub-text mt-2">
                                    <i class="fas fa-hourglass-half me-1"></i>
                                    Masih ada barang dari permintaan ini yang belum diterima, nominal tagihan dapat bertambah.
                                </div>
                            @endif

                            @if ($bayar)
                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <span class="badge-status {{ $bayar->status_acc === 'disetujui' ? 'bs-hijau' : ($bayar->status_acc === 'ditolak' ? 'bs-merah' : 'bs-kuning') }}">
                                        ACC: {{ ucfirst($bayar->status_acc) }}
                                    </span>
                                    <span class="badge-status {{ $bayar->status_bayar === 'lunas' ? 'bs-hijau' : 'bs-kuning' }}">
                                        {{ $bayar->status_bayar === 'lunas' ? 'Lunas' : 'Belum dibayar' }}
                                    </span>
                                </div>

                                @if ($bayar->status_bayar === 'lunas')
                                    <div class="sub-text mt-2">
                                        Dibayar {{ \Carbon\Carbon::parse($bayar->tanggal_bayar)->translatedFormat('d M Y') }}
                                        @if ($bayar->metode_bayar) · {{ $bayar->metode_bayar }} @endif
                                    </div>
                                    @if ($bisaBatal)
                                        <form action="{{ route('penerimaan-bahan-baku.batal-bayar', $penerimaan->id) }}"
                                            method="POST" class="mt-2 form-batal-bayar">
                                            @csrf
                                            <input type="hidden" name="permintaan_bahan_baku_id" value="{{ $t['permintaan_id'] }}">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="fas fa-undo me-1"></i> Batalkan Pembayaran
                                            </button>
                                        </form>
                                    @endif
                                @elseif ($bayar->status_acc !== 'ditolak' && $bisaBayar)
                                    <form action="{{ route('penerimaan-bahan-baku.bayar', $penerimaan->id) }}"
                                        method="POST" class="row g-2 mt-2">
                                        @csrf
                                        <input type="hidden" name="permintaan_bahan_baku_id" value="{{ $t['permintaan_id'] }}">
                                        <div class="col-sm-5">
                                            <input type="date" name="tanggal_bayar" class="form-control form-control-sm"
                                                value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required>
                                        </div>
                                        <div class="col-sm-4">
                                            <select name="metode_bayar" class="form-select form-select-sm" required>
                                                <option value="">Metode</option>
                                                <option>Tunai</option>
                                                <option>Transfer</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-3 d-grid">
                                            <button type="submit" class="btn btn-success btn-sm">Bayar</button>
                                        </div>
                                    </form>
                                @endif
                            @else
                                <div class="sub-text mt-3">Belum ada tagihan untuk permintaan ini.</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-muted" style="font-size:.85rem;">Belum ada tagihan pembayaran.</div>
                @endforelse
            </div>
            <div class="form-text mt-3" style="font-size:.75rem;">
                Total tagihan dihitung per permintaan dari seluruh penerimaan produsen tersebut (jumlah diterima × harga satuan).
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    const btnHapus = document.getElementById('btn-hapus');
    if (btnHapus) {
        btnHapus.addEventListener('click', function () {
            swal({
                title: 'Hapus Data?',
                text: 'Penerimaan {{ $penerimaan->nomor_transaksi }} akan dihapus. Stok bahan baku dan tagihan pembayaran akan disesuaikan.',
                icon: 'warning',
                buttons: {
                    cancel: 'Batal',
                    confirm: { text: 'Ya, Hapus!', className: 'btn-danger' }
                },
                dangerMode: true,
            }).then(confirmed => {
                if (confirmed) document.getElementById('form-hapus').submit();
            });
        });
    }
</script>
@endsection
