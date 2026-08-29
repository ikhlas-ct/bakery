@extends('layouts.user.user')

@section('title', 'Edit Permintaan – ' . $permintaan->nomor_transaksi)

@section('styles')
<style>
    .section-divider {
        background: #f8f9fa; border-left: 4px solid #1a73e8; padding: 8px 14px;
        border-radius: 0 6px 6px 0; font-weight: 600; font-size: .9rem;
        color: #1a73e8; margin-bottom: 1rem;
    }
    .required-mark { color: #dc3545; }
    label { font-size: .875rem; font-weight: 500; }
    .form-control, .form-select {
        border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: .85rem;
        padding: 8px 12px; color: #334155; background-color: #f8fafc;
        transition: border-color .2s, box-shadow .2s;
    }
    .form-control:focus, .form-select:focus { border-color: #1a73e8; background: #fff; box-shadow: 0 0 0 3px rgba(26,115,232,.12); }
    .form-control[readonly] { background-color: #f1f5f9; color: #64748b; }
    .form-select:disabled { background-color: #f1f5f9; }

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
        width: 42px; height: 42px; border-radius: 10px; display: flex;
        align-items: center; justify-content: center; font-size: 1rem;
        flex-shrink: 0; background: #dbeafe; color: #1a73e8;
    }
    .ph-title { font-size: 1.15rem; font-weight: 800; color: #1e293b; margin: 0; }
    .ph-breadcrumb { list-style: none; display: flex; gap: 6px; padding: 0; margin: 2px 0 0; }
    .ph-breadcrumb li:not(:last-child)::after { content: '/'; margin-left: 6px; color: #cbd5e1; }
    .ph-breadcrumb a { color: #1a73e8; text-decoration: none; font-size: .78rem; }
    .ph-breadcrumb .bc-active { font-size: .75rem; color: #94a3b8; }

    #items-table th, #locked-table th { font-size: .72rem; text-transform: uppercase; color: #64748b; }
    #items-table td, #locked-table td { vertical-align: middle; }
    .btn-remove-row { color: #dc3545; border-color: #f1c2c2; }
    .badge-locked { background: #e2e8f0; color: #475569; }
    .badge-pending { background: #fef3c7; color: #92400e; }
    .badge-approved { background: #dcfce7; color: #166534; }
    .badge-rejected { background: #fee2e2; color: #991b1b; }
</style>
@endsection

@section('content')
@php
    $dashboardRoute = auth()->user()->role === 'admin' ? route('admin.dashboard') : route('pemilik.dashboard');
    $badgeMap = [
        'pending' => 'badge-pending',
        'disetujui' => 'badge-approved',
        'ditolak' => 'badge-rejected',
    ];
@endphp
<div class="container">

    <div class="ph-card">
        <div class="ph-left">
            <div class="ph-icon"><i class="fas fa-file-invoice"></i></div>
            <div>
                <h5 class="ph-title">Edit Permintaan {{ $permintaan->nomor_transaksi }}</h5>
                <ol class="ph-breadcrumb">
                    <li><a href="{{ $dashboardRoute }}">Dashboard</a></li>
                    <li><a href="{{ route('permintaan-bahan-baku.index') }}">Permintaan Bahan Baku</a></li>
                    <li><span class="bc-active">Edit</span></li>
                </ol>
            </div>
        </div>
        <a href="{{ route('permintaan-bahan-baku.show', $permintaan->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($lockedItems->isNotEmpty())
        <div class="alert alert-info" style="font-size:.85rem;">
            <i class="fas fa-lock me-1"></i>
            Ada {{ $lockedItems->count() }} item yang sudah direspons produsen — item tersebut dikunci dan
            tidak bisa diubah/dihapus dari sini.
        </div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="section-divider"><i class="fas fa-info-circle me-2"></i>Data Permintaan</div>

            <form id="form-permintaan" action="{{ route('permintaan-bahan-baku.update', $permintaan->id) }}" method="POST" novalidate>
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Nomor Transaksi</label>
                        <input type="text" class="form-control" readonly value="{{ $permintaan->nomor_transaksi }}">
                    </div>
                    <div class="col-md-7">
                        <label class="form-label">Tanggal Permintaan <span class="required-mark">*</span></label>
                        <input type="date" name="tanggal_permintaan"
                            class="form-control @error('tanggal_permintaan') is-invalid @enderror"
                            value="{{ old('tanggal_permintaan', \Carbon\Carbon::parse($permintaan->tanggal_permintaan)->format('Y-m-d')) }}"
                            required>
                        @error('tanggal_permintaan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Keterangan</label>
                        <input type="text" name="keterangan"
                            class="form-control @error('keterangan') is-invalid @enderror"
                            value="{{ old('keterangan', $permintaan->keterangan) }}">
                        @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                @if ($lockedItems->isNotEmpty())
                    <hr class="my-4">
                    <div class="section-divider"><i class="fas fa-lock me-2"></i>Item Sudah Direspons (Terkunci)</div>
                    <div class="table-responsive">
                        <table class="table" id="locked-table">
                            <thead>
                                <tr>
                                    <th>Bahan Baku</th>
                                    <th>Produsen</th>
                                    <th class="text-end">Harga Satuan</th>
                                    <th class="text-end">Jumlah Diminta</th>
                                    <th class="text-end">Jumlah Disetujui</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lockedItems as $detail)
                                    <tr>
                                        <td>{{ $detail->bahanBaku->nama_bahan_baku ?? '-' }}</td>
                                        <td>{{ $detail->produsen->nama_produsen ?? '-' }}</td>
                                        <td class="text-end">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                                        <td class="text-end">{{ rtrim(rtrim(number_format($detail->jumlah_diminta, 2, ',', '.'), '0'), ',') }}</td>
                                        <td class="text-end">
                                            {{ $detail->jumlah_disetujui !== null
                                                ? rtrim(rtrim(number_format($detail->jumlah_disetujui, 2, ',', '.'), '0'), ',')
                                                : '-' }}
                                        </td>
                                        <td><span class="badge {{ $badgeMap[$detail->status_produsen] ?? 'badge-locked' }}">{{ ucfirst($detail->status_produsen) }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="section-divider mb-0"><i class="fas fa-boxes-stacked me-2"></i>Item Menunggu Respons (Bisa Diedit)</div>
                    <button type="button" id="btn-add-row" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i> Tambah Baris
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table" id="items-table">
                        <thead>
                            <tr>
                                <th style="min-width: 220px;">Bahan Baku <span class="required-mark">*</span></th>
                                <th style="min-width: 200px;">Produsen <span class="required-mark">*</span></th>
                                <th style="min-width: 140px;">Harga Satuan</th>
                                <th style="min-width: 140px;">Jumlah Diminta <span class="required-mark">*</span></th>
                                <th style="width: 48px;"></th>
                            </tr>
                        </thead>
                        <tbody id="items-tbody">
                            {{-- Baris item diisi oleh JavaScript --}}
                        </tbody>
                    </table>
                </div>
                <div class="form-text" style="font-size:.75rem;">
                    Baris di atas hanya berisi item yang statusnya masih <em>pending</em>. Hapus baris untuk
                    membatalkan item itu, atau tambah baris baru untuk menambah item ke permintaan ini.
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fas fa-save me-2"></i> Simpan Perubahan
                    </button>
                    <a href="{{ route('permintaan-bahan-baku.show', $permintaan->id) }}" class="btn btn-outline-secondary flex-fill">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@php
    $editableItemsJs = $editableItems->map(fn ($d) => [
        'id' => $d->id,
        'bahan_baku_id' => $d->bahan_baku_id,
        'produsen_id' => $d->produsen_id,
        'jumlah_diminta' => $d->jumlah_diminta,
    ]);
@endphp
<script>
(function () {
    // Master data bahan baku aktif untuk dropdown.
    const bahanBakus = @json($bahanBakusForEdit ?? []);

    // Item pending yang ada saat ini (dari DB) — jadi baris awal kalau tidak ada old('items').
    const currentEditableItems = @json($editableItemsJs);

    // Kalau validasi sebelumnya gagal, pakai data yang barusan diketik user.
    const oldItems = @json(old('items', null));

    const produsenUrlTemplate = "{{ route('bahan-baku.produsens', ['bahanBaku' => '__ID__']) }}";

    const tbody = document.getElementById('items-tbody');
    let rowIndex = 0;

    function formatRupiah(value) {
        const num = Number(value) || 0;
        return 'Rp ' + num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    function bahanBakuOptions(selectedId) {
        let html = '<option value="">-- Pilih Bahan Baku --</option>';
        bahanBakus.forEach(function (bb) {
            const selected = String(bb.id) === String(selectedId) ? 'selected' : '';
            html += `<option value="${bb.id}" ${selected}>${bb.label}</option>`;
        });
        return html;
    }

    function addRow(prefill) {
        prefill = prefill || {};
        const index = rowIndex++;

        const tr = document.createElement('tr');
        tr.dataset.index = index;
        tr.innerHTML = `
            <td>
                <input type="hidden" name="items[${index}][id]" value="${prefill.id ?? ''}">
                <select name="items[${index}][bahan_baku_id]" class="form-select bahan-baku-select" required>
                    ${bahanBakuOptions(prefill.bahan_baku_id)}
                </select>
            </td>
            <td>
                <select name="items[${index}][produsen_id]" class="form-select produsen-select" required disabled>
                    <option value="">-- Pilih Bahan Baku Dahulu --</option>
                </select>
            </td>
            <td>
                <input type="text" class="form-control harga-display" readonly value="-">
            </td>
            <td>
                <input type="number" step="0.01" min="0.01" name="items[${index}][jumlah_diminta]"
                    class="form-control jumlah-input" placeholder="0.00"
                    value="${prefill.jumlah_diminta ?? ''}" required>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" title="Hapus baris">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);

        const bahanBakuSelect = tr.querySelector('.bahan-baku-select');
        const produsenSelect = tr.querySelector('.produsen-select');
        const hargaDisplay = tr.querySelector('.harga-display');

        let pendingSelectedProdusenId = prefill.produsen_id ?? null;

        bahanBakuSelect.addEventListener('change', function () {
            loadProdusens(this.value, produsenSelect, hargaDisplay, pendingSelectedProdusenId);
            pendingSelectedProdusenId = null; // hanya dipakai sekali saat prefill awal
        });

        produsenSelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const harga = selectedOption ? selectedOption.dataset.harga : null;
            hargaDisplay.value = harga ? formatRupiah(harga) : '-';
        });

        tr.querySelector('.btn-remove-row').addEventListener('click', function () {
            tr.remove();
        });

        if (prefill.bahan_baku_id) {
            loadProdusens(prefill.bahan_baku_id, produsenSelect, hargaDisplay, prefill.produsen_id);
        }
    }

    function loadProdusens(bahanBakuId, produsenSelect, hargaDisplay, selectedProdusenId) {
        hargaDisplay.value = '-';

        if (!bahanBakuId) {
            produsenSelect.innerHTML = '<option value="">-- Pilih Bahan Baku Dahulu --</option>';
            produsenSelect.disabled = true;
            return;
        }

        produsenSelect.disabled = true;
        produsenSelect.innerHTML = '<option value="">Memuat produsen...</option>';

        const url = produsenUrlTemplate.replace('__ID__', bahanBakuId);

        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(function (res) { return res.json(); })
            .then(function (produsens) {
                if (!produsens.length) {
                    produsenSelect.innerHTML = '<option value="">Tidak ada produsen untuk bahan baku ini</option>';
                    produsenSelect.disabled = true;
                    return;
                }

                let html = '<option value="">-- Pilih Produsen --</option>';
                produsens.forEach(function (p) {
                    const selected = String(p.id) === String(selectedProdusenId) ? 'selected' : '';
                    html += `<option value="${p.id}" data-harga="${p.harga}" ${selected}>${p.nama_produsen}</option>`;
                });
                produsenSelect.innerHTML = html;
                produsenSelect.disabled = false;

                if (selectedProdusenId) {
                    const opt = produsenSelect.querySelector(`option[value="${selectedProdusenId}"]`);
                    if (opt) hargaDisplay.value = formatRupiah(opt.dataset.harga);
                }
            })
            .catch(function () {
                produsenSelect.innerHTML = '<option value="">Gagal memuat produsen</option>';
                produsenSelect.disabled = true;
            });
    }

    document.getElementById('btn-add-row').addEventListener('click', function () {
        addRow();
    });

    // Inisialisasi baris: pakai old('items') kalau validasi gagal, kalau tidak pakai data pending dari DB.
    if (oldItems && oldItems.length > 0) {
        oldItems.forEach(function (item) { addRow(item); });
    } else if (currentEditableItems.length > 0) {
        currentEditableItems.forEach(function (item) { addRow(item); });
    }
    // Kalau tidak ada item pending sama sekali, tabel dibiarkan kosong —
    // admin tinggal klik "Tambah Baris" kalau mau menambah item baru.
})();
</script>
@endsection
