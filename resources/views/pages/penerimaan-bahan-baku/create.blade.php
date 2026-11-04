@extends('layouts.user.user')

@section('title', 'Catat Penerimaan Bahan Baku')

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
    .grup-row td { background: #f1f5f9; font-size: .8rem; font-weight: 700; color: #334155; border-bottom: 1px solid #e2e8f0 !important; }
    .total-box {
        background: #f0f9ff; border-radius: 12px; padding: 12px 16px;
        display: flex; justify-content: space-between; align-items: center;
        font-size: .9rem; color: #0369a1; font-weight: 700;
    }
    .alert { border-radius: 12px; border: none; font-size: .85rem; }
    .alert-danger { background: #fee2e2; color: #991b1b; }
</style>
@endsection

@section('content')
@php
    $dashboardRoute = auth()->user()->role === 'admin' ? route('admin.dashboard') : route('pemilik.dashboard');
@endphp
<div class="container">

    {{-- ── Page Header ── --}}
    <div class="ph-card">
        <div class="ph-left">
            <div class="ph-icon"><i class="fas fa-truck-loading"></i></div>
            <div>
                <h5 class="ph-title">Catat Penerimaan Bahan Baku</h5>
                <ol class="ph-breadcrumb">
                    <li><a href="{{ $dashboardRoute }}">Dashboard</a></li>
                    <li><a href="{{ route('penerimaan-bahan-baku.index') }}">Penerimaan Bahan Baku</a></li>
                    <li><span class="bc-active">Tambah</span></li>
                </ol>
            </div>
        </div>
        <a href="{{ route('penerimaan-bahan-baku.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

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

                    <form action="{{ route('penerimaan-bahan-baku.store') }}" method="POST" novalidate id="form-penerimaan">
                        @csrf

                        <div class="section-divider"><i class="fas fa-truck me-2"></i>Data Pengiriman</div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Produsen Pengirim <span class="required-mark">*</span></label>
                                <select name="produsen_id" id="produsen_id"
                                    class="form-select @error('produsen_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Produsen --</option>
                                    @foreach ($produsens as $produsen)
                                        <option value="{{ $produsen->id }}" @selected(old('produsen_id') == $produsen->id)>
                                            {{ $produsen->nama_produsen }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('produsen_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="form-text" style="font-size:.75rem;">
                                    Satu penerimaan untuk satu produsen, boleh berisi barang dari beberapa permintaan.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tanggal Terima <span class="required-mark">*</span></label>
                                <input type="date" name="tanggal_terima" id="tanggal_terima"
                                    class="form-control @error('tanggal_terima') is-invalid @enderror"
                                    value="{{ old('tanggal_terima', now()->toDateString()) }}"
                                    max="{{ now()->toDateString() }}" required>
                                @error('tanggal_terima')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Catatan</label>
                                <input type="text" name="catatan" maxlength="1000"
                                    class="form-control @error('catatan') is-invalid @enderror"
                                    value="{{ old('catatan') }}" placeholder="Opsional, mis. kondisi barang">
                                @error('catatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="section-divider"><i class="fas fa-boxes-stacked me-2"></i>Item yang Belum Diterima</div>

                        <div id="item-placeholder" class="text-center text-muted py-4" style="font-size:.85rem;">
                            <i class="fas fa-hand-pointer mb-2 d-block" style="font-size:1.4rem;"></i>
                            Pilih produsen untuk menampilkan semua item dari berbagai permintaan yang sudah disetujui dan belum diterima.
                        </div>

                        <div id="item-wrapper" class="d-none">
                            <div class="table-responsive">
                                <table class="table-item table">
                                    <thead>
                                        <tr>
                                            <th>Bahan Baku</th>
                                            <th class="text-end">Disetujui</th>
                                            <th class="text-end">Sudah Diterima</th>
                                            <th class="text-end">Sisa</th>
                                            <th class="text-end">Harga</th>
                                            <th width="150">Jumlah Diterima</th>
                                            <th width="160">Tgl. Kadaluarsa</th>
                                        </tr>
                                    </thead>
                                    <tbody id="item-body"></tbody>
                                </table>
                            </div>

                            <div class="total-box mt-3">
                                <span><i class="fas fa-calculator me-2"></i>Estimasi tagihan pembayaran</span>
                                <span id="total-estimasi">Rp 0</span>
                            </div>
                            <div class="form-text mt-2" style="font-size:.75rem;">
                                Kosongkan jumlah pada item yang belum dikirim produsen (item yang sudah diterima penuh tidak ditampilkan lagi). Saat disimpan, stok bahan baku bertambah dan
                                tagihan pembayaran ke produsen dibuat/diperbarui otomatis.
                            </div>
                        </div>

                        <div id="item-kosong" class="alert alert-danger d-none mt-3">
                            <i class="fas fa-info-circle me-1"></i>
                            Produsen ini tidak punya item yang disetujui dan masih menunggu penerimaan.
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary flex-fill" id="btn-simpan">
                                <i class="fas fa-save me-2"></i> Simpan Penerimaan
                            </button>
                            <a href="{{ route('penerimaan-bahan-baku.index') }}" class="btn btn-outline-secondary flex-fill">
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

@section('scripts')
<script>
    const urlTemplate = @json(route('penerimaan-bahan-baku.item-produsen', ['produsen' => '__ID__']));
    const oldItems = @json(old('items', []));
    let allRows = [];

    const selProdusen = document.getElementById('produsen_id');
    const placeholder = document.getElementById('item-placeholder');
    const wrapper = document.getElementById('item-wrapper');
    const kosong = document.getElementById('item-kosong');
    const body = document.getElementById('item-body');
    const totalEl = document.getElementById('total-estimasi');
    const tglTerima = document.getElementById('tanggal_terima');

    const fmt = n => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(n);
    const rupiah = n => 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(n);
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    function hitungTotal() {
        let total = 0;
        body.querySelectorAll('.input-jumlah').forEach(inp => {
            total += (parseFloat(inp.value) || 0) * parseFloat(inp.dataset.harga);
        });
        totalEl.textContent = rupiah(total);
    }

    function renderItems(rows) {
        body.innerHTML = '';
        placeholder.classList.add('d-none');
        kosong.classList.add('d-none');

        if (!rows.length) {
            wrapper.classList.add('d-none');
            kosong.classList.remove('d-none');
            return;
        }

        let grup = null;
        rows.forEach(r => {
            if (grup !== r.permintaan_id) {
                grup = r.permintaan_id;
                const jml = rows.filter(x => x.permintaan_id === grup).length;
                const head = document.createElement('tr');
                head.className = 'grup-row';
                head.innerHTML = `<td colspan="7">
                    <i class="fas fa-file-invoice me-1"></i> Permintaan
                    <span class="badge-kode ms-1">${esc(r.nomor_permintaan)}</span>
                    <span class="sub-text ms-2">${jml} item belum diterima</span>
                </td>`;
                body.appendChild(head);
            }

            const old = oldItems[r.id] || {};
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <div class="name-text">${esc(r.nama)}</div>
                    <div class="sub-text">${esc(r.kode)}</div>
                </td>
                <td class="text-end">${fmt(r.batas)} ${esc(r.satuan)}</td>
                <td class="text-end">${fmt(r.diterima)}</td>
                <td class="text-end fw-bold">${fmt(r.sisa)}</td>
                <td class="text-end">${rupiah(r.harga)}</td>
                <td>
                    <input type="hidden" name="items[${r.id}][permintaan_bahan_baku_detail_id]" value="${r.id}">
                    <div class="input-group input-group-sm">
                        <input type="number" step="0.01" min="0" max="${r.sisa}"
                            name="items[${r.id}][jumlah_diterima]" class="form-control input-jumlah"
                            data-harga="${r.harga}" data-sisa="${r.sisa}"
                            value="${esc(old.jumlah_diterima ?? '')}" placeholder="0">
                        <button type="button" class="btn btn-outline-secondary btn-isi" title="Isi sesuai sisa">Max</button>
                    </div>
                </td>
                <td>
                    <input type="date" name="items[${r.id}][tanggal_kadaluarsa]" class="form-control form-control-sm"
                        value="${esc(old.tanggal_kadaluarsa ?? '')}">
                </td>`;
            body.appendChild(tr);
        });

        body.querySelectorAll('input[type=date]').forEach(i => i.min = tglTerima.value);
        wrapper.classList.remove('d-none');
        hitungTotal();
    }

    function resetTampilan() {
        body.innerHTML = '';
        wrapper.classList.add('d-none');
        kosong.classList.add('d-none');
        placeholder.classList.remove('d-none');
        hitungTotal();
    }

    function muatItem() {
        const id = selProdusen.value;
        allRows = [];

        if (!id) {
            resetTampilan();
            return;
        }

        fetch(urlTemplate.replace('__ID__', id), { headers: { 'Accept': 'application/json' } })
            .then(res => {
                if (!res.ok) throw new Error('gagal');
                return res.json();
            })
            .then(rows => {
                allRows = rows; // sudah hanya berisi item yang masih punya sisa
                renderItems(allRows);
            })
            .catch(() => {
                swal('Gagal', 'Item produsen tidak dapat dimuat. Silakan coba lagi.', 'error');
            });
    }

    body.addEventListener('input', e => {
        if (e.target.classList.contains('input-jumlah')) hitungTotal();
    });
    body.addEventListener('click', e => {
        const btn = e.target.closest('.btn-isi');
        if (!btn) return;
        const inp = btn.closest('.input-group').querySelector('.input-jumlah');
        inp.value = inp.dataset.sisa;
        hitungTotal();
    });

    // Tanggal kadaluarsa tidak boleh sebelum tanggal terima
    tglTerima.addEventListener('change', () => {
        body.querySelectorAll('input[type=date]').forEach(i => i.min = tglTerima.value);
    });

    selProdusen.addEventListener('change', () => {
        Object.keys(oldItems).forEach(k => delete oldItems[k]); // old input hanya berlaku sekali
        muatItem();
    });

    if (selProdusen.value) muatItem();
</script>
@endsection
