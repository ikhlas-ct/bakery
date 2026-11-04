@extends('layouts.user.user')

@section('title', 'Edit Penerimaan – ' . $penerimaan->nomor_transaksi)

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
    .required-mark { color: #dc3545; }
    label { font-size: .875rem; font-weight: 500; }

    .form-control, .form-select {
        border-radius: 10px; border: 1.5px solid #e2e8f0;
        font-size: .85rem; padding: 8px 12px; color: #334155;
        background-color: #f8fafc; transition: border-color .2s, box-shadow .2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: #1a73e8; background: #fff;
        box-shadow: 0 0 0 3px rgba(26,115,232,.12);
    }
    .form-control::placeholder { color: #94a3b8; }
    .form-control[readonly], .form-control:disabled { background-color: #f1f5f9; color: #64748b; }

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

    .table-item { font-size: .83rem; margin-bottom: 0; }
    .table-item thead th {
        background: #f8fafc; color: #475569; font-weight: 700;
        font-size: .72rem; text-transform: uppercase; letter-spacing: .4px;
        border-bottom: 1.5px solid #eef2f6; padding: 10px 12px; white-space: nowrap;
    }
    .table-item tbody td { padding: 10px 12px; vertical-align: middle; border-bottom: 1px solid #f4f6f8; }
    .badge-kode {
        background: #eef2ff; color: #4338ca; font-weight: 600;
        font-size: .72rem; padding: 5px 10px; border-radius: 8px;
    }
    .name-text { font-weight: 700; color: #1e293b; font-size: .85rem; }
    .sub-text { color: #94a3b8; font-size: .74rem; }
    .alert { border-radius: 12px; border: none; font-size: .85rem; }
    .alert-danger { background: #fee2e2; color: #991b1b; }
    .alert-warning { background: #fef3c7; color: #92400e; }
</style>
@endsection

@section('content')
@php
    $dashboardRoute = auth()->user()->role === 'admin' ? route('admin.dashboard') : route('pemilik.dashboard');
    $angka = fn ($v) => rtrim(rtrim(number_format($v, 2, ',', '.'), '0'), ',');
@endphp
<div class="container">

    {{-- ── Page Header ── --}}
    <div class="ph-card">
        <div class="ph-left">
            <div class="ph-icon"><i class="fas fa-truck-loading"></i></div>
            <div>
                <h5 class="ph-title">Edit Penerimaan Bahan Baku</h5>
                <ol class="ph-breadcrumb">
                    <li><a href="{{ $dashboardRoute }}">Dashboard</a></li>
                    <li><a href="{{ route('penerimaan-bahan-baku.index') }}">Penerimaan Bahan Baku</a></li>
                    <li><span class="bc-active">Edit</span></li>
                </ol>
            </div>
        </div>
        <a href="{{ route('penerimaan-bahan-baku.show', $penerimaan->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if ($alasanTerkunci)
        <div class="alert alert-warning mb-3">
            <i class="fas fa-lock me-1"></i> {{ $alasanTerkunci }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mb-3">
            <ul class="mb-0 ps-3">
                @foreach (array_unique($errors->all()) as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="card shadow-sm">
                <div class="card-body">

                    <form action="{{ route('penerimaan-bahan-baku.update', $penerimaan->id) }}" method="POST" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="section-divider"><i class="fas fa-truck me-2"></i>Data Pengiriman</div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">No. Transaksi</label>
                                <input type="text" class="form-control" readonly value="{{ $penerimaan->nomor_transaksi }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Produsen Pengirim</label>
                                <input type="text" class="form-control" readonly
                                    value="{{ $penerimaan->produsen->nama_produsen ?? '-' }}">
                                <div class="form-text" style="font-size:.75rem;">Produsen tidak dapat diubah.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Tanggal Terima <span class="required-mark">*</span></label>
                                <input type="date" name="tanggal_terima" id="tanggal_terima"
                                    class="form-control @error('tanggal_terima') is-invalid @enderror"
                                    value="{{ old('tanggal_terima', $penerimaan->tanggal_terima->toDateString()) }}"
                                    max="{{ now()->toDateString() }}" required @disabled($alasanTerkunci)>
                                @error('tanggal_terima')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Catatan</label>
                                <input type="text" name="catatan" maxlength="1000"
                                    class="form-control @error('catatan') is-invalid @enderror"
                                    value="{{ old('catatan', $penerimaan->catatan) }}" @disabled($alasanTerkunci)>
                                @error('catatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="section-divider"><i class="fas fa-boxes-stacked me-2"></i>Item Diterima</div>

                        <div class="table-responsive">
                            <table class="table-item table">
                                <thead>
                                    <tr>
                                        <th>Permintaan</th>
                                        <th>Bahan Baku</th>
                                        <th class="text-end">Maks. Dapat Diterima</th>
                                        <th class="text-end">Harga</th>
                                        <th width="170">Jumlah Diterima</th>
                                        <th width="170">Tgl. Kadaluarsa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($penerimaan->penerimaanBahanBakuDetails as $detail)
                                        @php
                                            $line = $baris[$detail->permintaan_bahan_baku_detail_id] ?? null;
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
                                            <td class="text-end">
                                                {{ $line ? $angka($line['sisa']) . ' ' . $satuan : '-' }}
                                            </td>
                                            <td class="text-end">
                                                {{ $line ? 'Rp ' . number_format($line['harga'], 0, ',', '.') : '-' }}
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" step="0.01" min="0.01"
                                                        @if ($line) max="{{ $line['sisa'] }}" @endif
                                                        name="items[{{ $detail->id }}][jumlah_diterima]"
                                                        class="form-control @error('items.' . $detail->id . '.jumlah_diterima') is-invalid @enderror"
                                                        value="{{ old('items.' . $detail->id . '.jumlah_diterima', $detail->jumlah_diterima + 0) }}"
                                                        required @disabled($alasanTerkunci)>
                                                    <span class="input-group-text">{{ $satuan }}</span>
                                                </div>
                                                @error('items.' . $detail->id . '.jumlah_diterima')
                                                    <div class="text-danger" style="font-size:.75rem;">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <input type="date" name="items[{{ $detail->id }}][tanggal_kadaluarsa]"
                                                    class="form-control form-control-sm @error('items.' . $detail->id . '.tanggal_kadaluarsa') is-invalid @enderror"
                                                    value="{{ old('items.' . $detail->id . '.tanggal_kadaluarsa', optional($detail->tanggal_kadaluarsa)->toDateString()) }}"
                                                    @disabled($alasanTerkunci)>
                                                @error('items.' . $detail->id . '.tanggal_kadaluarsa')
                                                    <div class="text-danger" style="font-size:.75rem;">{{ $message }}</div>
                                                @enderror
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="form-text mt-2" style="font-size:.75rem;">
                            Mengubah jumlah akan menyesuaikan stok bahan baku dan nominal tagihan pembayaran.
                            Bahan baku, produsen, dan permintaan asal tidak dapat diubah — hapus lalu catat ulang bila keliru.
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary flex-fill" @disabled($alasanTerkunci)>
                                <i class="fas fa-save me-2"></i> Simpan Perubahan
                            </button>
                            <a href="{{ route('penerimaan-bahan-baku.show', $penerimaan->id) }}"
                                class="btn btn-outline-secondary flex-fill">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
