<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PowerBI Executive Fixed Assets &amp; Depreciation Dashboard — Istana Laundry ERP</title>
    <style>
        @page {
            margin: 15px 20px 20px 20px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9.5px;
            color: #0f172a;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .header-ribbon {
            width: 100%;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            border-bottom: 4px solid #ff6600;
            padding: 12px 15px;
            border-radius: 8px 8px 0 0;
            margin-bottom: 12px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .brand-logo {
            font-size: 16px;
            font-weight: 900;
            color: #ff6600;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .brand-sub {
            font-size: 8px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }
        .doc-title {
            font-size: 14px;
            font-weight: 900;
            color: #ffffff;
            text-align: right;
            letter-spacing: 0.5px;
        }
        .doc-sub {
            font-size: 8px;
            color: #cbd5e1;
            text-align: right;
            margin-top: 2px;
        }
        .meta-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #6366f1;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 12px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-label {
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 700;
        }
        .meta-val {
            font-size: 10px;
            color: #0f172a;
            font-weight: 800;
            margin-top: 1px;
        }
        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            margin-bottom: 12px;
        }
        .kpi-card {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-top: 3px solid #ff6600;
            border-radius: 6px;
            padding: 8px;
            text-align: center;
        }
        .kpi-card-emerald { border-top-color: #10b981; }
        .kpi-card-blue { border-top-color: #3b82f6; }
        .kpi-card-purple { border-top-color: #a855f7; }
        .kpi-label {
            font-size: 8px;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
        }
        .kpi-val {
            font-size: 13px;
            font-weight: 900;
            color: #0f172a;
            margin-top: 3px;
            font-family: 'Courier New', Courier, monospace;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .data-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-size: 8.5px;
            font-weight: 800;
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
        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
        .font-bold { font-weight: 800; }
        .row-total {
            background-color: #fff7ed !important;
            font-weight: 900;
            color: #c2410c;
        }
        .footer-table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
        }
        .sig-space { height: 40px; }
        .security-stamp {
            font-size: 7.5px;
            color: #94a3b8;
            text-align: center;
            margin-top: 15px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 6px;
        }
    </style>
</head>
<body>

    <!-- Header Ribbon -->
    <div class="header-ribbon">
        <table class="header-table">
            <tr>
                <td style="width: 55%;">
                    <div class="brand-logo">ISTANA LAUNDRY ERP</div>
                    <div class="brand-sub">PowerBI Fixed Assets &amp; Depreciation Valuation Analytics</div>
                </td>
                <td style="width: 45%;" class="text-right">
                    <div class="doc-title">EXECUTIVE FIXED ASSETS REPORT</div>
                    <div class="doc-sub">Laporan Aset Tetap Mesin &amp; Depresiasi Terkumulasi</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Metadata Card -->
    <div class="meta-card">
        <table class="meta-table">
            <tr>
                <td style="width: 25%;">
                    <div class="meta-label">Scope Cabang</div>
                    <div class="meta-val">{{ $branchName }}</div>
                </td>
                <td style="width: 25%;">
                    <div class="meta-label">Total Unit Aset</div>
                    <div class="meta-val">{{ count($assets) }} Unit Mesin / Aset</div>
                </td>
                <td style="width: 25%;">
                    <div class="meta-label">Tanggal Cetak</div>
                    <div class="meta-val">{{ now()->format('d/m/Y H:i') }} WITA</div>
                </td>
                <td style="width: 25%;">
                    <div class="meta-label">Dicetak Oleh</div>
                    <div class="meta-val">{{ auth()->user()?->name ?? 'Asset Controller' }}</div>
                </td>
            </tr>
        </table>
    </div>

    @php
        $totalAcquisition = $assets->sum('acquisition_cost');
        $totalBookValue = $assets->sum(fn($a) => $a->acquisition_cost - $a->accumulated_depreciation);
        $totalAccumDep = $assets->sum('accumulated_depreciation');
    @endphp

    <!-- Top KPI Cards Row -->
    <table class="kpi-table">
        <tr>
            <td style="width: 25%;">
                <div class="kpi-card">
                    <div class="kpi-label">Nilai Perolehan Aset</div>
                    <div class="kpi-val" style="color: #ff6600;">Rp {{ number_format($totalAcquisition, 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="kpi-card kpi-card-emerald">
                    <div class="kpi-label">Nilai Buku Saat Ini (Book Value)</div>
                    <div class="kpi-val" style="color: #059669;">Rp {{ number_format($totalBookValue, 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="kpi-card kpi-card-purple">
                    <div class="kpi-label">Akumulasi Depresiasi</div>
                    <div class="kpi-val" style="color: #dc2626;">Rp {{ number_format($totalAccumDep, 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="kpi-card kpi-card-blue">
                    <div class="kpi-label">Metode Depresiasi</div>
                    <div class="kpi-val" style="color: #2563eb; font-size: 11px;">STRAIGHT LINE / DECLINING</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 12%;">Kode Aset</th>
                <th style="width: 24%;">Nama Mesin / Aset</th>
                <th style="width: 12%;">Cabang</th>
                <th style="width: 12%;">Tgl Perolehan</th>
                <th style="width: 14%;" class="text-right">Harga Beli (Rp)</th>
                <th style="width: 13%;" class="text-right">Akum. Depresiasi (Rp)</th>
                <th style="width: 13%;" class="text-right">Nilai Buku (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assets as $asset)
                @php $bookVal = $asset->acquisition_cost - $asset->accumulated_depreciation; @endphp
                <tr>
                    <td class="font-mono font-bold text-center">{{ $asset->asset_code }}</td>
                    <td class="font-bold">{{ $asset->name }}</td>
                    <td>{{ $asset->branch?->name ?? 'HQ' }}</td>
                    <td class="text-center">{{ $asset->acquisition_date?->format('d/m/Y') }}</td>
                    <td class="text-right font-mono font-bold">Rp {{ number_format($asset->acquisition_cost, 0, ',', '.') }}</td>
                    <td class="text-right font-mono text-rose-600">Rp {{ number_format($asset->accumulated_depreciation, 0, ',', '.') }}</td>
                    <td class="text-right font-mono font-bold text-emerald-600">Rp {{ number_format($bookVal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-4">Belum ada aset tetap terdaftar.</td>
                </tr>
            @endforelse
            <tr class="row-total">
                <td colspan="4" class="text-right">TOTAL KONSOLIDASI ASET TETAP:</td>
                <td class="text-right font-mono">Rp {{ number_format($totalAcquisition, 0, ',', '.') }}</td>
                <td class="text-right font-mono">Rp {{ number_format($totalAccumDep, 0, ',', '.') }}</td>
                <td class="text-right font-mono">Rp {{ number_format($totalBookValue, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Signature Footer -->
    <table class="footer-table">
        <tr>
            <td style="width: 50%; text-align: center;">
                <div>Disiapkan Oleh,</div>
                <div class="sig-space"></div>
                <div class="font-bold">({{ auth()->user()?->name ?? 'Asset Controller' }})</div>
                <div style="font-size: 8px; color: #64748b;">Staff Aset &amp; Pemeliharaan</div>
            </td>
            <td style="width: 50%; text-align: center;">
                <div>Disetujui Oleh,</div>
                <div class="sig-space"></div>
                <div class="font-bold">( Head of Finance &amp; Assets )</div>
                <div style="font-size: 8px; color: #64748b;">Istana Laundry Samarinda</div>
            </td>
        </tr>
    </table>

    <div class="security-stamp">
        Document Security Hash: {{ md5(now()->timestamp . 'ASSETS-POWERBI') }} | Generated by Istana Laundry Fixed Asset &amp; Depreciation Engine | Executive Confidential
    </div>

</body>
</html>
