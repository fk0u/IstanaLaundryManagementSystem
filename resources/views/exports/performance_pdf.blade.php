<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kinerja &amp; Produktivitas Operational</title>
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
        .section-title {
            font-size: 11px;
            font-weight: 800;
            color: #ffffff;
            background-color: #1e293b;
            padding: 6px 10px;
            border-radius: 4px;
            margin-top: 15px;
            margin-bottom: 8px;
            text-transform: uppercase;
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
                <div class="brand-sub" style="font-size: 9px; color: #64748b; text-transform: uppercase; margin-top: 3px;">Premium Care &amp; Performance Analytics</div>
            </td>
            <td width="50%" style="text-align: right; vertical-align: middle;">
                <div class="doc-title" style="font-size: 15px; font-weight: 800; color: #0f172a;">LAPORAN KINERJA &amp; PRODUKTIVITAS</div>
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
                    <div class="meta-label" style="font-size: 8.5px; color: #64748b; text-transform: uppercase; font-weight: 700;">Cabang Scope</div>
                    <div class="meta-val" style="font-size: 10.5px; color: #0f172a; font-weight: 700;">{{ $branchName }}</div>
                </td>
                <td width="25%">
                    <div class="meta-label" style="font-size: 8.5px; color: #64748b; text-transform: uppercase; font-weight: 700;">Periode Tanggal</div>
                    <div class="meta-val" style="font-size: 10.5px; color: #0f172a; font-weight: 700;">{{ $dateFrom }} s/d {{ $dateTo }}</div>
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

    <!-- Section 1: Leaderboard Kasir -->
    <div class="section-title" style="font-size: 11px; font-weight: 800; color: #ffffff; background-color: #1e293b; padding: 6px 10px; border-radius: 4px; margin-top: 15px; margin-bottom: 8px;">1. LEADERBOARD OMSET TRANSAKSI KASIR</div>
    <table class="data-table" width="100%" border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse;">
        <thead>
            <tr style="background-color: #1e293b; color: #ffffff;">
                <th width="5%" style="text-align: center; background-color: #1e293b; color: #ffffff;">Peringkat</th>
                <th width="30%" style="background-color: #1e293b; color: #ffffff;">Nama Kasir</th>
                <th width="15%" style="text-align: center; background-color: #1e293b; color: #ffffff;">Total Nota</th>
                <th width="25%" style="text-align: right; background-color: #1e293b; color: #ffffff;">Pendapatan Lunas (Rp)</th>
                <th width="25%" style="text-align: right; background-color: #1e293b; color: #ffffff;">Pendapatan Pending (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totOrders = 0;
                $totPaid = 0;
                $totPending = 0;
            @endphp
            @forelse($cashiers as $idx => $cashier)
                @php
                    $totOrders += $cashier->total_orders;
                    $totPaid += $cashier->total_revenue ?? 0;
                    $totPending += $cashier->total_pending_revenue ?? 0;
                @endphp
                <tr style="{{ $idx % 2 == 1 ? 'background-color: #f8fafc;' : 'background-color: #ffffff;' }}">
                    <td style="text-align: center; font-weight: bold;">#{{ $idx + 1 }}</td>
                    <td style="font-weight: bold;">{{ $cashier->name }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ number_format($cashier->total_orders) }} Nota</td>
                    <td style="text-align: right; font-weight: bold; color: #16a34a;">Rp {{ number_format($cashier->total_revenue ?? 0, 0, ',', '.') }}</td>
                    <td style="text-align: right; color: #d97706;">Rp {{ number_format($cashier->total_pending_revenue ?? 0, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align: center; color: #94a3b8; padding: 15px;">Belum ada data transaksi kasir pada periode ini.</td></tr>
            @endforelse
            <tr class="row-total" style="background-color: #fff7ed; font-weight: bold; color: #c2410c;">
                <td colspan="2" style="text-align: right; font-weight: bold; background-color: #fff7ed; color: #c2410c;">TOTAL AKUMULASI KASIR:</td>
                <td style="text-align: center; font-weight: bold; background-color: #fff7ed; color: #c2410c;">{{ number_format($totOrders) }} Nota</td>
                <td style="text-align: right; font-weight: bold; background-color: #fff7ed; color: #c2410c;">Rp {{ number_format($totPaid, 0, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold; background-color: #fff7ed; color: #c2410c;">Rp {{ number_format($totPending, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Section 2: Produktivitas Staff Workshop -->
    <div class="section-title" style="font-size: 11px; font-weight: 800; color: #ffffff; background-color: #1e293b; padding: 6px 10px; border-radius: 4px; margin-top: 15px; margin-bottom: 8px;">2. PRODUKTIVITAS OPERASIONAL STAFF WORKSHOP</div>
    <table class="data-table" width="100%" border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse;">
        <thead>
            <tr style="background-color: #1e293b; color: #ffffff;">
                <th width="5%" style="text-align: center; background-color: #1e293b; color: #ffffff;">No</th>
                <th width="35%" style="background-color: #1e293b; color: #ffffff;">Nama Staff Operational</th>
                <th width="20%" style="text-align: center; background-color: #1e293b; color: #ffffff;">Total Aksi Update</th>
                <th width="20%" style="text-align: center; background-color: #1e293b; color: #ffffff;">Pesanan Selesai (SIAP)</th>
                <th width="20%" style="text-align: center; background-color: #1e293b; color: #ffffff;">Pesanan Diambil</th>
            </tr>
        </thead>
        <tbody>
            @forelse($staffProductivity as $sIdx => $staff)
                <tr style="{{ $sIdx % 2 == 1 ? 'background-color: #f8fafc;' : 'background-color: #ffffff;' }}">
                    <td style="text-align: center;">{{ $sIdx + 1 }}</td>
                    <td style="font-weight: bold;">{{ $staff->staff_name }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ number_format($staff->total_actions) }} Aksi</td>
                    <td style="text-align: center; font-weight: bold; color: #16a34a;">{{ number_format($staff->completed_orders) }} Nota</td>
                    <td style="text-align: center; color: #0284c7;">{{ number_format($staff->picked_up_orders) }} Nota</td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align: center; color: #94a3b8; padding: 15px;">Belum ada aktivitas workshop pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- Signature Footer -->
    <table class="footer-table" width="100%">
        <tr>
            <td width="50%" style="text-align: center;">
                <div style="font-size: 8.5px; color: #64748b; font-weight: bold;">SUPERVISOR / MANAGER</div>
                <div class="sig-space" style="height: 40px;"></div>
                <div style="font-weight: bold; text-decoration: underline;">{{ auth()->user()?->name ?? 'Manager Operasional' }}</div>
                <div style="font-size: 8px; color: #64748b;">Tim Pengawas Kinerja</div>
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
