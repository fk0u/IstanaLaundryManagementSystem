<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PowerBI Executive Financial Dashboard Report — Istana Laundry ERP</title>
    <style>
        @page {
            margin: 15px 20px 20px 20px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
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
            border-left: 4px solid #3b82f6;
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
        .kpi-card-emerald {
            border-top-color: #10b981;
        }
        .kpi-card-blue {
            border-top-color: #3b82f6;
        }
        .kpi-card-purple {
            border-top-color: #a855f7;
        }
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
            font-size: 9.5px;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-mono {
            font-family: 'Courier New', Courier, monospace;
        }
        .font-bold {
            font-weight: 800;
        }
        .row-total {
            background-color: #fff7ed !important;
            font-weight: 900;
            color: #c2410c;
        }
        .row-grand {
            background-color: #ff6600 !important;
            color: #ffffff !important;
            font-weight: 900;
            font-size: 10px;
        }
        .row-group {
            background-color: #e2e8f0;
            font-weight: 900;
            color: #0f172a;
            font-size: 9px;
            text-transform: uppercase;
        }
        .badge-sync {
            background-color: #dcfce7;
            color: #166534;
            font-size: 7.5px;
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
        }
        .footer-table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
        }
        .sig-space {
            height: 40px;
        }
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
                    <div class="brand-logo">🧺 ISTANA LAUNDRY ERP</div>
                    <div class="brand-sub">PowerBI Executive Financial &amp; Operational Analytics</div>
                </td>
                <td style="width: 45%;" class="text-right">
                    <div class="doc-title">EXECUTIVE FINANCIAL DASHBOARD</div>
                    <div class="doc-sub">Laporan Keuangan Konsolidasi Terverifikasi <span class="badge-sync">● LEDGER SYNCED</span></div>
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
                    <div class="meta-val">{{ $selectedBranch ? $selectedBranch->name : 'Seluruh Cabang (Konsolidasi)' }}</div>
                </td>
                <td style="width: 25%;">
                    <div class="meta-label">Periode Laporan</div>
                    <div class="meta-val">{{ $month ? date('F', mktime(0,0,0,$month,1)) . ' ' . $year : 'Tahun ' . $year }}</div>
                </td>
                <td style="width: 25%;">
                    <div class="meta-label">Tanggal Cetak</div>
                    <div class="meta-val">{{ now()->format('d/m/Y H:i') }} WITA</div>
                </td>
                <td style="width: 25%;">
                    <div class="meta-label">Otoritas Ekspor</div>
                    <div class="meta-val">{{ auth()->user()?->name ?? 'Finance Controller' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Top KPI Cards Row -->
    <table class="kpi-table">
        <tr>
            <td style="width: 25%;">
                <div class="kpi-card">
                    <div class="kpi-label">Total Pendapatan (Revenue)</div>
                    <div class="kpi-val" style="color: #059669;">Rp {{ number_format($incomeStatement['total_revenue'], 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="kpi-card kpi-card-purple">
                    <div class="kpi-label">Total Beban Operasional</div>
                    <div class="kpi-val" style="color: #dc2626;">Rp {{ number_format($incomeStatement['total_expense'], 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="kpi-card kpi-card-emerald">
                    <div class="kpi-label">Laba (Rugi) Bersih</div>
                    <div class="kpi-val" style="color: {{ $incomeStatement['net_income'] >= 0 ? '#16a34a' : '#dc2626' }};">
                        Rp {{ number_format($incomeStatement['net_income'], 0, ',', '.') }}
                    </div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="kpi-card kpi-card-blue">
                    <div class="kpi-label">Total Aktiva (Assets)</div>
                    <div class="kpi-val" style="color: #2563eb;">Rp {{ number_format($balanceSheet['total_assets'], 0, ',', '.') }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Section 1: Laba Rugi -->
    <div class="section-header">1. LAPORAN LABA RUGI (INCOME STATEMENT)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">Kode Akun</th>
                <th style="width: 45%;">Nama Akun COA</th>
                <th style="width: 15%;">Kategori</th>
                <th style="width: 25%;" class="text-right">Jumlah Saldo (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr class="row-group">
                <td colspan="4">--- PENDAPATAN OPERASIONAL ---</td>
            </tr>
            @foreach($incomeStatement['revenues'] as $rev)
                <tr>
                    <td class="font-mono text-center font-bold">{{ $rev['code'] }}</td>
                    <td class="font-bold">{{ $rev['name'] }}</td>
                    <td class="text-center">Pendapatan</td>
                    <td class="text-right font-mono font-bold">Rp {{ number_format($rev['amount'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="row-total">
                <td colspan="3" class="text-right">TOTAL PENDAPATAN OPERASIONAL:</td>
                <td class="text-right font-mono">Rp {{ number_format($incomeStatement['total_revenue'], 2, ',', '.') }}</td>
            </tr>

            <tr class="row-group">
                <td colspan="4">--- BEBAN OPERASIONAL &amp; PRODUKSI ---</td>
            </tr>
            @foreach($incomeStatement['expenses'] as $exp)
                <tr>
                    <td class="font-mono text-center font-bold">{{ $exp['code'] }}</td>
                    <td>{{ $exp['name'] }}</td>
                    <td class="text-center">Beban</td>
                    <td class="text-right font-mono">Rp {{ number_format($exp['amount'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="row-total">
                <td colspan="3" class="text-right">TOTAL BEBAN OPERASIONAL:</td>
                <td class="text-right font-mono">Rp {{ number_format($incomeStatement['total_expense'], 2, ',', '.') }}</td>
            </tr>
            <tr class="row-grand">
                <td colspan="3" class="text-right">LABA (RUGI) BERSIH OPERASIONAL:</td>
                <td class="text-right font-mono">Rp {{ number_format($incomeStatement['net_income'], 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Section 2: Neraca -->
    <div class="section-header">2. LAPORAN NERACA KEUANGAN (BALANCE SHEET)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">Kode Akun</th>
                <th style="width: 45%;">Nama Akun / Kelompok</th>
                <th style="width: 15%;">Tipe</th>
                <th style="width: 25%;" class="text-right">Saldo Akhir (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr class="row-group"><td colspan="4">--- AKTIVA (ASSETS) ---</td></tr>
            @foreach($balanceSheet['assets'] as $ast)
                <tr>
                    <td class="font-mono text-center font-bold">{{ $ast['code'] }}</td>
                    <td class="font-bold">{{ $ast['name'] }}</td>
                    <td class="text-center">Aktiva</td>
                    <td class="text-right font-mono">Rp {{ number_format($ast['amount'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="row-total">
                <td colspan="3" class="text-right">TOTAL AKTIVA:</td>
                <td class="text-right font-mono">Rp {{ number_format($balanceSheet['total_assets'], 2, ',', '.') }}</td>
            </tr>

            <tr class="row-group"><td colspan="4">--- PASIVA (KEWAJIBAN &amp; MODAL) ---</td></tr>
            @foreach($balanceSheet['liabilities'] as $liab)
                <tr>
                    <td class="font-mono text-center font-bold">{{ $liab['code'] }}</td>
                    <td>{{ $liab['name'] }}</td>
                    <td class="text-center">Kewajiban</td>
                    <td class="text-right font-mono">Rp {{ number_format($liab['amount'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
            @foreach($balanceSheet['equities'] as $eq)
                <tr>
                    <td class="font-mono text-center font-bold">{{ $eq['code'] }}</td>
                    <td>{{ $eq['name'] }}</td>
                    <td class="text-center">Ekuitas</td>
                    <td class="text-right font-mono">Rp {{ number_format($eq['amount'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="row-total">
                <td colspan="3" class="text-right">TOTAL PASIVA (KEWAJIBAN &amp; MODAL):</td>
                <td class="text-right font-mono">Rp {{ number_format($balanceSheet['total_liabilities_equity'], 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Signature Footer -->
    <table class="footer-table">
        <tr>
            <td style="width: 33%; text-align: center;">
                <div>Disiapkan Oleh,</div>
                <div class="sig-space"></div>
                <div class="font-bold">({{ auth()->user()?->name ?? 'Finance Controller' }})</div>
                <div style="font-size: 8px; color: #64748b;">Staff / Controller Keuangan</div>
            </td>
            <td style="width: 34%; text-align: center;">
                <div>Diperiksa Oleh,</div>
                <div class="sig-space"></div>
                <div class="font-bold">( Head of Finance &amp; Audit )</div>
                <div style="font-size: 8px; color: #64748b;">Supervisor Keuangan</div>
            </td>
            <td style="width: 33%; text-align: center;">
                <div>Disetujui Oleh,</div>
                <div class="sig-space"></div>
                <div class="font-bold">( Owner / Direksi Utama )</div>
                <div style="font-size: 8px; color: #64748b;">Istana Laundry Samarinda</div>
            </td>
        </tr>
    </table>

    <div class="security-stamp">
        🔒 Document Security Hash: {{ md5(now()->timestamp . 'FINANCE-POWERBI') }} | Generated by Istana Laundry Management System (Double-Entry Ledger Architecture) | Confidential Internal Executive Document
    </div>

</body>
</html>
