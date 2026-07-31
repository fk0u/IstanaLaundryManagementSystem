<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Aset Tetap ERP</title>
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
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 6px 8px;
            text-align: left;
            border: 1px solid #1e293b;
        }
        .data-table td {
            padding: 5px 8px;
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
    <table class="header-table" width="100%">
        <tr>
            <td width="50%" style="vertical-align: middle;">
                <div class="brand-logo" style="background-color: #ff6600; color: #ffffff; font-weight: 900; font-size: 18px; padding: 6px 12px; border-radius: 6px; display: inline-block;">ISTANA LAUNDRY</div>
                <div class="brand-sub" style="font-size: 9px; color: #64748b; text-transform: uppercase; margin-top: 3px;">Fixed Assets &amp; Depreciation Management</div>
            </td>
            <td width="50%" style="text-align: right; vertical-align: middle;">
                <div class="doc-title" style="font-size: 15px; font-weight: 800; color: #0f172a;">REKAPITULASI ASET TETAP</div>
                <div style="font-size: 9px; color: #64748b; margin-top: 3px;">
                    Dicetak Pada: {{ now()->translatedFormat('d F Y H:i') }} WIB
                </div>
            </td>
        </tr>
    </table>

    <!-- Metadata Grid -->
    <div class="meta-box" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px; margin-bottom: 15px;">
        <table class="meta-table" width="100%">
            <tr>
                <td width="25%">
                    <div class="meta-label" style="font-size: 8.5px; color: #64748b; text-transform: uppercase; font-weight: 700;">Cabang Lokasi</div>
                    <div class="meta-val" style="font-size: 10.5px; color: #0f172a; font-weight: 700;">{{ $branchName }}</div>
                </td>
                <td width="25%">
                    <div class="meta-label" style="font-size: 8.5px; color: #64748b; text-transform: uppercase; font-weight: 700;">Total Unit Aset</div>
                    <div class="meta-val" style="font-size: 10.5px; color: #0f172a; font-weight: 700;">{{ number_format($assets->count()) }} Unit</div>
                </td>
                <td width="25%">
                    <div class="meta-label" style="font-size: 8.5px; color: #64748b; text-transform: uppercase; font-weight: 700;">Tanggal Dicetak</div>
                    <div class="meta-val" style="font-size: 10.5px; color: #0f172a; font-weight: 700;">{{ now()->format('d/m/Y H:i') }} WIB</div>
                </td>
                <td width="25%" style="text-align: right;">
                    <div class="meta-label" style="font-size: 8.5px; color: #64748b; text-transform: uppercase; font-weight: 700;">Dicetak Oleh</div>
                    <div class="meta-val" style="font-size: 10.5px; color: #0f172a; font-weight: 700;">{{ auth()->user()?->name ?? 'System Admin' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Data Table Assets -->
    <table class="data-table" width="100%" border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse;">
        <thead>
            <tr style="background-color: #1e293b; color: #ffffff;">
                <th width="10%" style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 6px 8px; border: 1px solid #1e293b;">Kode Aset</th>
                <th width="22%" style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 6px 8px; border: 1px solid #1e293b;">Nama Aset</th>
                <th width="14%" style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 6px 8px; border: 1px solid #1e293b;">Kategori</th>
                <th width="14%" style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 6px 8px; border: 1px solid #1e293b;">Cabang</th>
                <th width="10%" style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 6px 8px; border: 1px solid #1e293b;">Tgl Beli</th>
                <th width="10%" style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 6px 8px; border: 1px solid #1e293b; text-align: right;">Perolehan (Rp)</th>
                <th width="10%" style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 6px 8px; border: 1px solid #1e293b; text-align: right;">Akum. Susut (Rp)</th>
                <th width="10%" style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 6px 8px; border: 1px solid #1e293b; text-align: right;">Nilai Buku (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalCost = 0;
                $totalDep = 0;
                $totalBook = 0;
            @endphp
            @forelse($assets as $index => $asset)
                @php
                    $totalCost += $asset->acquisition_cost;
                    $totalDep += $asset->accumulated_depreciation;
                    $totalBook += $asset->book_value;
                @endphp
                <tr style="{{ $index % 2 == 1 ? 'background-color: #f8fafc;' : 'background-color: #ffffff;' }}">
                    <td style="font-weight: bold; font-family: monospace;">{{ $asset->asset_code }}</td>
                    <td style="font-weight: bold;">{{ $asset->name }}</td>
                    <td>{{ $asset->category }}</td>
                    <td>{{ $asset->branch?->name ?? '-' }}</td>
                    <td>{{ $asset->acquisition_date?->format('d/m/Y') ?? '-' }}</td>
                    <td style="text-align: right;">Rp {{ number_format($asset->acquisition_cost, 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format($asset->accumulated_depreciation, 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold; color: #047857;">Rp {{ number_format($asset->book_value, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align: center; color: #94a3b8; padding: 15px;">Belum ada data aset tetap terdaftar.</td></tr>
            @endforelse
            <tr class="row-total" style="background-color: #fff7ed; font-weight: bold; color: #c2410c;">
                <td colspan="5" style="text-align: right; font-weight: bold; background-color: #fff7ed; color: #c2410c;">TOTAL KESELURUHAN ASET:</td>
                <td style="text-align: right; font-weight: bold; background-color: #fff7ed; color: #c2410c;">Rp {{ number_format($totalCost, 0, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold; background-color: #fff7ed; color: #c2410c;">Rp {{ number_format($totalDep, 0, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold; background-color: #fff7ed; color: #c2410c;">Rp {{ number_format($totalBook, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Signature Footer -->
    <table class="footer-table" width="100%">
        <tr>
            <td width="50%" style="text-align: center;">
                <div style="font-size: 8.5px; color: #64748b; font-weight: bold;">DIBUAT OLEH (ASET)</div>
                <div class="sig-space" style="height: 40px;"></div>
                <div style="font-weight: bold; text-decoration: underline;">{{ auth()->user()?->name ?? 'Penanggung Jawab Aset' }}</div>
                <div style="font-size: 8px; color: #64748b;">Staff / Manager Aset &amp; Inventori</div>
            </td>
            <td width="50%" style="text-align: center;">
                <div style="font-size: 8.5px; color: #64748b; font-weight: bold;">DISETUJUI OLEH (MANAGEMENT)</div>
                <div class="sig-space" style="height: 40px;"></div>
                <div style="font-weight: bold; text-decoration: underline;">Owner / Direksi</div>
                <div style="font-size: 8px; color: #64748b;">Istana Laundry ERP</div>
            </td>
        </tr>
    </table>

</body>
</html>
