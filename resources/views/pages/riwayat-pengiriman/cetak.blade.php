@php
    $rupiah = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $angka  = fn ($v) => number_format((float) $v, 2, ',', '.');

    $namaToko = $toko->nama ?? null ?: 'Roti Baru Bakery';
    $kontak = array_filter([
        $toko->alamat ?? null,
        !empty($toko->nomor_telepon) ? 'Telp. ' . $toko->nomor_telepon : null,
        $toko->email ?? null,
    ]);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Riwayat Pengiriman - {{ $produsen->nama_produsen }}</title>
    <style>
        @page {
            size: A4;
            margin: 14mm 15mm;
            @bottom-left  { content: "Laporan Riwayat Pengiriman"; font: 8.5pt "Times New Roman", serif; }
            @bottom-right { content: "Halaman " counter(page); font: 8.5pt "Times New Roman", serif; }
        }

        * { box-sizing: border-box; }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 10.5pt; color: #000; background: #e5e7eb; margin: 0; line-height: 1.35;
        }
        .page { background: #fff; max-width: 210mm; margin: 56px auto 24px; padding: 14mm 15mm; box-shadow: 0 2px 12px rgba(0,0,0,.15); }

        /* Toolbar (hanya di layar, tidak ikut tercetak) */
        .toolbar {
            position: fixed; top: 0; left: 0; right: 0; background: #1e293b; padding: 10px 16px;
            display: flex; gap: 8px; justify-content: flex-end; font-family: Arial, sans-serif; z-index: 10;
        }
        .toolbar button {
            border: 0; border-radius: 6px; padding: 7px 16px; font-size: 13px; cursor: pointer;
            background: #1a73e8; color: #fff;
        }
        .toolbar button.secondary { background: #475569; }
        .toolbar .hint { color: #cbd5e1; font-size: 12px; align-self: center; margin-right: auto; }

        /* Kop */
        .kop { display: flex; align-items: center; gap: 4mm; padding-bottom: 6px; border-bottom: 3px double #000; margin-bottom: 12px; }
        .kop .logo, .kop .logo-spacer { width: 24mm; flex: 0 0 24mm; }
        .kop .logo img { display: block; width: 24mm; height: 24mm; object-fit: contain; }
        .kop .teks { flex: 1; text-align: center; }
        .kop h1 { margin: 0; font-size: 18pt; letter-spacing: .5px; }
        .kop p  { margin: 2px 0 0; font-size: 9.5pt; }

        .judul { text-align: center; margin-bottom: 10px; }
        .judul h2 { margin: 0; font-size: 13pt; text-decoration: underline; }
        .judul p  { margin: 2px 0 0; }

        table.info { border-collapse: collapse; margin-bottom: 8px; }
        table.info td { padding: 1px 0; vertical-align: top; }
        table.info td:nth-child(1) { width: 32mm; }
        table.info td:nth-child(2) { width: 5mm; }

        h3 { font-size: 11.5pt; margin: 12px 0 4px; }

        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #000; padding: 3px 6px; font-size: 10pt; }
        table.data thead th { background: #ebebeb; text-align: center; }
        table.data thead { display: table-header-group; }
        table.data tr { page-break-inside: avoid; }
        .grup td { background: #f7f7f7; font-weight: bold; }
        .c { text-align: center; } .r { text-align: right; } .b { font-weight: bold; }

        .catatan { font-size: 9pt; margin-top: 3px; }
        .kosong { text-align: center; padding: 18px 0 !important; font-style: italic; }

        .ttd { width: 42%; margin: 28px 0 0 auto; text-align: center; page-break-inside: avoid; }
        .ttd .ruang { height: 62px; }

        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .page { margin: 0; padding: 0; max-width: none; box-shadow: none; }
        }
    </style>
</head>
<body>

    <div class="toolbar">
        <span class="hint">Di dialog cetak, pilih tujuan <b>Save as PDF</b> atau printer A4</span>
        <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
        <button type="button" class="secondary" onclick="window.close()">Tutup</button>
    </div>

    <div class="page">

        {{-- ===== Kop ===== --}}
        <div class="kop">
            @if ($logoUrl)
                <div class="logo"><img src="{{ $logoUrl }}" alt="Logo"></div>
            @endif
            <div class="teks">
                <h1>{{ Str::upper($namaToko) }}</h1>
                @if (count($kontak))
                    <p>{{ implode('  |  ', $kontak) }}</p>
                @endif
            </div>
            {{-- penyeimbang agar teks tetap di tengah halaman --}}
            @if ($logoUrl)
                <div class="logo-spacer"></div>
            @endif
        </div>

        <div class="judul">
            <h2>{{ $judul }}</h2>
            <p>{{ $subjudul }}</p>
        </div>

        <table class="info">
            <tr><td>Produsen</td><td>:</td><td>{{ $produsen->nama_produsen }}</td></tr>
            <tr><td>Alamat</td><td>:</td><td>{{ $produsen->alamat }}</td></tr>
            <tr><td>No. Telp</td><td>:</td><td>{{ $produsen->no_telp }}</td></tr>
            <tr><td>Dicetak oleh</td><td>:</td><td>{{ $pencetak }}</td></tr>
            <tr><td>Tanggal cetak</td><td>:</td><td>{{ $tanggalCetak->format('d/m/Y') }}</td></tr>
        </table>

        {{-- ===== A. Rincian ===== --}}
        <h3>A. Rincian Pengiriman Diterima</h3>
        <table class="data">
            <thead>
                <tr>
                    <th style="width:10mm">No</th>
                    <th>Bahan Baku</th>
                    <th style="width:28mm">Tgl Kadaluarsa</th>
                    <th style="width:30mm">Jumlah Diterima</th>
                    <th style="width:28mm">Harga Satuan</th>
                    <th style="width:32mm">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transaksis as $t)
                    <tr class="grup">
                        <td colspan="6">
                            {{ $t['nomor'] }} &nbsp;|&nbsp; Tgl Terima: {{ $t['tanggal']->format('d/m/Y') }}
                            &nbsp;|&nbsp; Catatan: {{ $t['catatan'] ?: '-' }}
                        </td>
                    </tr>
                    @foreach ($t['items'] as $it)
                        <tr>
                            <td class="c">{{ $loop->iteration }}</td>
                            <td>{{ $it['nama'] }}</td>
                            <td class="c">{{ $it['kadaluarsa'] ? $it['kadaluarsa']->format('d/m/Y') : '-' }}</td>
                            <td class="r">{{ $angka($it['jumlah']) }} {{ $it['satuan'] }}</td>
                            <td class="r">{{ $it['harga'] !== null ? $rupiah($it['harga']) : '-' }}</td>
                            <td class="r">{{ $rupiah($it['subtotal']) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="5" class="r b">Total Nilai ({{ $t['nomor'] }})</td>
                        <td class="r b">{{ $rupiah($t['total']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="kosong">Tidak ada data pengiriman pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($transaksis->isNotEmpty())
            <div class="catatan">Catatan: subtotal = jumlah diterima × harga satuan pada permintaan.</div>
        @endif

        {{-- ===== B. Rekapitulasi ===== --}}
        @if ($tampilRekap && $rekap->isNotEmpty())
            <h3>B. Rekapitulasi per Bahan Baku</h3>
            <table class="data">
                <thead>
                    <tr>
                        <th style="width:10mm">No</th>
                        <th style="width:24mm">Kode</th>
                        <th>Nama Bahan Baku</th>
                        <th style="width:30mm">Jumlah Transaksi</th>
                        <th style="width:32mm">Total Diterima</th>
                        <th style="width:32mm">Total Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rekap as $rk)
                        <tr>
                            <td class="c">{{ $loop->iteration }}</td>
                            <td class="c">{{ $rk['kode'] }}</td>
                            <td>{{ $rk['nama'] }}</td>
                            <td class="c">{{ $rk['jumlah_transaksi'] }}</td>
                            <td class="r">{{ $angka($rk['total_jumlah']) }} {{ $rk['satuan'] }}</td>
                            <td class="r">{{ $rupiah($rk['total_nilai']) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="4" class="r b">Total</td>
                        <td class="r b">{{ $satuanTunggal ? $angka($totalJumlah) . ' ' . $satuanTunggal : '-' }}</td>
                        <td class="r b">{{ $rupiah($totalNilai) }}</td>
                    </tr>
                </tbody>
            </table>
        @endif

        {{-- ===== Tanda tangan ===== --}}
        <div class="ttd">
            <div>{{ $kota }}, {{ $tanggalCetak->copy()->locale('id')->translatedFormat('d F Y') }}</div>
            <div>Produsen</div>
            <div class="ruang"></div>
            <div>( {{ $produsen->nama_produsen }} )</div>
        </div>

    </div>

</body>
</html>
