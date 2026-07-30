<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan — Istana Laundry ERP</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            margin: 0;
            padding: 15px;
            background-color: #ffffff;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-bottom: 3px solid #ff6600;
            padding-bottom: 10px;
        }
        .brand-logo {
            background-color: #ff6600;
            color: #ffffff;
            font-weight: 900;
            font-size: 18px;
            padding: 8px 15px;
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
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            text-align: right;
        }
        .meta-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 15px;
            margin-bottom: 20px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-label {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 700;
        }
        .meta-val {
            font-size: 11px;
            color: #0f172a;
            font-weight: 700;
        }
        .section-title {
            font-size: 12px;
            font-weight: 800;
            color: #ffffff;
            background-color: #1e293b;
            padding: 6px 12px;
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
            background-color: #334155;
            color: #ffffff;
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 6px 8px;
            text-align: left;
            border: 1px solid #334155;
        }
        .data-table td {
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
            font-size: 10px;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: 700;
        }
        .row-total {
            background-color: #fff7ed !important;
            font-weight: 800;
            color: #c2410c;
        }
        .row-group {
            background-color: #f1f5f9;
            font-weight: 800;
            color: #334155;
        }
        .kpi-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px;
            margin-bottom: 15px;
        }
        .kpi-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-top: 3px solid #ff6600;
            border-radius: 6px;
            padding: 8px 12px;
            text-align: center;
        }
        .kpi-num {
            font-size: 14px;
            font-weight: 800;
            color: #ff6600;
        }
        .footer-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        .sig-space {
            height: 45px;
        }
    </style>
</head>
<body>

    <!-- Header Banner -->
    <table class="header-table">
        <tr>
            <td style="width: 50%;">
                <div class="brand-logo">🧺 ISTANA LAUNDRY</div>
                <div class="brand-sub">Premium Care & Executive ERP System</div>
            </td>
            <td style="width: 50%;" class="text-right">
                <div class="doc-title">LAPORAN KEUANGAN KONSOLIDASI</div>
                <div style="font-size: 10px; color: #64748b;">Dokumen Resmi Eksekutif Keuangan</div>
            </td>
        </tr>
    </table>

    <!-- Metadata Grid -->
    <div class="meta-box">
        <table class="meta-table">
            <tr>
                <td style="width: 25%;">
                    <div class="meta-label">Cabang Scope</div>
                    <div class="meta-val">{{ $selectedBranch ? $selectedBranch->name : 'Seluruh Cabang (Konsolidasi)' }}</div>
                </td>
                <td style="width: 25%;">
                    <div class="meta-label">Periode Laporan</div>
                    <div class="meta-val">{{ $month ? date('F', mktime(0,0,0,$month,1)) . ' ' . $year : 'Tahun ' . $year }}</div>
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

    <!-- Section 1: Laba Rugi -->
    <div class="section-title">1. LAPORAN LABA RUGI (INCOME STATEMENT)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">Kode Akun</th>
                <th style="width: 45%;">Nama Akun</th>
                <th style="width: 15%;">Kategori</th>
                <th style="width: 25%;" class="text-right">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr class="row-group">
                <td colspan="4">PENDAPATAN OPERASIONAL</td>
            </tr>
            @foreach($incomeStatement['revenues'] as $rev)
                <tr>
                    <td>{{ $rev['code'] }}</td>
                    <td>{{ $rev['name'] }}</td>
                    <td>Pendapatan</td>
                    <td class="text-right">Rp {{ number_format($rev['amount'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="row-total">
                <td colspan="3" class="text-right">TOTAL PENDAPATAN OPERASIONAL:</td>
                <td class="text-right">Rp {{ number_format($incomeStatement['total_revenue'], 2, ',', '.') }}</td>
            </tr>

            <tr class="row-group">
                <td colspan="4">BEBAN OPERASIONAL</td>
            </tr>
            @foreach($incomeStatement['expenses'] as $exp)
                <tr>
                    <td>{{ $exp['code'] }}</td>
                    <td>{{ $exp['name'] }}</td>
                    <td>Beban</td>
                    <td class="text-right">Rp {{ number_format($exp['amount'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="row-total">
                <td colspan="3" class="text-right">TOTAL BEBAN OPERASIONAL:</td>
                <td class="text-right">Rp {{ number_format($incomeStatement['total_expense'], 2, ',', '.') }}</td>
            </tr>
            <tr class="row-total" style="background-color: #ff6600 !important; color: #ffffff !important;">
                <td colspan="3" class="text-right" style="font-size: 11px;">LABA (RUGI) BERSIH OPERASIONAL:</td>
                <td class="text-right" style="font-size: 11px;">Rp {{ number_format($incomeStatement['net_income'], 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Section 2: Neraca -->
    <div class="section-title">2. LAPORAN NERACA (BALANCE SHEET)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">Kode Akun</th>
                <th style="width: 45%;">Nama Akun / Kelompok</th>
                <th style="width: 15%;">Tipe</th>
                <th style="width: 25%;" class="text-right">Saldo (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr class="row-group"><td colspan="4">AKTIVA (ASSETS)</td></tr>
            @foreach($balanceSheet['assets'] as $ast)
                <tr>
                    <td>{{ $ast['code'] }}</td>
                    <td>{{ $ast['name'] }}</td>
                    <td>Aktiva</td>
                    <td class="text-right">Rp {{ number_format($ast['amount'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="row-total">
                <td colspan="3" class="text-right">TOTAL AKTIVA:</td>
                <td class="text-right">Rp {{ number_format($balanceSheet['total_assets'], 2, ',', '.') }}</td>
            </tr>

            <tr class="row-group"><td colspan="4">KEWAJIBAN & MODAL (LIABILITIES & EQUITY)</td></tr>
            @foreach($balanceSheet['liabilities'] as $liab)
                <tr>
                    <td>{{ $liab['code'] }}</td>
                    <td>{{ $liab['name'] }}</td>
                    <td>Kewajiban</td>
                    <td class="text-right">Rp {{ number_format($liab['amount'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
            @foreach($balanceSheet['equities'] as $eq)
                <tr>
                    <td>{{ $eq['code'] }}</td>
                    <td>{{ $eq['name'] }}</td>
                    <td>Ekuitas</td>
                    <td class="text-right">Rp {{ number_format($eq['amount'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="row-total">
                <td colspan="3" class="text-right">TOTAL PASIVA (KEWAJIBAN & MODAL):</td>
                <td class="text-right">Rp {{ number_format($balanceSheet['total_liabilities_equity'], 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Signature Footer -->
    <table class="footer-table">
        <tr>
            <td style="width: 50%; text-align: center;">
                <div>Dibuat Oleh,</div>
                <div class="sig-space"></div>
                <div class="font-bold">({{ auth()->user()?->name ?? 'Bagian Keuangan' }})</div>
                <div style="font-size: 9px; color: #64748b;">Staff / Controller Keuangan</div>
            </td>
            <td style="width: 50%; text-align: center;">
                <div>Disetujui Oleh,</div>
                <div class="sig-space"></div>
                <div class="font-bold">( Owner / Direksi )</div>
                <div style="font-size: 9px; color: #64748b;">Istana Laundry Samarinda</div>
            </td>
        </tr>
    </table>

</body>
</html>
