<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Penerimaan Bahan Baku – {{ $periode['label'] }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        @page { size: A4; margin: 14mm 14mm 16mm; }

        * { box-sizing: border-box; }
        body {
            margin: 0; padding: 0; color: #1e293b; background: #f1f5f9;
            font-family: 'Plus Jakarta Sans', 'Segoe UI', Arial, sans-serif; font-size: 11px; line-height: 1.45;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        .toolbar {
            position: sticky; top: 0; z-index: 10; background: #1e293b; color: #fff;
            padding: 10px 20px; display: flex; gap: 8px; align-items: center; justify-content: space-between;
        }
        .toolbar button, .toolbar a {
            background: #1a73e8; color: #fff; border: 0; border-radius: 8px; padding: 7px 14px;
            font-size: 12px; font-weight: 600; cursor: pointer; text-decoration: none;
        }
        .toolbar .ghost { background: transparent; border: 1px solid #64748b; }

        .page { background: #fff; width: 210mm; min-height: 297mm; margin: 16px auto; padding: 14mm; box-shadow: 0 2px 16px rgba(0,0,0,.12); }

        /* Kop */
        .kop { position: relative; min-height: 62px; padding-bottom: 10px; border-bottom: 2.5px solid #1e293b; text-align: center; }
        .kop img { position: absolute; left: 0; top: 0; width: 62px; height: 62px; object-fit: contain; }
        .kop .teks { padding: 0 76px; /* sisi kiri & kanan sama lebar supaya teks benar-benar di tengah */ min-height: 62px; display: flex; flex-direction: column; justify-content: center; }
        .kop h1 { margin: 0; font-size: 18px; letter-spacing: .6px; }
        .kop p { margin: 2px 0 0; color: #475569; }

        h2.judul { text-align: center; font-size: 14px; margin: 16px 0 2px; letter-spacing: .5px; }
        .sub-judul { text-align: center; color: #64748b; margin: 0 0 14px; }

        .meta { width: 100%; margin-bottom: 12px; }
        .meta td { padding: 1.5px 0; vertical-align: top; }
        .meta td:first-child { width: 110px; color: #64748b; }
        .meta td:nth-child(2) { width: 12px; }

        .ringkasan { display: flex; gap: 10px; margin-bottom: 14px; }
        .ringkasan div { flex: 1; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 10px; }
        .ringkasan span { display: block; color: #64748b; font-size: 10px; }
        .ringkasan strong { font-size: 13px; }

        .rpt-table { width: 100%; border-collapse: collapse; }
        .rpt-table th { background: #f1f5f9; text-align: left; padding: 6px 8px; border: 1px solid #cbd5e1; font-size: 10px; }
        .rpt-table td { padding: 5px 8px; border: 1px solid #e2e8f0; }
        .rpt-table tr.rpt-group td { background: #eef2ff; border-color: #c7d2fe; }
        .rpt-table tfoot td { background: #f1f5f9; border-top: 1.5px solid #94a3b8; }
        .rpt-table tr { page-break-inside: avoid; }
        .rpt-muted { color: #64748b; font-weight: 400; }
        .rpt-empty { text-align: center; padding: 24px !important; color: #64748b; }
        .text-end { text-align: right; }

        .ttd { display: flex; justify-content: space-around; margin-top: 34px; page-break-inside: avoid; }
        .ttd > div { width: 40%; text-align: center; }
        .ttd .garis { height: 64px; }
        .ttd .nama { width: 75%; margin: 0 auto; border-top: 1px solid #1e293b; padding-top: 3px; }

        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .page { margin: 0; width: auto; min-height: 0; padding: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
    @php
        $namaToko = $setting?->nama ?: 'Roti Baru Bakery';
        $logo     = $setting ? $setting->logo_url : asset('default-image/default_logo.png');
        $kontak   = collect([
            $setting?->nomor_telepon ? 'Telp. ' . $setting->nomor_telepon : null,
            $setting?->email,
        ])->filter()->implode(' · ');
    @endphp

    <div class="toolbar">
        <span>Pratinjau cetak – pilih <strong>Simpan sebagai PDF</strong> di dialog printer untuk menyimpan file.</span>
        <span style="display:flex;gap:8px;">
            <button onclick="window.print()">Cetak / Simpan PDF</button>
            <a href="#" class="ghost" onclick="window.close(); return false;">Tutup</a>
        </span>
    </div>

    <div class="page">
        <div class="kop">
            <img src="{{ $logo }}" alt="Logo">
            <div class="teks">
                <h1>{{ strtoupper($namaToko) }}</h1>
                @if ($setting?->alamat)<p>{{ $setting->alamat }}</p>@endif
                @if ($kontak)<p>{{ $kontak }}</p>@endif
            </div>
        </div>

        <h2 class="judul">LAPORAN PENERIMAAN BAHAN BAKU</h2>
        <p class="sub-judul">Bahan baku yang sudah diterima dari supplier, berdasarkan tanggal terima.</p>

        <table class="meta">
            <tr><td>{{ $periode['label_periode'] }}</td><td>:</td><td><strong>{{ $periode['label'] }}</strong></td></tr>
            <tr><td>Jenis laporan</td><td>:</td><td>{{ $periode['label_jenis'] }}</td></tr>
            @if ($produsen)
                <tr><td>Supplier</td><td>:</td><td>{{ $produsen->nama_produsen }}</td></tr>
            @endif
            <tr><td>Tanggal cetak</td><td>:</td><td>{{ $tanggalCetak }}</td></tr>
        </table>

        <div class="ringkasan">
            <div><span>Transaksi penerimaan</span><strong>{{ $ringkasan['jumlah_transaksi'] }}</strong></div>
            <div><span>Supplier</span><strong>{{ $ringkasan['jumlah_supplier'] }}</strong></div>
            <div><span>Total diterima</span><strong>{{ $ringkasan['total_diterima'] }}</strong></div>
            <div><span>Total nilai</span><strong>{{ $ringkasan['total_nilai'] }}</strong></div>
        </div>

        @php
            $qty = fn ($n) => number_format($n, 2, ',', '.');
            $rp  = fn ($n) => 'Rp ' . number_format($n, fmod($n, 1) == 0 ? 0 : 2, ',', '.');
            $tgl = fn ($d, $f = 'j M Y') => $d ? $d->copy()->locale('id')->translatedFormat($f) : '-';
            $totalJumlah = $transaksis->flatMap(fn ($t) => $t->items)->groupBy('satuan')
                ->map(fn ($g, $s) => trim($qty($g->sum('jumlah')) . ' ' . $s))->implode(' + ');
        @endphp

        <table class="rpt-table">
            <thead>
                <tr>
                    <th>Bahan baku</th>
                    <th>Kode</th>
                    <th class="text-end">Jumlah diterima</th>
                    <th class="text-end">Harga satuan</th>
                    <th class="text-end">Subtotal</th>
                    <th>Kadaluarsa</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transaksis as $t)
                    <tr class="rpt-group">
                        <td colspan="5">
                            <strong>{{ $t->nomor }}</strong>
                            @if ($periode['jenis'] !== 'harian')
                                <span class="rpt-muted"> · {{ $tgl($t->tanggal) }}</span>
                            @endif
                            <span class="rpt-muted"> · Supplier: {{ $t->produsen }}</span>
                        </td>
                        <td class="text-end"><strong>{{ $rp($t->subtotal) }}</strong></td>
                    </tr>
                    @foreach ($t->items as $item)
                        <tr>
                            <td>{{ $item->nama }}</td>
                            <td>{{ $item->kode }}</td>
                            <td class="text-end">{{ $qty($item->jumlah) }} {{ $item->satuan }}</td>
                            <td class="text-end">{{ $rp($item->harga) }}</td>
                            <td class="text-end">{{ $rp($item->subtotal) }}</td>
                            <td>{{ $tgl($item->kadaluarsa) }}</td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="6" class="rpt-empty">Tidak ada penerimaan bahan baku pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
            @if ($transaksis->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="2"><strong>Total</strong></td>
                        <td class="text-end"><strong>{{ $totalJumlah }}</strong></td>
                        <td></td>
                        <td class="text-end"><strong>{{ $rp($totalNilai) }}</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>

        <div class="ttd">
            <div>
                <div>Dibuat oleh</div>
                <div class="garis"></div>
                <div class="nama">Admin</div>
            </div>
            <div>
                <div>Mengetahui</div>
                <div class="garis"></div>
                <div class="nama">Pemilik</div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('load', () => setTimeout(() => window.print(), 400));
    </script>
</body>
</html>
