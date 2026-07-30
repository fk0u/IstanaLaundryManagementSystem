<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Aset Tetap — Istana Laundry ERP</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            margin: 0;
            padding: 15px;
            background-color: #ffffff;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            border-bottom: 3px solid #ff6600;
            padding-bottom: 8px;
        }
        .brand-logo {
            background-color: #ff6600;
            color: #ffffff;
            font-weight: 900;
            font-size: 18px;
            padding: 6px 12px;
            border-radius: 6px;
            display: inline-block;
            letter-spacing: 1px;
        }
        .brand-sub {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 3px;
        }
        .doc-title {
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
            text-align: right;
        }
        .meta-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 15px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-label {
            font-size: 8.5px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 700;
        }
        .meta-val {
            font-size: 10.5px;
            color: #0f172a;
            font-weight: 700;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 5px 6px;
            text-align: left;
            border: 1px solid #1e293b;
        }
        .data-table td {
            padding: 4px 6px;
            border: 1px solid #e2e8f0;
            font-size: 9px;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: 700; }
        .row-total {
            background-color: #fff7ed !important;
            font-weight: 800;
            color: #c2410c;
        }
        .footer-table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
        }
        .sig-space { height: 40px; }
    </style>
</head>
<body>

    <!-- Header Banner -->
    <table class="header-table">
        <tr>
            <td style="width: 50%;">
                <div class="brand-logo">🧺 ISTANA LAUNDRY</div>
                <div class="brand-sub">Fixed Assets & Depreciation Management</div>
            </td>
            <td style="width: 50%;" class="text-right">
                <div class="doc-title">REKAPITULASI ASET TETAP</div>
                <div style="font-size: 9.5px; color: #64748b;">Daftar Perolehan & Nilai Buku Aset</div>
            </td>
        </tr>
    </table>

    <!-- Metadata Grid -->
    <div class="meta-box">
        <table class="meta-table">
            <tr>
                <td style="width: 25%;">
                    <div class="meta-label">Cabang Lokasi</div>
                    <div class="meta-val">{{ $branchName }}</div>
                </td>
                <td style="width: 25%;">
                    <div class="meta-label">Total Unit Aset</div>
                    <div class="meta-val">{{ number_format($assets->count()) }} Unit</div>
                </td>
                <td style="width: 25%;">
                    <div class="meta-label">Tanggal Dicetak</div>
                    <div class="meta-val">{{ now()->format('d/m/Y H:i') }} WITA</div>
                </td>
                <td style="width: 25%;">
                    <div class="meta-label">Dicetak Oleh</div>
                    <div class="meta-val">{{ auth()->user()?->name ?? 'System Admin' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Data Table Assets -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;">Kode Aset</th>
                <th style="width: 20%;">Nama Aset</th>
                <th style="width: 12%;">Kategori</th>
                <th style="width: 12%;">Cabang</th>
                <th style="width: 10%;">Tgl Beli</th>
                <th style="width: 13%;" class="text-right">Perolehan (Rp)</th>
                <th style="width: 12%;" class="text-right">Akum. Susut (Rp)</th>
                <th style="width: 13%;" class="text-right">Nilai Buku (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalCost = 0;
                $totalDep = 0;
                $totalBook = 0;
            @endphp
            @forelse($assets as $asset)
                @php
                    $totalCost += $asset->acquisition_cost;
                    $totalDep += $asset->accumulated_depreciation;
                    $totalBook += $asset->book_value;
                @endphp
                <tr>
                    <td class="font-bold">{{ $asset->asset_code }}</td>
                    <td class="font-bold">{{ $asset->name }}</td>
                    <td>{{ $asset->category }}</td>
                    <td>{{ $asset->branch?->name ?? '-' }}</td>
                    <td>{{ $asset->acquisition_date?->format('d/m/Y') ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($asset->acquisition_cost, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($asset->accumulated_depreciation, 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: #047857;">Rp {{ number_format($asset->book_value, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center">Belum ada data aset tetap terdaftar.</td></tr>
            @endforelse
            <tr class="row-total">
                <td colspan="5" class="text-right">TOTAL KESELURUHAN:</td>
                <td class="text-right">Rp {{ number_format($totalCost, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totalDep, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totalBook, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Signature Footer -->
    <table class="footer-table">
        <tr>
            <td style="width: 50%; text-align: center;">
                <div>Dibuat Oleh,</div>
                <div class="sig-space"></div>
                <div class="font-bold">({{ auth()->user()?->name ?? 'Penanggung Jawab Aset' }})</div>
                <div style="font-size: 8.5px; color: #64748b;">Staff / Manager Aset & Inventori</div>
            </td>
            <td style="width: 50%; text-align: center;">
                <div>Disetujui Oleh,</div>
                <div class="sig-space"></div>
                <div class="font-bold">( Owner / Direksi )</div>
                <div style="font-size: 8.5px; color: #64748b;">Istana Laundry Samarinda</div>
            </td>
        </tr>
    </table>

</body>
</html>
