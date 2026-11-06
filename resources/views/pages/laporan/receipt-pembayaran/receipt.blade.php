<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $receipts->count() === 1 ? 'Bukti Pembayaran ' . $receipts->first()['nomor'] : 'Bukti Pembayaran (' . $receipts->count() . ' receipt)' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #111111;
            --muted: #6b7280;
            --line: #9ca3af;
            --hair: #e5e7eb;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: "Inter", "Helvetica Neue", Arial, "Segoe UI", sans-serif;
            font-size: 13px;
            line-height: 1.45;
            color: var(--ink);
            background: #e5e7eb;
        }

        /* ── Toolbar (tidak ikut tercetak) ── */
        .toolbar {
            position: sticky; top: 0; z-index: 5;
            display: flex; justify-content: center; gap: 8px;
            padding: 12px; background: #111827;
        }
        .toolbar a, .toolbar button {
            font: inherit; font-size: 13px; font-weight: 600;
            padding: 8px 16px; border-radius: 8px; border: 0; cursor: pointer;
            text-decoration: none; display: inline-block;
        }
        .btn-print { background: #16a34a; color: #fff; }
        .btn-back { background: #374151; color: #fff; }

        /* ── Kertas A4 ── */
        .paper {
            width: 210mm; min-height: 297mm;
            margin: 18px auto;
            padding: 15mm 18mm 12mm;
            background: #fff;
            box-shadow: 0 2px 14px rgba(0,0,0,.15);
            display: flex; flex-direction: column;
        }
        .paper + .paper { margin-top: 18px; }

        /* ── Kop surat (data dari Pengaturan Website) ── */
        .kop {
            display: grid; grid-template-columns: 24mm 1fr 24mm;
            align-items: center; gap: 8px;
        }
        .kop-logo { width: 24mm; height: 20mm; display: flex; align-items: center; justify-content: flex-start; }
        .kop-logo img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .kop-text { text-align: center; }
        .kop-nama { font-size: 23px; font-weight: 800; letter-spacing: 1.2px; text-transform: uppercase; line-height: 1.2; }
        .kop-meta { font-size: 11.5px; color: #374151; margin-top: 3px; }
        .rule { margin: 12px 0 0; border-top: 3px solid var(--ink); border-bottom: 1px solid var(--ink); height: 5px; }

        /* ── Judul & nomor ── */
        .judul { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; margin: 22px 0 14px; }
        .judul h1 { margin: 0; font-size: 27px; font-weight: 800; letter-spacing: .8px; line-height: 1.1; }
        .judul .sub { color: #374151; font-size: 12px; margin-top: 3px; }
        .nomor { text-align: right; }
        .nomor .lbl { font-size: 10px; letter-spacing: 2.2px; color: var(--muted); }
        .nomor .box {
            display: inline-block; margin: 3px 0 3px; padding: 5px 12px;
            border: 1.5px solid var(--ink); font-weight: 800; font-size: 15px; letter-spacing: .3px;
        }
        .nomor .tgl { color: #374151; font-size: 12px; }

        /* ── Kotak informasi ── */
        .info { display: grid; grid-template-columns: 1fr 1fr; border: 1px solid var(--line); }
        .info > div { padding: 12px 16px 14px; }
        .info > div + div { border-left: 1px solid var(--line); }
        .info h2 {
            margin: 0 0 8px; padding-bottom: 6px; border-bottom: 1px solid var(--hair);
            font-size: 10px; font-weight: 700; letter-spacing: 2.2px; color: #374151;
        }
        .party { font-size: 18px; font-weight: 800; margin-bottom: 4px; }
        .info .alamat { color: #374151; font-size: 13px; line-height: 1.55; }
        .kv-row { display: grid; grid-template-columns: 104px 14px 1fr; line-height: 1.7; }
        .kv-row .k { color: #4b5563; }
        .kv-row .v { font-weight: 700; }
        .nb { white-space: nowrap; }

        /* ── Tabel ── */
        table.items { width: 100%; border-collapse: collapse; margin-top: 18px; }
        table.items th {
            background: var(--ink); color: #fff; text-align: left;
            font-size: 10.5px; font-weight: 700; letter-spacing: 1.1px; text-transform: uppercase;
            padding: 9px 8px;
        }
        table.items td { padding: 9px 8px; border-bottom: 1px solid var(--line); vertical-align: top; }
        .r { text-align: right; }
        .c { text-align: center; }

        /* ── Terbilang & total ── */
        .ringkas { display: flex; justify-content: space-between; align-items: stretch; gap: 20px; }
        .terbilang { padding-top: 10px; flex: 1; min-width: 0; }
        .terbilang .lbl { font-size: 10px; letter-spacing: 2.2px; color: var(--muted); margin-bottom: 2px; }
        .terbilang .isi { font-style: italic; font-weight: 700; font-size: 13px; }
        .total-box {
            flex-shrink: 0; min-width: 62mm; border: 1.5px solid var(--ink); border-top: 0;
            padding: 12px 14px; display: flex; justify-content: space-between; align-items: center; gap: 18px;
        }
        .total-box .lbl { font-size: 11px; font-weight: 800; letter-spacing: 1.4px; white-space: nowrap; }
        .total-box .nilai { font-size: 25px; font-weight: 800; white-space: nowrap; }

        .catatan { margin-top: 10px; font-size: 11px; font-style: italic; color: var(--muted); }

        /* ── Stempel LUNAS ── */
        .stamp-row { display: flex; justify-content: center; margin: 24px 0 10px; }
        .stamp {
            transform: rotate(-3deg);
            border: 4px double var(--ink); padding: 6px 26px;
            font-size: 21px; font-weight: 800; letter-spacing: 8px; padding-left: 34px;
        }

        /* ── Tanda tangan ── */
        .sign { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 6px; text-align: center; }
        .sign .role-top { color: #4b5563; }
        .sign .space { height: 66px; }
        .sign .line { border-top: 1px solid var(--ink); margin: 0 6px; padding-top: 6px; }
        .sign .name { font-weight: 800; font-size: 14px; min-height: 1.45em; }
        .sign .role { color: var(--muted); font-size: 11.5px; letter-spacing: .4px; }

        /* ── Footer ── */
        .foot {
            margin-top: auto; padding-top: 24px;
            display: flex; justify-content: space-between; gap: 12px;
            color: #374151; font-size: 10.5px;
        }
        .foot .tengah { text-align: center; flex: 1; }

        @page { size: A4 portrait; margin: 0; }
        @media print {
            body { background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .toolbar { display: none !important; }
            .paper {
                margin: 0; box-shadow: none; width: 210mm;
                height: 296mm; min-height: 0; overflow: hidden;
            }
            .paper + .paper { margin-top: 0; break-before: page; page-break-before: always; }
        }
        @media screen and (max-width: 800px) {
            .paper { width: auto; min-height: 0; margin: 0; padding: 18px; }
            .paper + .paper { margin-top: 12px; }
            .judul { flex-direction: column; align-items: flex-start; }
            .nomor { text-align: left; }
            .info { grid-template-columns: 1fr; }
            .info > div + div { border-left: 0; border-top: 1px solid var(--line); }
            .ringkas { flex-direction: column; }
            .terbilang { flex: none; }
            .total-box { border-top: 1.5px solid var(--ink); margin-top: 8px; }
            .foot { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" class="btn-print" onclick="window.print()">
            Cetak / Simpan PDF{{ $receipts->count() > 1 ? ' (' . $receipts->count() . ' receipt)' : '' }}
        </button>
        <a class="btn-back" href="{{ route('laporan.receipt-pembayaran.index') }}">Kembali ke Laporan</a>
    </div>

    @foreach ($receipts as $r)
        @php
            $produsen = $r['produsen'];
            $telpEmail = collect([
                !empty($setting?->nomor_telepon) ? 'Telp. ' . $setting->nomor_telepon : null,
                $setting?->email ?? null,
            ])->filter()->implode(' · ');
        @endphp
        <div class="paper">
            {{-- Kop: seluruhnya dari Pengaturan Website --}}
            <div class="kop">
                <div class="kop-logo">
                    <img src="{{ $setting?->logo_url ?? asset('default-image/default_logo.png') }}" alt="Logo">
                </div>
                <div class="kop-text">
                    <div class="kop-nama">{{ $namaToko }}</div>
                    <div class="kop-meta">
                        @if (!empty($setting?->alamat)) {{ $setting->alamat }}<br> @endif
                        {{ $telpEmail }}
                    </div>
                </div>
                <div></div>
            </div>
            <div class="rule"></div>

            {{-- Judul & nomor --}}
            <div class="judul">
                <div>
                    <h1>BUKTI PEMBAYARAN</h1>
                    <div class="sub">Pembayaran bahan baku kepada produsen</div>
                </div>
                <div class="nomor">
                    <div class="lbl">NOMOR / TANGGAL</div>
                    <div class="box">{{ $r['nomor'] }}</div>
                    <div class="tgl">{{ $r['tanggalBayar'] }}</div>
                </div>
            </div>

            {{-- Pihak & rujukan --}}
            <div class="info">
                <div>
                    <h2>DIBAYARKAN KEPADA</h2>
                    <div class="party">{{ $produsen->nama_produsen ?? '-' }}</div>
                    <div class="alamat">
                        {{ $produsen->alamat ?? '-' }}<br>
                        Telp. {{ $produsen->no_telp ?? '-' }}
                    </div>
                </div>
                <div>
                    <h2>RUJUKAN</h2>
                    <div class="kv-row"><span class="k">Permintaan</span><span>:</span><span class="v">@forelse ($r['permintaanNomors'] as $nomorPermintaan)<span class="nb">{{ $nomorPermintaan }}</span>@if (!$loop->last), @endif @empty - @endforelse</span></div>
                    <div class="kv-row"><span class="k">Penerimaan</span><span>:</span><span class="v">@forelse ($r['penerimaanNomors'] as $nomorPenerimaan)<span class="nb">{{ $nomorPenerimaan }}</span>@if (!$loop->last), @endif @empty - @endforelse</span></div>
                    <div class="kv-row"><span class="k">Tanggal terima</span><span>:</span><span class="v">{{ $r['tanggalTerima'] }}</span></div>
                    <div class="kv-row"><span class="k">Metode bayar</span><span>:</span><span class="v">{{ $r['metode'] }}</span></div>
                    <div class="kv-row"><span class="k">Persetujuan</span><span>:</span><span class="v">{{ $r['persetujuan'] }}</span></div>
                </div>
            </div>

            {{-- Rincian --}}
            <table class="items">
                <thead>
                    <tr>
                        <th class="c" style="width:34px;">No</th>
                        <th>Bahan baku</th>
                        <th>Kode</th>
                        <th class="r">Jumlah diterima</th>
                        <th class="r">Harga satuan</th>
                        <th class="r">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($r['rows'] as $row)
                        <tr>
                            <td class="c">{{ $loop->iteration }}</td>
                            <td>{{ $row['nama'] }}</td>
                            <td>{{ $row['kode'] }}</td>
                            <td class="r">{{ number_format($row['jumlah'], 2, ',', '.') }} {{ $row['satuan'] }}</td>
                            <td class="r">Rp {{ number_format($row['harga'], 0, ',', '.') }}</td>
                            <td class="r">Rp {{ number_format($row['subtotal'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="c" style="color:var(--muted);">Tidak ada rincian barang.</td></tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Terbilang & total --}}
            <div class="ringkas">
                <div class="terbilang">
                    <div class="lbl">TERBILANG</div>
                    <div class="isi">{{ $r['terbilang'] }}</div>
                </div>
                <div class="total-box">
                    <span class="lbl">TOTAL DIBAYAR</span>
                    <span class="nilai">Rp {{ number_format($r['total'], 0, ',', '.') }}</span>
                </div>
            </div>

            @if (!empty($r['catatan']))
                <div class="catatan">{{ $r['catatan'] }}</div>
            @endif

            <div class="stamp-row"><div class="stamp">LUNAS</div></div>

            {{-- Tanda tangan --}}
            <div class="sign">
                <div>
                    <div class="role-top">Menyetujui</div>
                    <div class="space"></div>
                    <div class="line">
                        <div class="name">{{ $r['namaPemilik'] ?: '' }}</div>
                        <div class="role">Pemilik</div>
                    </div>
                </div>
                <div>
                    <div class="role-top">Membayar</div>
                    <div class="space"></div>
                    <div class="line">
                        <div class="name">{{ $r['namaAdmin'] ?: '' }}</div>
                        <div class="role">Admin</div>
                    </div>
                </div>
                <div>
                    <div class="role-top">Menerima</div>
                    <div class="space"></div>
                    <div class="line">
                        <div class="name">{{ $produsen->nama_produsen ?? '' }}</div>
                        <div class="role">Produsen</div>
                    </div>
                </div>
            </div>

            <div class="foot">
                <span>{{ $r['nomor'] }}</span>
                <span class="tengah">Simpan sebagai bukti pembayaran. Dokumen dibuat oleh sistem.</span>
                <span>Halaman 1 dari 1</span>
            </div>
        </div>
    @endforeach

    @if ($autoPrint)
        <script>window.addEventListener('load', () => setTimeout(() => window.print(), 400));</script>
    @endif
</body>
</html>
