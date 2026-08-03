<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PowerBI Executive Performance Analytics — Istana Laundry ERP</title>
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
            border-left: 4px solid #a855f7;
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
        .section-header {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            padding: 5px 10px;
            border-radius: 4px;
            margin-top: 10px;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
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
                    <div class="brand-sub">PowerBI Staff Productivity &amp; Leaderboard Analytics</div>
                </td>
                <td style="width: 45%;" class="text-right">
                    <div class="doc-title">EXECUTIVE PERFORMANCE REPORT</div>
                    <div class="doc-sub">Laporan Analisis Performa Staf Kasir &amp; Workshop</div>
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
                    <div class="meta-label">Rentang Periode</div>
                    <div class="meta-val">{{ $startDate }} s.d {{ $endDate }}</div>
                </td>
                <td style="width: 25%;">
                    <div class="meta-label">Tanggal Cetak</div>
                    <div class="meta-val">{{ now()->format('d/m/Y H:i') }} WITA</div>
                </td>
                <td style="width: 25%;">
                    <div class="meta-label">Dicetak Oleh</div>
                    <div class="meta-val">{{ auth()->user()?->name ?? 'HR Manager' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Top KPI Cards Row -->
    <table class="kpi-table">
        <tr>
            <td style="width: 25%;">
                <div class="kpi-card">
                    <div class="kpi-label">Total Omset Diolah Kasir</div>
                    <div class="kpi-val" style="color: #ff6600;">Rp {{ number_format($summaryStats['total_sales_revenue'] ?? 0, 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="kpi-card kpi-card-emerald">
                    <div class="kpi-label">Total Nota Terlayani</div>
                    <div class="kpi-val" style="color: #059669;">{{ number_format($summaryStats['total_orders_processed'] ?? 0) }} Nota</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="kpi-card kpi-card-purple">
                    <div class="kpi-label">Volume Workshop (Kg)</div>
                    <div class="kpi-val" style="color: #a855f7;">{{ number_format($summaryStats['total_weight_processed'] ?? 0, 1) }} Kg</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="kpi-card kpi-card-blue">
                    <div class="kpi-label">Volume Workshop (Pcs)</div>
                    <div class="kpi-val" style="color: #2563eb;">{{ number_format($summaryStats['total_pieces_processed'] ?? 0) }} Pcs</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Section 1: Leaderboard Kasir -->
    <div class="section-header">1. KINERJA PENJUALAN KASIR &amp; LEADERBOARD</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;" class="text-center">Rank</th>
                <th style="width: 32%;">Nama Kasir</th>
                <th style="width: 15%;" class="text-center">Total Order</th>
                <th style="width: 20%;" class="text-right">Total Omset (Rp)</th>
                <th style="width: 25%;" class="text-right">Rata-rata / Order (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cashierPerformance as $index => $cashier)
                <tr>
                    <td class="text-center font-bold">#{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $cashier['user_name'] }}</td>
                    <td class="text-center font-bold">{{ number_format($cashier['order_count']) }} Nota</td>
                    <td class="text-right font-mono font-bold" style="color: #059669;">Rp {{ number_format($cashier['total_revenue'], 0, ',', '.') }}</td>
                    <td class="text-right font-mono">Rp {{ number_format($cashier['order_count'] > 0 ? $cashier['total_revenue'] / $cashier['order_count'] : 0, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-3">Tidak ada data performa kasir pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Section 2: Produktivitas Workshop -->
    <div class="section-header">2. PRODUKTIVITAS PETUGAS WORKSHOP (PENCUCI / PENGERING / PENERIKA)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;" class="text-center">No</th>
                <th style="width: 32%;">Nama Petugas Workshop</th>
                <th style="width: 20%;" class="text-center">Total Aktivitas Status</th>
                <th style="width: 20%;" class="text-center">Total Volume (Kg)</th>
                <th style="width: 20%;" class="text-center">Total Pcs Satuan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($staffPerformance as $index => $staff)
                <tr>
                    <td class="text-center font-bold">{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $staff['user_name'] }}</td>
                    <td class="text-center font-bold">{{ number_format($staff['actions_count']) }} Selesai</td>
                    <td class="text-center font-mono font-bold" style="color: #a855f7;">{{ number_format($staff['total_weight'], 1) }} Kg</td>
                    <td class="text-center font-mono font-bold" style="color: #2563eb;">{{ number_format($staff['total_pcs']) }} Pcs</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-3">Tidak ada data produktivitas workshop pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Signature Footer -->
    <table class="footer-table">
        <tr>
            <td style="width: 50%; text-align: center;">
                <div>Disiapkan Oleh,</div>
                <div class="sig-space"></div>
                <div class="font-bold">({{ auth()->user()?->name ?? 'HR Manager' }})</div>
                <div style="font-size: 8px; color: #64748b;">Staff HR &amp; Operational Analytics</div>
            </td>
            <td style="width: 50%; text-align: center;">
                <div>Disetujui Oleh,</div>
                <div class="sig-space"></div>
                <div class="font-bold">( Head of Operations )</div>
                <div style="font-size: 8px; color: #64748b;">Istana Laundry Samarinda</div>
            </td>
        </tr>
    </table>

    <div class="security-stamp">
        Document Security Hash: {{ md5(now()->timestamp . 'PERFORMANCE-POWERBI') }} | Generated by Istana Laundry Performance Analytics Engine | Executive Confidential
    </div>

</body>
</html>
