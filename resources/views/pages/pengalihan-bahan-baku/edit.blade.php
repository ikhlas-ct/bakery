@extends('layouts.user.user')

@section('title', 'Tentukan Pengganti')

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
    $satuan = $asal->satuan->nama ?? '';
    $header = $detail->permintaanBahanBaku;
    $belum  = is_null($p->produsen_pengganti_id);
@endphp
<div class="container">

    <div class="ph-card">
        <div class="ph-left">
            <div class="ph-icon"><i class="fas fa-random"></i></div>
            <div>
                <h5 class="ph-title">{{ $belum ? 'Tentukan Pengganti' : 'Ubah Pengganti' }}</h5>
                <ol class="ph-breadcrumb">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('pengalihan-bahan-baku.index') }}">Pengalihan Bahan Baku</a></li>
                    <li><span class="bc-active">{{ $belum ? 'Tentukan' : 'Ubah' }}</span></li>
                </ol>
            </div>
        </div>
        <a href="{{ route('pengalihan-bahan-baku.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
    </div>

    @if ($p->status_produsen_pengganti === 'ditolak')
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
            <i class="fas fa-triangle-exclamation"></i>
            Pengganti sebelumnya menolak. Pilih pengganti lain; menyimpan akan memulai ulang siklus persetujuan.
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-8">

            {{-- Ringkasan kekurangan --}}
            <div class="card shadow-sm mb-3"><div class="card-body">
                <div class="section-divider"><i class="fas fa-file-lines me-2"></i>Kekurangan Permintaan</div>
                <div class="row g-3">
                    <div class="col-md-4 col-6"><div class="info-label">Permintaan</div>
                        <div class="info-value"><span class="badge-kode">{{ $header->nomor_transaksi }}</span></div></div>
                    <div class="col-md-4 col-6"><div class="info-label">Bahan Baku</div>
                        <div class="info-value">{{ $asal->nama_bahan_baku }}</div></div>
                    <div class="col-md-4 col-6"><div class="info-label">Produsen Asal</div>
                        <div class="info-value">{{ $detail->produsen->nama_produsen ?? '-' }}</div></div>
                    <div class="col-md-4 col-6"><div class="info-label">Diminta</div>
                        <div class="info-value">{{ $fmt($detail->jumlah_diminta) }} {{ $satuan }}</div></div>
                    <div class="col-md-4 col-6"><div class="info-label">Disetujui</div>
                        <div class="info-value">{{ $fmt($detail->jumlah_disetujui) }} {{ $satuan }}</div></div>
                    <div class="col-md-4 col-6"><div class="info-label">Kekurangan</div>
                        <div class="info-value text-danger">{{ $fmt($kekurangan) }} {{ $satuan }}</div></div>
                </div>
            </div></div>

            {{-- Form --}}
            <div class="card shadow-sm"><div class="card-body">
                <div class="section-divider"><i class="fas fa-user-check me-2"></i>Pengganti</div>

                <form action="{{ route('pengalihan-bahan-baku.update', $p->id) }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Bahan Baku <span class="required-mark">*</span></label>
                            <select name="bahan_baku_pengganti_id" id="bahan_baku_pengganti_id"
                                class="form-select @error('bahan_baku_pengganti_id') is-invalid @enderror">
                                <option value="">{{ $asal->nama_bahan_baku }} (bahan baku sama)</option>
                                @foreach ($bahanPengganti as $b)
                                    <option value="{{ $b->id }}" @selected(old('bahan_baku_pengganti_id', $p->bahan_baku_pengganti_id) == $b->id)>
                                        {{ $b->nama_bahan_baku }} — {{ $b->kategoriBarang->nama_kategori ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text" style="font-size:.75rem;">Hanya bahan baku aktif dengan satuan {{ $satuan }} yang punya pemasok.</div>
                            @error('bahan_baku_pengganti_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Produsen Pengganti <span class="required-mark">*</span></label>
                            <select name="produsen_pengganti_id" id="produsen_pengganti_id"
                                class="form-select @error('produsen_pengganti_id') is-invalid @enderror"></select>
                            <div class="form-text" id="info-harga" style="font-size:.75rem;"></div>
                            @error('produsen_pengganti_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Jumlah Dialihkan <span class="required-mark">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0.01" max="{{ $kekurangan }}" name="jumlah_dialihkan"
                                    class="form-control @error('jumlah_dialihkan') is-invalid @enderror"
                                    value="{{ old('jumlah_dialihkan', $p->jumlah_dialihkan) }}">
                                <span class="input-group-text">{{ $satuan }}</span>
                                @error('jumlah_dialihkan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-text" style="font-size:.75rem;">Maksimal {{ $fmt($kekurangan) }} {{ $satuan }} (kekurangan).</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Alasan Pengalihan <span class="required-mark">*</span></label>
                            <input type="text" name="alasan_pengalihan" maxlength="255"
                                class="form-control @error('alasan_pengalihan') is-invalid @enderror"
                                value="{{ old('alasan_pengalihan', $p->alasan_pengalihan) }}">
                            @error('alasan_pengalihan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary flex-fill" id="btn-simpan"><i class="fas fa-save me-2"></i> Simpan</button>
                        <a href="{{ route('pengalihan-bahan-baku.index') }}" class="btn btn-outline-secondary flex-fill">Batal</a>
                    </div>
                </form>
            </div></div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // [bahan_baku_id => [{produsen_id, nama, harga}]]  (sudah difilter: mitra aktif, bukan produsen asal utk bahan sama)
    const OPSI = @json($opsi);
    const ASAL_ID = {{ $asal->id }};
    const TERPILIH = @json((string) old('produsen_pengganti_id', $p->produsen_pengganti_id));

    const selBahan   = document.getElementById('bahan_baku_pengganti_id');
    const selProd    = document.getElementById('produsen_pengganti_id');
    const infoHarga  = document.getElementById('info-harga');
    const btnSimpan  = document.getElementById('btn-simpan');
    const rupiah = n => 'Rp ' + Number(n).toLocaleString('id-ID');

    function renderProdusen(pilih) {
        const key  = selBahan.value || ASAL_ID;
        const list = OPSI[key] || [];
        selProd.innerHTML = '';

        if (list.length === 0) {
            selProd.innerHTML = '<option value="">-- Tidak ada produsen tersedia --</option>';
            btnSimpan.disabled = true;
            infoHarga.textContent = '';
            return;
        }
        btnSimpan.disabled = false;

        selProd.insertAdjacentHTML('beforeend', '<option value="">-- Pilih Produsen --</option>');
        list.forEach(o => {
            const opt = document.createElement('option');
            opt.value = o.produsen_id;
            opt.textContent = `${o.nama} — ${rupiah(o.harga)}`;
            opt.dataset.harga = o.harga;
            if (String(o.produsen_id) === String(pilih)) opt.selected = true;
            selProd.appendChild(opt);
        });
        tampilHarga();
    }

    function tampilHarga() {
        const opt = selProd.selectedOptions[0];
        infoHarga.textContent = (opt && opt.dataset.harga) ? `Harga per satuan: ${rupiah(opt.dataset.harga)}` : '';
    }

    selBahan.addEventListener('change', () => renderProdusen(null));
    selProd.addEventListener('change', tampilHarga);
    renderProdusen(TERPILIH);
</script>
@endsection
