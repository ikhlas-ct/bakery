@extends('layouts.user.user')

@section('title', 'Tanggapi Permintaan – ' . $permintaan->nomor_transaksi)

@section('styles')
<style>
    .section-divider {
        background: #f8f9fa; border-left: 4px solid #1a73e8; padding: 8px 14px;
        border-radius: 0 6px 6px 0; font-weight: 600; font-size: .9rem; color: #1a73e8; margin-bottom: 1rem;
    }
    .form-control {
        border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: .85rem; padding: 8px 12px;
        color: #334155; background-color: #f8fafc; transition: border-color .2s, box-shadow .2s;
    }
    .form-control:focus { border-color: #1a73e8; background: #fff; box-shadow: 0 0 0 3px rgba(26,115,232,.12); }
    .form-control::placeholder { color: #94a3b8; }

    .ph-card {
        background: #fff; border: 1px solid #e9ecef; border-radius: 14px; padding: 16px 20px;
        display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;
        margin-bottom: 1.25rem; position: relative; overflow: hidden; box-shadow: 0 1px 6px rgba(0,0,0,.05);
    }
    .ph-card::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; border-radius: 14px 0 0 14px; background: #1a73e8; }
    .ph-left { display: flex; align-items: center; gap: 12px; }
    .ph-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; background: #dbeafe; color: #1a73e8; }
    .ph-title { font-size: 1.15rem; font-weight: 800; color: #1e293b; margin: 0; }
    .ph-breadcrumb { list-style: none; display: flex; gap: 6px; padding: 0; margin: 2px 0 0; }
    .ph-breadcrumb li:not(:last-child)::after { content: '/'; margin-left: 6px; color: #cbd5e1; }
    .ph-breadcrumb a { color: #1a73e8; text-decoration: none; font-size: .78rem; }
    .ph-breadcrumb .bc-active { font-size: .75rem; color: #94a3b8; }

    .info-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: #94a3b8; }
    .info-value { font-size: .9rem; font-weight: 600; color: #1e293b; }

    .table { font-size: .83rem; margin-bottom: 0; }
    .table thead th {
        background: #f8fafc; color: #475569; font-weight: 700; font-size: .72rem; text-transform: uppercase;
        letter-spacing: .4px; border-bottom: 1.5px solid #eef2f6; padding: 12px 16px; white-space: nowrap;
    }
    .table tbody td { padding: 14px 16px; vertical-align: top; border-bottom: 1px solid #f4f6f8; }
    .name-text { font-weight: 700; color: #1e293b; font-size: .85rem; }
    .sub-text { color: #94a3b8; font-size: .74rem; }
    .badge-kode { background: #eef2ff; color: #4338ca; font-weight: 600; font-size: .72rem; padding: 5px 10px; border-radius: 8px; }

    .chip { font-weight: 600; font-size: .72rem; padding: 5px 10px; border-radius: 8px; display: inline-block; }
    .chip-pending   { background: #fff7ed; color: #c2410c; }
    .chip-disetujui { background: #f0fdf4; color: #166534; }
    .chip-sebagian  { background: #eff6ff; color: #1d4ed8; }
    .chip-ditolak   { background: #fef2f2; color: #dc2626; }

    .qty-box { max-width: 190px; }
    .qty-actions { display: flex; gap: 6px; margin-top: 6px; }
    .btn-mini { font-size: .7rem; font-weight: 600; padding: 3px 10px; border-radius: 8px; }

    .kurang-note {
        display: none; margin-top: 8px; padding: 8px 12px; border-radius: 10px;
        background: #fff7ed; color: #9a3412; font-size: .76rem; border: 1px solid #fed7aa;
    }
    .kurang-note.show { display: block; }


    .alert { border-radius: 12px; border: none; font-size: .85rem; }
    .alert-success { background: #dcfce7; color: #166534; }
    .alert-danger { background: #fee2e2; color: #991b1b; }
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
@endphp
<div class="container">

    {{-- ── Page Header ── --}}
    <div class="ph-card">
        <div class="ph-left">
            <div class="ph-icon"><i class="fas fa-reply"></i></div>
            <div>
                <h5 class="ph-title">Tanggapi Permintaan</h5>
                <ol class="ph-breadcrumb">
                    <li><a href="{{ route('produsen.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('produsen.permintaan.index') }}">Permintaan Masuk</a></li>
                    <li><span class="bc-active">{{ $permintaan->nomor_transaksi }}</span></li>
                </ol>
            </div>
        </div>
        <a href="{{ route('produsen.permintaan.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success d-flex align-items-center mb-3 gap-2">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if ($errors->has('items'))
        <div class="alert alert-danger d-flex align-items-center mb-3 gap-2">
            <i class="fas fa-exclamation-circle"></i> {{ $errors->first('items') }}
        </div>
    @elseif ($errors->any())
        <div class="alert alert-danger d-flex align-items-center mb-3 gap-2">
            <i class="fas fa-exclamation-circle"></i> Ada isian yang belum valid, periksa kembali.
        </div>
    @endif

    {{-- ── Info Permintaan ── --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="section-divider"><i class="fas fa-file-lines me-2"></i>Informasi Permintaan</div>
            <div class="row g-3">
                <div class="col-md-3 col-6">
                    <div class="info-label">Nomor Transaksi</div>
                    <div class="info-value"><span class="badge-kode">{{ $permintaan->nomor_transaksi }}</span></div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="info-label">Tanggal Permintaan</div>
                    <div class="info-value">{{ $permintaan->tanggal_permintaan->translatedFormat('d F Y') }}</div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="info-label">Diminta Oleh</div>
                    <div class="info-value">{{ $permintaan->admin->user->username ?? '-' }}</div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="info-label">Catatan Admin</div>
                    <div class="info-value" style="font-weight:500;">{{ $permintaan->keterangan ?: '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Form Tanggapan ── --}}
    <form id="form-respon" action="{{ route('produsen.permintaan.respon', $permintaan->id) }}" method="POST" novalidate>
        @csrf
        @method('PUT')

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="section-divider"><i class="fas fa-boxes-stacked me-2"></i>Item Yang Diminta Kepada Anda</div>

                @if ($adaPending)
                    <p class="text-muted" style="font-size:.8rem;">
                        Isi jumlah yang <strong>sanggup Anda sediakan</strong> untuk tiap item. Isi <strong>0</strong> bila tidak sanggup.
                        Item yang dikosongkan tetap berstatus menunggu.
                    </p>
                @endif

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="40">#</th>
                                <th>Bahan Baku</th>
                                <th class="text-end">Harga Satuan</th>
                                <th class="text-end">Diminta</th>
                                <th>Tanggapan Anda</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($details as $i => $detail)
                                @php
                                    [$cls, $label] = $badge[$detail->status_produsen];
                                    $satuan = $detail->bahanBaku->satuan->nama ?? '';
                                    $fieldKey = "items.{$detail->id}.jumlah_disetujui";
                                @endphp
                                <tr>
                                    <td class="text-muted">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="name-text">{{ $detail->bahanBaku->nama_bahan_baku }}</div>
                                        <span class="badge-kode">{{ $detail->bahanBaku->kode_bahan_baku }}</span>
                                    </td>
                                    <td class="text-end">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}
                                        <div class="sub-text">per {{ $satuan }}</div>
                                    </td>
                                    <td class="text-end">
                                        <div class="name-text">{{ $fmt($detail->jumlah_diminta) }}</div>
                                        <div class="sub-text">{{ $satuan }}</div>
                                    </td>
                                    <td>
                                        @if ($detail->status_produsen === 'pending')
                                            <div class="qty-box">
                                                <div class="input-group input-group-sm">
                                                    <input type="number" step="0.01" min="0" max="{{ $detail->jumlah_diminta }}"
                                                        name="items[{{ $detail->id }}][jumlah_disetujui]"
                                                        class="form-control qty-input @error($fieldKey) is-invalid @enderror"
                                                        data-diminta="{{ $detail->jumlah_diminta }}"
                                                        data-satuan="{{ $satuan }}"
                                                        value="{{ old("items.{$detail->id}.jumlah_disetujui") }}"
                                                        placeholder="0.00">
                                                    <span class="input-group-text" style="font-size:.75rem;">{{ $satuan }}</span>
                                                    @error($fieldKey)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>
                                                <div class="qty-actions">
                                                    <button type="button" class="btn btn-outline-success btn-mini btn-penuh">
                                                        <i class="fas fa-check me-1"></i>Penuh
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-mini btn-tolak">
                                                        <i class="fas fa-times me-1"></i>Tolak
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="kurang-note">
                                                <input type="text" maxlength="200"
                                                    name="items[{{ $detail->id }}][alasan]"
                                                    class="form-control form-control-sm"
                                                    value="{{ old("items.{$detail->id}.alasan") }}"
                                                    placeholder="Alasan (opsional), mis. stok habis">
                                            </div>
                                        @else
                                            <span class="chip {{ $cls }}">{{ $label }}</span>
                                            <div class="sub-text mt-1">
                                                Disetujui: <strong>{{ $fmt($detail->jumlah_disetujui) }}</strong> {{ $satuan }}
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($adaPending)
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="fas fa-paper-plane me-2"></i> Kirim Tanggapan
                        </button>
                        <a href="{{ route('produsen.permintaan.index') }}" class="btn btn-outline-secondary flex-fill">Batal</a>
                    </div>
                @endif
            </div>
        </div>
    </form>

</div>
@endsection

@section('scripts')
<script>
    const fmt = n => (Math.round(n * 100) / 100).toLocaleString('id-ID', { maximumFractionDigits: 2 });

    function hitungKurang(input) {
        const diminta = parseFloat(input.dataset.diminta);
        const row     = input.closest('td');
        const note    = row.querySelector('.kurang-note');
        const raw     = input.value.trim();

        if (raw === '' || isNaN(parseFloat(raw))) { note.classList.remove('show'); return; }

        const kurang = Math.round((diminta - parseFloat(raw)) * 100) / 100;
        if (kurang > 0) {
            note.classList.add('show');
        } else {
            note.classList.remove('show');
        }
    }

    document.querySelectorAll('.qty-input').forEach(input => {
        input.addEventListener('input', () => hitungKurang(input));
        hitungKurang(input); // pulihkan tampilan setelah validasi gagal (old input)
    });

    document.querySelectorAll('.btn-penuh').forEach(btn => btn.addEventListener('click', function () {
        const input = this.closest('td').querySelector('.qty-input');
        input.value = input.dataset.diminta;
        hitungKurang(input);
    }));

    document.querySelectorAll('.btn-tolak').forEach(btn => btn.addEventListener('click', function () {
        const input = this.closest('td').querySelector('.qty-input');
        input.value = 0;
        hitungKurang(input);
    }));

    // Konfirmasi sebelum kirim: tanggapan tidak bisa diubah lagi.
    const form = document.getElementById('form-respon');
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const terisi  = [...document.querySelectorAll('.qty-input')].filter(i => i.value.trim() !== '').length;

        if (terisi === 0) {
            swal({ title: 'Belum ada yang diisi', text: 'Isi jumlah minimal untuk satu item.', icon: 'warning' });
            return;
        }

        swal({
            title: 'Kirim Tanggapan?',
            text: `${terisi} item akan ditanggapi. Tanggapan tidak dapat diubah setelah dikirim.`,
            icon: 'info',
            buttons: { cancel: 'Periksa Lagi', confirm: { text: 'Ya, Kirim' } },
        }).then(ok => { if (ok) form.submit(); });
    });
</script>
@endsection
