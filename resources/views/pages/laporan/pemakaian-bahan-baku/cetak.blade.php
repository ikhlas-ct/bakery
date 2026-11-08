<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Pemakaian Bahan Baku — {{ $periode['label'] }}</title>
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
        table.lap tr.trx td { background: #f3f4f6; font-weight: 700; }
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
        $satuanOf = fn ($bahan) => mb_strtolower($bahan?->satuan?->kode_satuan ?: ($bahan?->satuan?->nama ?? ''));
        $tgl = fn ($d) => $d ? $d->format('d/m/Y') : '-';
        $totalText = $totalPerSatuan->isEmpty()
            ? '0,00'
            : $totalPerSatuan->map(fn ($total, $sat) => $qty($total) . ' ' . $sat)->implode(' + ');
        $transaksi = $transaksi ?? null;
        $tanggalCetak = now()->locale('id');
        // Nama Admin Gudang di tanda tangan: admin pencatat (mode 1 transaksi) atau admin yang mencetak.
        $namaAdmin = $transaksi
            ? ($transaksi->admin->user->username ?? null)
            : ($pencetak->role === 'admin' ? $pencetak->username : null);
        $namaToko = $setting?->nama ?: 'Roti Baru Bakery';
        $kontak = collect([
            $setting?->alamat,
            $setting?->nomor_telepon ? 'Telp. ' . $setting->nomor_telepon : null,
            $setting?->email,
        ])->filter()->implode(' | ');
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

        <div class="judul"><h2>LAPORAN PEMAKAIAN BAHAN BAKU</h2></div>

        <table class="info">
            @if ($transaksi)
                <tr><td>No. Transaksi</td><td>:</td><td>{{ $transaksi->nomor_transaksi }}</td></tr>
                <tr><td>Tanggal Pakai</td><td>:</td><td>{{ $tgl($transaksi->tanggal_pakai) }}</td></tr>
                <tr><td>Keterangan</td><td>:</td><td>{{ $transaksi->keterangan ?: '-' }}</td></tr>
                <tr><td>Dicatat oleh</td><td>:</td><td>{{ $transaksi->admin->user->username ?? '-' }}</td></tr>
            @else
                <tr><td>Periode</td><td>:</td><td>{{ $periode['jenis'] === 'rentang' ? $periode['label'] : ucfirst($periode['jenis']) . ' — ' . $periode['label'] }}</td></tr>
                <tr><td>Bahan Baku</td><td>:</td><td>{{ $bahanBakuDipilih ? $bahanBakuDipilih->kode_bahan_baku . ' — ' . $bahanBakuDipilih->nama_bahan_baku : 'Semua bahan baku' }}</td></tr>
            @endif
            <tr><td>Dicetak oleh</td><td>:</td><td>{{ $pencetak->username }}</td></tr>
            <tr><td>Tanggal cetak</td><td>:</td><td>{{ $tanggalCetak->format('d/m/Y') }}</td></tr>
        </table>

        {{-- A. Rincian --}}
        <h3>A. Rincian Pemakaian</h3>
        <table class="lap">
            <thead>
                <tr>
                    <th width="30">No</th>
                    <th>Bahan Baku</th>
                    <th>Batch Penerimaan</th>
                    <th>Tgl Terima</th>
                    <th>Kadaluarsa</th>
                    <th>Jumlah Dipakai</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pemakaians as $pmk)
                    @unless ($transaksi)
                        <tr class="trx">
                            <td colspan="6">
                                {{ $pmk->nomor_transaksi }} | {{ $tgl($pmk->tanggal_pakai) }}
                                @if ($pmk->keterangan) | {{ $pmk->keterangan }} @endif
                            </td>
                        </tr>
                    @endunless
                    @foreach ($pmk->pemakaianBahanBakuDetails->groupBy('bahan_baku_id') as $rows)
                        @php
                            $bahan = $rows->first()->bahanBaku;
                            $sat   = $satuanOf($bahan);
                        @endphp
                        @foreach ($rows as $row)
                            @php
                                $batch = $row->penerimaanBahanBakuDetail;
                                $pen   = $batch?->penerimaanBahanBaku;
                            @endphp
                            <tr>
                                <td class="c">{{ $loop->first ? $loop->parent->iteration : '' }}</td>
                                <td>{{ $loop->first ? ($bahan->nama_bahan_baku ?? '-') : '' }}</td>
                                <td class="nw">{{ $pen->nomor_transaksi ?? '-' }}</td>
                                <td class="c">{{ $tgl($pen?->tanggal_terima) }}</td>
                                <td class="c">{{ $tgl($batch?->tanggal_kadaluarsa) }}</td>
                                <td class="r">{{ $qty($row->jumlah_dipakai) }} {{ $sat }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                @empty
                    <tr><td colspan="6" class="kosong">Tidak ada pemakaian bahan baku pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="catatan">Catatan: satu bahan baku dapat diambil dari beberapa batch penerimaan.</div>

        {{-- B. Rekapitulasi --}}
        <h3>B. Rekapitulasi per Bahan Baku</h3>
        <table class="lap">
            <thead>
                <tr>
                    <th width="30">No</th>
                    <th>Kode</th>
                    <th>Nama Bahan Baku</th>
                    @unless ($transaksi)<th>Jumlah Transaksi</th>@endunless
                    <th>Total Dipakai</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rekap as $i => $r)
                    <tr>
                        <td class="c">{{ $i + 1 }}</td>
                        <td class="nw">{{ $r->bahanBaku->kode_bahan_baku ?? '-' }}</td>
                        <td>{{ $r->bahanBaku->nama_bahan_baku ?? '-' }}</td>
                        @unless ($transaksi)<td class="c">{{ $r->jumlah_transaksi }}</td>@endunless
                        <td class="r">{{ $qty($r->total_dipakai) }} {{ $satuanOf($r->bahanBaku) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $transaksi ? 4 : 5 }}" class="kosong">Tidak ada data.</td></tr>
                @endforelse
                @if ($rekap->isNotEmpty())
                    <tr class="total">
                        <td colspan="{{ $transaksi ? 3 : 4 }}" class="r">Total</td>
                        <td class="r">{{ $totalText }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        {{-- Tanda tangan --}}
        <div class="ttd">
            <div class="box">
                <div>Mengetahui,</div>
                <div>Pemilik</div>
                <div class="ruang"></div>
                <div class="nama">( <span class="garis">&nbsp;</span> )</div>
            </div>
            <div class="box">
                <div>Padang, {{ $tanggalCetak->translatedFormat('d F Y') }}</div>
                <div>Admin</div>
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
