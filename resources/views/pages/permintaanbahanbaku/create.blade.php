@extends('layouts.user.user')

@section('title', 'Permintaan Bahan Baku Baru')

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

    #items-table th { font-size: .75rem; text-transform: uppercase; color: #64748b; }
    #items-table td { vertical-align: middle; }
    .btn-remove-row { color: #dc3545; border-color: #f1c2c2; }
</style>
@endsection

@section('content')
@php
    $dashboardRoute = auth()->user()->role === 'admin' ? route('admin.dashboard') : route('pemilik.dashboard');
@endphp
<div class="container">

    <div class="ph-card">
        <div class="ph-left">
            <div class="ph-icon"><i class="fas fa-file-invoice"></i></div>
            <div>
                <h5 class="ph-title">Permintaan Bahan Baku Baru</h5>
                <ol class="ph-breadcrumb">
                    <li><a href="{{ $dashboardRoute }}">Dashboard</a></li>
                    <li><a href="{{ route('permintaan-bahan-baku.index') }}">Permintaan Bahan Baku</a></li>
                    <li><span class="bc-active">Buat Baru</span></li>
                </ol>
            </div>
        </div>
        <a href="{{ route('permintaan-bahan-baku.index') }}" class="btn btn-outline-secondary btn-sm">
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

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="section-divider"><i class="fas fa-info-circle me-2"></i>Data Permintaan</div>

            <form id="form-permintaan" action="{{ route('permintaan-bahan-baku.store') }}" method="POST" novalidate>
                @csrf

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Permintaan <span class="required-mark">*</span></label>
                        <input type="date" name="tanggal_permintaan" class="form-control"
                            value="{{ old('tanggal_permintaan', now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Keterangan</label>
                        <input type="text" name="keterangan" class="form-control"
                            value="{{ old('keterangan') }}" placeholder="Opsional, contoh: kebutuhan produksi minggu ini">
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="section-divider mb-0"><i class="fas fa-boxes-stacked me-2"></i>Daftar Item Diminta</div>
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
                            {{-- Baris item ditambahkan oleh JavaScript --}}
                        </tbody>
                    </table>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fas fa-save me-2"></i> Simpan Permintaan
                    </button>
                    <a href="{{ route('permintaan-bahan-baku.index') }}" class="btn btn-outline-secondary flex-fill">
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
    $bahanBakusJs = $bahanBakus->map(fn ($b) => [
        'id' => $b->id,
        'label' => $b->kode_bahan_baku . ' - ' . $b->nama_bahan_baku,
        'satuan' => optional($b->satuan)->nama,
    ]);
@endphp
<script>
(function () {
    // Master data bahan baku, dikirim dari controller.
    const bahanBakus = @json($bahanBakusJs);

    // Data lama (kalau validasi gagal, supaya baris tidak hilang).
    const oldItems = @json(old('items', []));

    // Template URL untuk ambil produsen per bahan baku. __ID__ diganti dynamic.
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

        bahanBakuSelect.addEventListener('change', function () {
            loadProdusens(this.value, produsenSelect, hargaDisplay, prefill.produsen_id);
            prefill.produsen_id = null; // hanya dipakai sekali saat prefill awal
        });

        produsenSelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const harga = selectedOption ? selectedOption.dataset.harga : null;
            hargaDisplay.value = harga ? formatRupiah(harga) : '-';
        });

        tr.querySelector('.btn-remove-row').addEventListener('click', function () {
            if (tbody.children.length === 1) {
                alert('Minimal harus ada 1 baris item.');
                return;
            }
            tr.remove();
        });

        // Jika ada prefill (dari old input / validasi gagal), muat ulang produsen-nya.
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

                // Kalau ada produsen terpilih dari prefill, tampilkan langsung harganya.
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

    document.getElementById('form-permintaan').addEventListener('submit', function (e) {
        if (tbody.children.length === 0) {
            e.preventDefault();
            alert('Minimal harus ada 1 baris item.');
        }
    });

    // Inisialisasi: pakai data lama kalau validasi sebelumnya gagal, kalau tidak mulai 1 baris kosong.
    if (oldItems && oldItems.length > 0) {
        oldItems.forEach(function (item) { addRow(item); });
    } else {
        addRow();
    }
})();
</script>
@endsection
