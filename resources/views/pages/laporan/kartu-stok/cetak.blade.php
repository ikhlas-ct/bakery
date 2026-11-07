<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kartu Stok — {{ $bahan->nama_bahan_baku }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif; font-size: 12px; color: #111;
            margin: 0; background: #f1f5f9;
        }
        .toolbar {
            position: sticky; top: 0; z-index: 10; background: #fff; border-bottom: 1px solid #e2e8f0;
            padding: 10px 20px; display: flex; gap: 8px; justify-content: flex-end;
        }
        .toolbar button {
            border: none; border-radius: 8px; padding: 8px 16px; font-size: 13px; font-weight: 600; cursor: pointer;
        }
        .btn-print { background: #16a34a; color: #fff; }
        .btn-close { background: #e2e8f0; color: #334155; }

        .page {
            width: 210mm; min-height: 297mm; margin: 16px auto; background: #fff;
            padding: 15mm; box-shadow: 0 2px 12px rgba(0, 0, 0, .12);
        }

        /* Kop */
        .kop { display: flex; align-items: center; gap: 14px; border-bottom: 3px double #111; padding-bottom: 10px; }
        .kop img { height: 60px; width: auto; }
        .kop-text { flex: 1; text-align: center; }
        .kop-nama { font-size: 20px; font-weight: 800; letter-spacing: .5px; text-transform: uppercase; }
        .kop-info { font-size: 11px; color: #333; margin-top: 2px; }

        .judul { text-align: center; margin: 16px 0 12px; }
        .judul h2 { margin: 0; font-size: 16px; letter-spacing: .5px; text-decoration: underline; }

        .info { margin-bottom: 14px; }
        .info td { padding: 2px 0; vertical-align: top; }
        .info td:first-child { width: 120px; }
        .info td:nth-child(2) { width: 14px; }

        h3 { font-size: 13px; margin: 16px 0 6px; }

        table.lap { width: 100%; border-collapse: collapse; }
        table.lap th, table.lap td { border: 1px solid #444; padding: 5px 7px; vertical-align: top; }
        table.lap thead th { background: #e5e7eb; text-align: center; font-size: 11px; }
        table.lap tr.saldo td { background: #f3f4f6; font-weight: 700; }
        table.lap tr.total td { font-weight: 800; background: #f3f4f6; }
        .c { text-align: center; }
        .r { text-align: right; white-space: nowrap; }
        .nw { white-space: nowrap; }
        .catatan { font-size: 11px; font-style: italic; margin-top: 6px; }
        .kosong { text-align: center; padding: 18px; font-style: italic; }

        /* Tanda tangan */
        .ttd { display: flex; justify-content: space-between; margin-top: 36px; page-break-inside: avoid; }
        .ttd .box { width: 45%; text-align: center; }
        .ttd .ruang { height: 70px; }
        .ttd .nama { font-weight: 700; border-top: 0; }
        .ttd .garis { display: inline-block; min-width: 160px; border-bottom: 1px solid #111; }

        @page { size: A4; margin: 12mm; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .page { margin: 0; width: auto; min-height: 0; padding: 0; box-shadow: none; }
            thead { display: table-header-group; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    @php
        $qty = fn ($v) => number_format((float) $v, 2, ',', '.');
        $sat = mb_strtolower($bahan->satuan?->kode_satuan ?: ($bahan->satuan?->nama ?? ''));
        $tgl = fn ($d) => $d ? $d->format('d/m/Y') : '-';
        $tanggalCetak = now()->locale('id');
        $namaAdmin = $pencetak->role === 'admin' ? $pencetak->username : null;
        $namaToko = $setting?->nama ?: 'Roti Baru Bakery';
        $kontak = collect([
            $setting?->alamat,
            $setting?->nomor_telepon ? 'Telp. ' . $setting->nomor_telepon : null,
            $setting?->email,
        ])->filter()->implode(' | ');
        $statusBatch = [
            'kadaluarsa' => 'Kadaluarsa',
            'segera'     => 'Segera kadaluarsa',
            'aman'       => 'Aman',
            'tanpa'      => 'Tanpa kadaluarsa',
        ];
    @endphp

    <div class="toolbar">
        <button type="button" class="btn-close" onclick="window.close()">Tutup</button>
        <button type="button" class="btn-print" onclick="window.print()">Cetak</button>
    </div>

    <div class="page">
        {{-- Kop --}}
        <div class="kop">
            @if ($setting?->logo)
                <img src="{{ $setting->logo_url }}" alt="Logo">
            @endif
            <div class="kop-text">
                <div class="kop-nama">{{ $namaToko }}</div>
                @if ($kontak)
                    <div class="kop-info">{{ $kontak }}</div>
                @endif
            </div>
        </div>

        <div class="judul"><h2>KARTU STOK BAHAN BAKU</h2></div>

        <table class="info">
            <tr><td>Bahan Baku</td><td>:</td><td>{{ $bahan->kode_bahan_baku }} — {{ $bahan->nama_bahan_baku }}</td></tr>
            <tr><td>Kategori</td><td>:</td><td>{{ $bahan->kategoriBarang->nama_kategori ?? '-' }}</td></tr>
            <tr><td>Satuan</td><td>:</td><td>{{ $bahan->satuan->nama ?? '-' }}{{ $sat ? ' (' . $sat . ')' : '' }}</td></tr>
            <tr><td>Periode</td><td>:</td><td>{{ $dari->format('d/m/Y') }} s/d {{ $sampai->format('d/m/Y') }}</td></tr>
            <tr><td>Dicetak oleh</td><td>:</td><td>{{ $pencetak->username }}</td></tr>
            <tr><td>Tanggal cetak</td><td>:</td><td>{{ $tanggalCetak->format('d/m/Y') }}</td></tr>
        </table>

        {{-- A. Kartu stok --}}
        <h3>A. Mutasi Stok</h3>
        <table class="lap">
            <thead>
                <tr>
                    <th width="30">No</th>
                    <th>Tanggal</th>
                    <th>No. Transaksi</th>
                    <th>Keterangan</th>
                    <th>Batch (Kadaluarsa)</th>
                    <th>Masuk</th>
                    <th>Keluar</th>
                    <th>Saldo</th>
                </tr>
            </thead>
            <tbody>
                <tr class="saldo">
                    <td colspan="7">Saldo awal per {{ $dari->format('d/m/Y') }}</td>
                    <td class="r">{{ $qty($kartu['saldo_awal']) }} {{ $sat }}</td>
                </tr>
                @forelse ($kartu['rows'] as $row)
                    <tr>
                        <td class="c">{{ $loop->iteration }}</td>
                        <td class="c nw">{{ $tgl($row->tanggal) }}</td>
                        <td class="nw">{{ $row->nomor }}</td>
                        <td>{{ $row->keterangan }}</td>
                        <td>
                            @if ($row->batch_nomor)
                                {{ $row->batch_nomor }}<br>
                                <span style="font-size:10px;">Exp. {{ $tgl($row->batch_kadaluarsa) }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="r">{{ $row->masuk > 0 ? $qty($row->masuk) : '-' }}</td>
                        <td class="r">{{ $row->keluar > 0 ? $qty($row->keluar) : '-' }}</td>
                        <td class="r">{{ $qty($row->saldo) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="kosong">Tidak ada mutasi stok pada periode ini.</td></tr>
                @endforelse
                <tr class="total">
                    <td colspan="5" class="r">Total / Saldo akhir</td>
                    <td class="r">{{ $qty($kartu['total_masuk']) }}</td>
                    <td class="r">{{ $qty($kartu['total_keluar']) }}</td>
                    <td class="r">{{ $qty($kartu['saldo_akhir']) }} {{ $sat }}</td>
                </tr>
            </tbody>
        </table>
        <div class="catatan">Catatan: pada tanggal yang sama, penerimaan dicatat lebih dahulu daripada pemakaian.</div>

        {{-- B. Posisi batch FEFO --}}
        <h3>B. Stok per Batch Penerimaan (FEFO) — posisi {{ $sampai->format('d/m/Y') }}</h3>
        <table class="lap">
            <thead>
                <tr>
                    <th width="30">FEFO</th>
                    <th>Batch Penerimaan</th>
                    <th>Produsen</th>
                    <th>Tgl Terima</th>
                    <th>Kadaluarsa</th>
                    <th>Status</th>
                    <th>Diterima</th>
                    <th>Terpakai</th>
                    <th>Sisa</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($batches as $bt)
                    <tr>
                        <td class="c">{{ $bt->urutan }}</td>
                        <td class="nw">{{ $bt->nomor_penerimaan }}</td>
                        <td>{{ $bt->produsen }}</td>
                        <td class="c">{{ $tgl($bt->tanggal_terima) }}</td>
                        <td class="c">{{ $tgl($bt->tanggal_kadaluarsa) }}</td>
                        <td class="c">{{ $statusBatch[$bt->status] }}</td>
                        <td class="r">{{ $qty($bt->diterima) }}</td>
                        <td class="r">{{ $qty($bt->dipakai) }}</td>
                        <td class="r">{{ $qty($bt->sisa) }} {{ $sat }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="kosong">Tidak ada batch yang masih bersisa.</td></tr>
                @endforelse
                @if ($batches->isNotEmpty())
                    <tr class="total">
                        <td colspan="8" class="r">Total sisa semua batch</td>
                        <td class="r">{{ $qty($batches->sum('sisa')) }} {{ $sat }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
        <div class="catatan">
            Catatan: FEFO (First Expired, First Out) — batch dengan tanggal kadaluarsa terdekat dipakai lebih dahulu (urutan 1).
        </div>

        {{-- Tanda tangan --}}
        <div class="ttd">
            <div class="box">
                <div>Mengetahui,</div>
                <div>Pemilik</div>
                <div class="ruang"></div>
                <div class="nama">( <span class="garis">&nbsp;</span> )</div>
            </div>
            <div class="box">
                <div>{{ $kota }}, {{ $tanggalCetak->translatedFormat('d F Y') }}</div>
                <div>Admin Gudang</div>
                <div class="ruang"></div>
                <div class="nama">
                    @if ($namaAdmin)
                        {{ $namaAdmin }}
                    @else
                        ( <span class="garis">&nbsp;</span> )
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($autoPrint)
        <script>window.addEventListener('load', function () { window.print(); });</script>
    @endif
</body>
</html>
