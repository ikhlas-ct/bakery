@extends('layouts.user.user')

@section('title', 'Edit Pemakaian – ' . $pemakaian->nomor_transaksi)

@section('styles')
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    body, .card, .table, .btn, h4, h5 { font-family: 'Plus Jakarta Sans', sans-serif; }

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
    .ph-breadcrumb a:hover { text-decoration: underline; }
    .ph-breadcrumb .bc-active { font-size: .78rem; color: #94a3b8; }

    /* ===== FORM ===== */
    .section-divider {
        background: #f8f9fa; border-left: 4px solid #1a73e8; padding: 8px 14px;
        border-radius: 0 6px 6px 0; font-weight: 600; font-size: .9rem; color: #1a73e8; margin-bottom: 1rem;
    }
    .required-mark { color: #dc3545; }
    label { font-size: .875rem; font-weight: 500; }

    .form-control, .form-select {
        border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: .85rem;
        padding: 8px 12px; color: #334155; background-color: #f8fafc;
        transition: border-color .2s, box-shadow .2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: #1a73e8; background: #fff; box-shadow: 0 0 0 3px rgba(26,115,232,.12);
    }
    .form-control::placeholder { color: #94a3b8; }
    .form-control[readonly] { background-color: #f1f5f9; color: #64748b; }

    /* ===== BARIS ITEM + PRATINJAU FEFO ===== */
    .item-row {
        border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 14px 16px;
        margin-bottom: 12px; background: #fff;
    }
    .item-row.has-error { border-color: #fca5a5; background: #fffafa; }
    .item-row .unit-addon {
        border-radius: 0 10px 10px 0; border: 1.5px solid #e2e8f0; border-left: 0;
        background: #f1f5f9; color: #64748b; font-size: .8rem; font-weight: 600; min-width: 52px; justify-content: center;
    }
    .item-row .input-group > .form-control { border-radius: 10px 0 0 10px; }
    .item-error { color: #dc2626; font-size: .78rem; margin-top: 6px; }

    .fefo-box { margin-top: 12px; }
    .fefo-head { font-size: .78rem; color: #475569; margin-bottom: 6px; }
    .fefo-warn {
        background: #fef2f2; color: #b91c1c; border-radius: 10px;
        padding: 8px 12px; font-size: .78rem; margin-top: 8px;
    }
    .fefo-table { width: 100%; font-size: .76rem; border-collapse: separate; border-spacing: 0; }
    .fefo-table th {
        background: #f8fafc; color: #64748b; font-weight: 700; font-size: .68rem;
        text-transform: uppercase; letter-spacing: .3px; padding: 7px 10px; white-space: nowrap;
    }
    .fefo-table th:first-child { border-radius: 8px 0 0 8px; }
    .fefo-table th:last-child  { border-radius: 0 8px 8px 0; }
    .fefo-table td { padding: 7px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .fefo-table tr.take td { background: #eff6ff; }
    .fefo-table .take-qty { color: #1a73e8; font-weight: 700; white-space: nowrap; }
    .fefo-table .rank {
        display: inline-flex; width: 20px; height: 20px; border-radius: 50%;
        align-items: center; justify-content: center; background: #e2e8f0;
        color: #475569; font-size: .65rem; font-weight: 700;
    }
    .fefo-table tr.take .rank { background: #1a73e8; color: #fff; }

    .alert { border-radius: 12px; border: none; font-size: .85rem; }
    .alert-success { background: #dcfce7; color: #166534; }
    .alert-danger  { background: #fee2e2; color: #991b1b; }
    .alert-info-soft { background: #eff6ff; color: #1e40af; }

    /* ===== BADGE & TEKS KECIL (dipakai pratinjau FEFO) ===== */
    .sub-text { color: #94a3b8; font-size: .74rem; }
    .badge-kritis {
        font-weight: 600; font-size: .72rem; padding: 5px 10px; border-radius: 8px;
        display: inline-block; background: #fef2f2; color: #dc2626;
    }
    </style>
@endsection

@section('content')
<div class="container">

    {{-- ── Page Header ── --}}
    <div class="ph-card">
        <div class="ph-left">
            <div class="ph-icon"><i class="fas fa-utensils"></i></div>
            <div>
                <h5 class="ph-title">Edit Pemakaian Bahan Baku</h5>
                <ol class="ph-breadcrumb">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('pemakaian-bahan-baku.index') }}">Pemakaian Bahan Baku</a></li>
                    <li><a href="{{ route('pemakaian-bahan-baku.show', $pemakaian->id) }}">{{ $pemakaian->nomor_transaksi }}</a></li>
                    <li><span class="bc-active">Edit</span></li>
                </ol>
            </div>
        </div>
        <a href="{{ route('pemakaian-bahan-baku.show', $pemakaian->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if (session('error'))
        <div class="alert alert-danger d-flex align-items-center mb-3 gap-2">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    <div class="alert alert-warning py-2 mb-3" style="border-radius:12px;font-size:.82rem;">
        <i class="fas fa-triangle-exclamation me-1"></i>
        Saat disimpan, stok lama transaksi ini <strong>dikembalikan ke batch asalnya</strong>, lalu bahan baku
        dialokasikan ulang dengan urutan FEFO terbaru.
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('pemakaian-bahan-baku.update', $pemakaian->id) }}" method="POST" novalidate id="form-pemakaian">
                        @csrf
                        @method('PUT')

                        <div class="section-divider"><i class="fas fa-file-alt me-2"></i>Informasi Transaksi</div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Nomor Transaksi</label>
                                <input type="text" class="form-control" readonly
                                    value="{{ $pemakaian->nomor_transaksi }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Tanggal Pakai <span class="required-mark">*</span></label>
                                <input type="date" name="tanggal_pakai" max="{{ now()->toDateString() }}"
                                    class="form-control @error('tanggal_pakai') is-invalid @enderror"
                                    value="{{ old('tanggal_pakai', $pemakaian->tanggal_pakai->toDateString()) }}"
                                    required>
                                @error('tanggal_pakai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-5">
                                <label class="form-label">Keterangan</label>
                                <input type="text" name="keterangan" maxlength="255"
                                    class="form-control @error('keterangan') is-invalid @enderror"
                                    value="{{ old('keterangan', $pemakaian->keterangan) }}"
                                    placeholder="Contoh: Produksi roti tawar pagi">
                                @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="section-divider"><i class="fas fa-boxes-stacked me-2"></i>Bahan Baku yang Dipakai</div>

                        <div class="alert alert-info-soft d-flex gap-2 align-items-start py-2 mb-3">
                            <i class="fas fa-circle-info mt-1"></i>
                            <div style="font-size:.8rem;">
                                Stok diambil <strong>otomatis dengan metode FEFO</strong>: batch dengan
                                <strong>tanggal penerimaan paling lama</strong> dipakai lebih dulu. Jika satu batch tidak cukup,
                                sisanya diambil dari batch berikutnya. Pratinjau pengambilan muncul di bawah setiap baris.
                            </div>
                        </div>

                        @error('items')
                            <div class="alert alert-danger py-2">{{ $message }}</div>
                        @enderror

                        <div id="items-container"></div>

                        <button type="button" id="btn-tambah-item" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-plus me-1"></i> Tambah Bahan Baku
                        </button>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary flex-fill" id="btn-simpan">
                                <i class="fas fa-save me-2"></i> Simpan Perubahan
                            </button>
                            <a href="{{ route('pemakaian-bahan-baku.show', $pemakaian->id) }}"
                                class="btn btn-outline-secondary flex-fill">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@php
    $bahanOptions = $bahanBakus->map(fn ($b) => [
        'id'     => $b->id,
        'label'  => $b->kode_bahan_baku . ' – ' . $b->nama_bahan_baku . ($b->is_aktif ? '' : ' (nonaktif)'),
        'satuan' => $b->satuan->kode_satuan ?: $b->satuan->nama,
    ])->values();

    // Prioritaskan input lama (setelah gagal validasi), lalu data awal (edit), lalu satu baris kosong.
    $barisAwal = collect(old('items', $initialItems))
        ->values()
        ->map(fn ($r, $i) => [
            'bahan_baku_id'  => $r['bahan_baku_id'] ?? '',
            'jumlah_dipakai' => $r['jumlah_dipakai'] ?? '',
            'error'          => $errors->first("items.$i.bahan_baku_id") ?: $errors->first("items.$i.jumlah_dipakai"),
        ])->all();

    if (empty($barisAwal)) {
        $barisAwal = [['bahan_baku_id' => '', 'jumlah_dipakai' => '', 'error' => null]];
    }
@endphp

<script>
    (function () {
        const BAHAN       = @json($bahanOptions);
        const BARIS_AWAL  = @json($barisAwal);
        const URL_BATCH   = @json(route('pemakaian-bahan-baku.batch', '__ID__'));
        const PEMAKAIAN_ID = @json($pemakaian->id); // dipakai agar stok transaksi ini dihitung "tersedia" lagi

        const container = document.getElementById('items-container');
        const btnTambah = document.getElementById('btn-tambah-item');
        const form      = document.getElementById('form-pemakaian');
        const cache     = {};   // cache hasil AJAX per bahan baku
        let counter     = 0;

        const nf  = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 });
        const sen = v => Math.round((parseFloat(v) || 0) * 100);
        const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

        function optionsHtml(selected) {
            let html = '<option value="">-- Pilih Bahan Baku --</option>';
            BAHAN.forEach(b => {
                html += `<option value="${b.id}" ${String(b.id) === String(selected) ? 'selected' : ''}>${esc(b.label)}</option>`;
            });
            return html;
        }

        function tambahBaris(data = {}) {
            const i = counter++;
            const row = document.createElement('div');
            row.className = 'item-row' + (data.error ? ' has-error' : '');
            row.innerHTML = `
                <div class="row g-2 align-items-start">
                    <div class="col-md-6">
                        <label class="form-label mb-1">Bahan Baku <span class="required-mark">*</span></label>
                        <select name="items[${i}][bahan_baku_id]" class="form-select" required>
                            ${optionsHtml(data.bahan_baku_id)}
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label mb-1">Jumlah Dipakai <span class="required-mark">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0.01" name="items[${i}][jumlah_dipakai]"
                                class="form-control" placeholder="0.00" required value="${esc(data.jumlah_dipakai)}">
                            <span class="input-group-text unit-addon">–</span>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end justify-content-md-end">
                        <button type="button" class="btn btn-outline-danger btn-sm btn-hapus-baris">
                            <i class="fas fa-trash-alt me-1"></i> Hapus
                        </button>
                    </div>
                </div>
                ${data.error ? `<div class="item-error"><i class="fas fa-exclamation-circle me-1"></i>${esc(data.error)}</div>` : ''}
                <div class="fefo-box"></div>
            `;
            container.appendChild(row);

            const select = row.querySelector('select');
            const qty    = row.querySelector('input[type=number]');

            select.addEventListener('change', async () => {
                row.classList.remove('has-error');
                row.querySelector('.item-error')?.remove();
                await muatBatch(select.value);
                render(row);
                segarkanPilihan();
            });
            qty.addEventListener('input', () => render(row));
            row.querySelector('.btn-hapus-baris').addEventListener('click', () => {
                if (container.children.length === 1) {
                    select.value = ''; qty.value = '';
                    render(row);
                } else {
                    row.remove();
                }
                segarkanPilihan();
            });

            if (data.bahan_baku_id) {
                muatBatch(data.bahan_baku_id).then(() => render(row));
            }
            return row;
        }

        async function muatBatch(id) {
            if (!id || cache[id]) return cache[id] || null;
            try {
                const url = URL_BATCH.replace('__ID__', id) + (PEMAKAIAN_ID ? `?pemakaian_id=${PEMAKAIAN_ID}` : '');
                const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                cache[id] = await res.json();
            } catch (e) {
                cache[id] = null;
            }
            return cache[id];
        }

        function render(row) {
            const select = row.querySelector('select');
            const qty    = row.querySelector('input[type=number]');
            const box    = row.querySelector('.fefo-box');
            const unit   = row.querySelector('.unit-addon');
            const data   = cache[select.value];

            if (!select.value) { box.innerHTML = ''; unit.textContent = '–'; return; }
            if (!data) {
                box.innerHTML = '<div class="fefo-warn">Gagal memuat data batch. Muat ulang halaman lalu coba lagi.</div>';
                return;
            }

            unit.textContent = data.satuan || '–';

            if (!data.batches.length) {
                box.innerHTML = '<div class="fefo-warn"><i class="fas fa-triangle-exclamation me-1"></i>Belum ada stok batch yang tersedia untuk bahan baku ini.</div>';
                return;
            }

            const butuh = sen(qty.value);
            let sisa = butuh;
            let baris = '';

            data.batches.forEach((b, idx) => {
                const ambil = Math.min(sisa, sen(b.tersedia));
                sisa -= ambil;

                const exp = b.tanggal_kadaluarsa
                    ? esc(b.tanggal_kadaluarsa) + (b.kadaluarsa ? ' <span class="badge-kritis" style="padding:2px 6px;font-size:.65rem;">Kadaluarsa</span>' : '')
                    : '–';

                baris += `
                    <tr class="${ambil > 0 ? 'take' : ''}">
                        <td><span class="rank">${idx + 1}</span></td>
                        <td>${esc(b.nomor || '-')}<div class="sub-text">${esc(b.produsen || '')}</div></td>
                        <td>${esc(b.tanggal_terima || '-')}</td>
                        <td>${exp}</td>
                        <td class="text-end">${nf.format(b.tersedia)}</td>
                        <td class="text-end take-qty">${ambil > 0 ? '− ' + nf.format(ambil / 100) : '–'}</td>
                    </tr>`;
            });

            const kurang = (butuh > 0 && sisa > 0)
                ? `<div class="fefo-warn"><i class="fas fa-triangle-exclamation me-1"></i>
                       Stok tidak mencukupi, kurang <strong>${nf.format(sisa / 100)} ${esc(data.satuan)}</strong>.</div>`
                : '';

            box.innerHTML = `
                <div class="fefo-head">
                    Stok tersedia: <strong>${nf.format(data.total)} ${esc(data.satuan)}</strong>
                    dalam ${data.batches.length} batch · urutan pemakaian (FEFO):
                </div>
                <div class="table-responsive">
                    <table class="fefo-table">
                        <thead><tr>
                            <th>#</th><th>Batch Penerimaan</th><th>Tgl Terima</th><th>Kadaluarsa</th>
                            <th class="text-end">Tersedia</th><th class="text-end">Akan Dipakai</th>
                        </tr></thead>
                        <tbody>${baris}</tbody>
                    </table>
                </div>
                ${kurang}`;
        }

        // Satu bahan baku hanya boleh muncul di satu baris: nonaktifkan opsi yang sudah dipilih baris lain.
        function segarkanPilihan() {
            const dipilih = [...container.querySelectorAll('select')].map(s => s.value).filter(Boolean);
            container.querySelectorAll('select').forEach(s => {
                [...s.options].forEach(o => {
                    o.disabled = o.value !== '' && o.value !== s.value && dipilih.includes(o.value);
                });
            });
        }

        // Rapikan indeks name="items[n]" agar berurutan sebelum dikirim (kunci error server = urutan baris).
        form.addEventListener('submit', () => {
            container.querySelectorAll('.item-row').forEach((row, i) => {
                row.querySelector('select').name = `items[${i}][bahan_baku_id]`;
                row.querySelector('input[type=number]').name = `items[${i}][jumlah_dipakai]`;
            });
            const btn = document.getElementById('btn-simpan');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan…';
        });

        btnTambah.addEventListener('click', () => {
            tambahBaris().scrollIntoView({ behavior: 'smooth', block: 'center' });
            segarkanPilihan();
        });

        BARIS_AWAL.forEach(r => tambahBaris(r));
        segarkanPilihan();
    })();
</script>
@endsection
