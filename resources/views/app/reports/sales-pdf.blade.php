<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; background: #fff; padding: 0; }
        .wrap { padding: 16mm 18mm; }

        /* ── Header ─────────────────────────────────────────────────── */
        .header { padding: 20px 0 16px; border-bottom: 2px solid #f59e0b; margin-bottom: 20px; }
        .header h1 { font-size: 20px; font-weight: bold; color: #1e293b; margin-bottom: 2px; }
        .header .meta { font-size: 11px; color: #64748b; margin-top: 4px; }
        .header .badge {
            display: inline-block;
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
            border-radius: 4px;
            padding: 2px 8px;
            font-size: 10px;
            font-weight: bold;
            margin-top: 6px;
        }

        /* ── Section title ───────────────────────────────────────────── */
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 20px 0 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #e2e8f0;
        }

        /* ── Tables ──────────────────────────────────────────────────── */
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 7px 10px;
            text-align: left;
            font-weight: bold;
            color: #475569;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        td { border: 1px solid #e2e8f0; padding: 7px 10px; color: #334155; }
        tr:nth-child(even) td { background: #f8fafc; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .text-amber { color: #b45309; }

        /* ── Summary cards (as table) ────────────────────────────────── */
        .summary-table td { font-size: 12px; vertical-align: middle; }
        .summary-label { color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; }
        .summary-value { font-size: 16px; font-weight: bold; color: #1e293b; }

        /* ── Footer ──────────────────────────────────────────────────── */
        .footer { margin-top: 30px; font-size: 10px; color: #94a3b8; text-align: right; border-top: 1px solid #e2e8f0; padding-top: 8px; }
    </style>
</head>
<body>
<div class="wrap">

    {{-- ── Header ──────────────────────────────────────────────── --}}
    <div class="header">
        <h1>Laporan Penjualan</h1>
        <div class="meta">{{ $companyName }} &mdash; {{ $branchName }}</div>
        <div class="badge">Periode: {{ \Carbon\Carbon::parse($dari)->translatedFormat('d F Y') }} &ndash; {{ \Carbon\Carbon::parse($sampai)->translatedFormat('d F Y') }}</div>
    </div>

    {{-- ── Ringkasan ────────────────────────────────────────────── --}}
    <div class="section-title">Ringkasan</div>
    <table class="summary-table">
        <tr>
            <td style="width:33%">
                <div class="summary-label">Total Pendapatan</div>
                <div class="summary-value text-amber">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
            </td>
            <td style="width:33%">
                <div class="summary-label">Jumlah Transaksi</div>
                <div class="summary-value">{{ number_format($totalTransaksi) }}</div>
            </td>
            <td style="width:34%">
                <div class="summary-label">Rata-rata / Transaksi</div>
                <div class="summary-value">Rp {{ number_format($rataRata, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    {{-- ── Penjualan per Hari ───────────────────────────────────── --}}
    <div class="section-title">Penjualan per Hari</div>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th class="text-right">Transaksi</th>
                <th class="text-right">Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($perHari as $row)
            <tr>
                <td>{{ \Carbon\Carbon::parse($row->tanggal)->translatedFormat('d F Y') }}</td>
                <td class="text-right">{{ number_format($row->transaksi) }}</td>
                <td class="text-right font-bold">Rp {{ number_format($row->pendapatan, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align:center; color:#94a3b8; padding: 16px;">Belum ada data.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── Top 10 Produk ────────────────────────────────────────── --}}
    <div class="section-title">Top 10 Produk Terlaris</div>
    <table>
        <thead>
            <tr>
                <th style="width:30px">#</th>
                <th>Produk</th>
                <th class="text-right">Qty Terjual</th>
                <th class="text-right">Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topProduk as $i => $produk)
            <tr>
                <td class="text-right" style="color:#94a3b8; font-weight:bold;">{{ $i + 1 }}</td>
                <td>{{ $produk->product_name }}</td>
                <td class="text-right">{{ number_format($produk->qty) }}</td>
                <td class="text-right font-bold">Rp {{ number_format($produk->pendapatan, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align:center; color:#94a3b8; padding: 16px;">Belum ada data.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} &nbsp;|&nbsp; Postera &copy; {{ now()->year }}
    </div>

</div>
</body>
</html>
