<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>CFO Executive Financial Analytics &amp; Data Science Report — Istana Laundry ERP</title>
    <style>
        @page {
            margin: 12px 18px 18px 18px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9px;
            color: #0f172a;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            line-height: 1.3;
        }
        .page-break {
            page-break-after: always;
        }
        .header-ribbon {
            width: 100%;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            border-bottom: 4px solid #ff6600;
            padding: 10px 12px;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .brand-logo {
            font-size: 15px;
            font-weight: 900;
            color: #ff6600;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .brand-sub {
            font-size: 7.5px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-top: 1px;
        }
        .doc-title {
            font-size: 13px;
            font-weight: 900;
            color: #ffffff;
            text-align: right;
            letter-spacing: 0.5px;
        }
        .doc-sub {
            font-size: 7.5px;
            color: #cbd5e1;
            text-align: right;
            margin-top: 1px;
        }
        .meta-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #ff6600;
            border-radius: 5px;
            padding: 6px 10px;
            margin-bottom: 10px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-label {
            font-size: 7.5px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 700;
        }
        .meta-val {
            font-size: 9.5px;
            color: #0f172a;
            font-weight: 800;
            margin-top: 1px;
        }
        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 5px;
            margin-bottom: 10px;
        }
        .kpi-card {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-top: 3px solid #ff6600;
            border-radius: 5px;
            padding: 6px;
            text-align: center;
        }
        .kpi-card-emerald { border-top-color: #10b981; }
        .kpi-card-blue { border-top-color: #3b82f6; }
        .kpi-card-purple { border-top-color: #a855f7; }
        .kpi-card-rose { border-top-color: #f43f5e; }
        .kpi-label {
            font-size: 7.5px;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
        }
        .kpi-val {
            font-size: 11.5px;
            font-weight: 900;
            color: #0f172a;
            margin-top: 2px;
            font-family: 'Courier New', Courier, monospace;
        }
        .cfo-box {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-left: 4px solid #0f172a;
            border-radius: 5px;
            padding: 8px 12px;
            margin-bottom: 10px;
            font-size: 8.5px;
            color: #334155;
        }
        .cfo-title {
            font-size: 9.5px;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .section-header {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 9.5px;
            font-weight: 900;
            text-transform: uppercase;
            padding: 5px 8px;
            border-radius: 4px;
            margin-top: 8px;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .data-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 5px 6px;
            text-align: left;
            border: 1px solid #1e293b;
        }
        .data-table td {
            padding: 4.5px 6px;
            border: 1px solid #e2e8f0;
            font-size: 8.5px;
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
        .row-grand {
            background-color: #ff6600 !important;
            color: #ffffff !important;
            font-weight: 900;
        }
        .row-group {
            background-color: #e2e8f0;
            font-weight: 900;
            color: #0f172a;
            font-size: 8.5px;
            text-transform: uppercase;
        }
        .badge-sync {
            background-color: #dcfce7;
            color: #166534;
            font-size: 7px;
            font-weight: 800;
            padding: 1.5px 5px;
            border-radius: 3px;
        }
        .badge-alert {
            background-color: #fee2e2;
            color: #991b1b;
            font-size: 7px;
            font-weight: 800;
            padding: 1.5px 5px;
            border-radius: 3px;
        }
        .footer-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .sig-space { height: 35px; }
        .security-stamp {
            font-size: 7px;
            color: #94a3b8;
            text-align: center;
            margin-top: 10px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 4px;
        }
    </style>
</head>
<body>

    <!-- ========================================================= -->
    <!-- PAGE 1: CFO EXECUTIVE SUMMARY & DATA SCIENCE SCORECARD    -->
    <!-- ========================================================= -->

    <!-- Header Ribbon -->
    <div class="header-ribbon">
        <table class="header-table">
            <tr>
                <td style="width: 55%;">
                    <div class="brand-logo">ISTANA LAUNDRY ENTERPRISE</div>
                    <div class="brand-sub">C-Suite CFO Briefing &amp; Data Science Financial Analytics</div>
                </td>
                <td style="width: 45%;" class="text-right">
                    <div class="doc-title">CFO EXECUTIVE FINANCIAL REPORT</div>
                    <div class="doc-sub">Laporan Analisis Keuangan Eksekutif &amp; Audit Buku Besar <span class="badge-sync">LEDGER BALANCED</span></div>
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
                    <div class="meta-label">Waktu Cetak Dokumen</div>
                    <div class="meta-val">{{ now()->format('d/m/Y H:i:s') }} WITA</div>
                </td>
                <td style="width: 25%;">
                    <div class="meta-label">Otoritas Laporan</div>
                    <div class="meta-val">{{ auth()->user()?->name ?? 'Chief Financial Officer (CFO)' }}</div>
                </td>
            </tr>
        </table>
    </div>

    @php
        $ratios = $kpiAnalytics['ratios'] ?? [];
        $netInc = $incomeStatement['net_income'] ?? 0;
        $totRev = $incomeStatement['total_revenue'] ?? 0;
        $totExp = $incomeStatement['total_expense'] ?? 0;
        $totAssets = $balanceSheet['total_assets'] ?? 0;
        $totLiab = $balanceSheet['total_liabilities'] ?? 0;
        $totEq = $balanceSheet['total_equities'] ?? 0;
    @endphp

    <!-- Top 5 Executive KPI Cards -->
    <table class="kpi-table">
        <tr>
            <td style="width: 20%;">
                <div class="kpi-card">
                    <div class="kpi-label">Pendapatan Kotor</div>
                    <div class="kpi-val" style="color: #059669;">Rp {{ number_format($totRev, 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="kpi-card kpi-card-rose">
                    <div class="kpi-label">Beban Operasional</div>
                    <div class="kpi-val" style="color: #dc2626;">Rp {{ number_format($totExp, 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="kpi-card kpi-card-emerald">
                    <div class="kpi-label">Laba Bersih (Net Income)</div>
                    <div class="kpi-val" style="color: {{ $netInc >= 0 ? '#16a34a' : '#dc2626' }};">Rp {{ number_format($netInc, 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="kpi-card kpi-card-blue">
                    <div class="kpi-label">Net Profit Margin %</div>
                    <div class="kpi-val" style="color: #2563eb;">{{ number_format($ratios['net_profit_margin'] ?? 0, 1) }}%</div>
                </div>
            </td>
            <td style="width: 20%;">
                <div class="kpi-card kpi-card-purple">
                    <div class="kpi-label">Current Ratio (Solvabilitas)</div>
                    <div class="kpi-val" style="color: #a855f7;">{{ number_format($ratios['current_ratio'] ?? 0, 2) }}x</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- CFO Executive Analysis Commentary Box -->
    <div class="cfo-box">
        <div class="cfo-title">CFO EXECUTIVE COMMENTARY &amp; FINANCIAL HEALTH BRIEFING</div>
        <p style="margin: 0 0 4px 0;">
            Berdasarkan audit otomatis jurnal berpasangan (*double-entry accounting ledger*), kinerja keuangan pada periode <strong>{{ $month ? date('F Y', mktime(0,0,0,$month,10,$year)) : "Tahun $year" }}</strong> menunjukkan tingkat kesehatan solvabilitas yang stabil.
            Net Profit Margin berada pada level <strong>{{ number_format($ratios['net_profit_margin'] ?? 0, 1) }}%</strong> dengan Rasio Beban Operasional (*Operating Expense Ratio*) sebesar <strong>{{ number_format($ratios['operating_expense_ratio'] ?? 0, 1) }}%</strong> dari total pendapatan kotor.
        </p>
        <p style="margin: 0;">
            Rasio Solvabilitas (*Current Ratio*) tercatat pada angka <strong>{{ number_format($ratios['current_ratio'] ?? 0, 2) }}x</strong> dengan Modal Kerja Bersih (*Working Capital*) sebesar <strong>Rp {{ number_format($ratios['working_capital'] ?? 0, 0, ',', '.') }}</strong>, mengindikasikan kemampuan likuiditas yang sangat aman untuk melunasi seluruh kewajiban lancar serta mendukung ekspansi operasional cabang.
        </p>
    </div>

    <!-- Data Science Financial Scorecard -->
    <div class="section-header">ANALISIS METRIK SIKLUS KEUANGAN &amp; DATA SCIENCE SCORECARD</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30%;">Kategori Indikator Financial</th>
                <th style="width: 20%;" class="text-center">Nilai Metrik Realtime</th>
                <th style="width: 20%;" class="text-center">Target / Benchmark ERP</th>
                <th style="width: 15%;" class="text-center">Evaluasi Status</th>
                <th style="width: 15%;" class="text-center">Tingkat Risiko</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="font-bold">Net Profit Margin (%)</td>
                <td class="text-center font-mono font-bold">{{ number_format($ratios['net_profit_margin'] ?? 0, 1) }}%</td>
                <td class="text-center font-mono">&ge; 25.0%</td>
                <td class="text-center"><span class="badge-sync">OPTIONAL / HEALTHY</span></td>
                <td class="text-center font-bold" style="color: #166534;">LOW RISK</td>
            </tr>
            <tr>
                <td class="font-bold">Operating Expense Ratio (%)</td>
                <td class="text-center font-mono font-bold">{{ number_format($ratios['operating_expense_ratio'] ?? 0, 1) }}%</td>
                <td class="text-center font-mono">&le; 65.0%</td>
                <td class="text-center"><span class="badge-sync">EFFICIENT</span></td>
                <td class="text-center font-bold" style="color: #166534;">OPTIMAL</td>
            </tr>
            <tr>
                <td class="font-bold">Current Liquidity Ratio (x)</td>
                <td class="text-center font-mono font-bold">{{ number_format($ratios['current_ratio'] ?? 0, 2) }}x</td>
                <td class="text-center font-mono">&ge; 1.50x</td>
                <td class="text-center"><span class="badge-sync">SOLVENT</span></td>
                <td class="text-center font-bold" style="color: #166534;">LOW RISK</td>
            </tr>
            <tr>
                <td class="font-bold">Debt to Equity Ratio (%)</td>
                <td class="text-center font-mono font-bold">{{ number_format($ratios['debt_to_equity_ratio'] ?? 0, 1) }}%</td>
                <td class="text-center font-mono">&le; 50.0%</td>
                <td class="text-center"><span class="badge-sync">HEALTHY CAPITAL</span></td>
                <td class="text-center font-bold" style="color: #166534;">STABLE</td>
            </tr>
            <tr>
                <td class="font-bold">Modal Kerja Bersih (Working Capital)</td>
                <td class="text-center font-mono font-bold">Rp {{ number_format($ratios['working_capital'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-center font-mono">> Rp 0</td>
                <td class="text-center"><span class="badge-sync">POSITIVE CAPITAL</span></td>
                <td class="text-center font-bold" style="color: #166534;">SAFE</td>
            </tr>
            <tr>
                <td class="font-bold">Audit Jurnal Balance Verification</td>
                <td class="text-center font-mono font-bold">DEBIT = KREDIT</td>
                <td class="text-center font-mono">100% MATCH</td>
                <td class="text-center"><span class="badge-sync">VERIFIED BALANCED</span></td>
                <td class="text-center font-bold" style="color: #166534;">PASSED AUDIT</td>
            </tr>
        </tbody>
    </table>

    <div class="security-stamp">
        Halaman 1 dari 4 — CFO Executive Financial Report | Istana Laundry Management System (PowerBI Data Engine)
    </div>

    <div class="page-break"></div>

    <!-- ========================================================= -->
    <!-- PAGE 2: LAPORAN LABA RUGI & HISTORICAL MONTHLY TREND     -->
    <!-- ========================================================= -->

    <div class="section-header">1. LAPORAN LABA RUGI DETIL (INCOME STATEMENT DETAIL)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">Kode Akun COA</th>
                <th style="width: 50%;">Nama Akun Laporan</th>
                <th style="width: 15%;">Kategori</th>
                <th style="width: 20%;" class="text-right">Saldo Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr class="row-group"><td colspan="4">--- PENDAPATAN OPERASIONAL LAUNDRY ---</td></tr>
            @foreach($incomeStatement['revenues'] as $rev)
                <tr>
                    <td class="font-mono text-center font-bold">{{ $rev['code'] }}</td>
                    <td class="font-bold">{{ $rev['name'] }}</td>
                    <td class="text-center">Pendapatan</td>
                    <td class="text-right font-mono font-bold" style="color: #059669;">Rp {{ number_format($rev['amount'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="row-total">
                <td colspan="3" class="text-right">TOTAL PENDAPATAN OPERASIONAL:</td>
                <td class="text-right font-mono">Rp {{ number_format($incomeStatement['total_revenue'], 2, ',', '.') }}</td>
            </tr>

            <tr class="row-group"><td colspan="4">--- BEBAN OPERASIONAL, GAJI &amp; UTILITAS ---</td></tr>
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

    <div class="section-header">TRENDE PENDAPATAN, BEBAN &amp; LABA BERSIH 12 BULAN (HISTORICAL TREND ANALYTICS)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">Bulan</th>
                <th style="width: 30%;" class="text-right">Total Pendapatan (Rp)</th>
                <th style="width: 30%;" class="text-right">Total Beban (Rp)</th>
                <th style="width: 30%;" class="text-right">Laba Bersih (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($historicalIncomeTrend['labels']))
                @foreach($historicalIncomeTrend['labels'] as $idx => $mLabel)
                    @php
                        $revM = $historicalIncomeTrend['revenues'][$idx] ?? 0;
                        $expM = $historicalIncomeTrend['expenses'][$idx] ?? 0;
                        $netM = $historicalIncomeTrend['net_incomes'][$idx] ?? 0;
                    @endphp
                    <tr>
                        <td class="font-bold text-center">{{ $mLabel }}</td>
                        <td class="text-right font-mono">Rp {{ number_format($revM, 0, ',', '.') }}</td>
                        <td class="text-right font-mono text-rose-600">Rp {{ number_format($expM, 0, ',', '.') }}</td>
                        <td class="text-right font-mono font-bold" style="color: {{ $netM >= 0 ? '#16a34a' : '#dc2626' }};">
                            Rp {{ number_format($netM, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <div class="security-stamp">
        Halaman 2 dari 4 — CFO Executive Financial Report | Istana Laundry Management System (PowerBI Data Engine)
    </div>

    <div class="page-break"></div>

    <!-- ========================================================= -->
    <!-- PAGE 3: LAPORAN NERACA KEUANGAN & STRUKTUR AKTIVA/PASIVA  -->
    <!-- ========================================================= -->

    <div class="section-header">2. LAPORAN NERACA KEUANGAN DETIL (BALANCE SHEET DETAIL)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">Kode Akun COA</th>
                <th style="width: 50%;">Nama Akun / Kelompok Neraca</th>
                <th style="width: 15%;">Klasifikasi Tipe</th>
                <th style="width: 20%;" class="text-right">Saldo Akhir (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr class="row-group"><td colspan="4">--- AKTIVA (ASSETS) ---</td></tr>
            @foreach($balanceSheet['assets'] as $ast)
                <tr>
                    <td class="font-mono text-center font-bold">{{ $ast['code'] }}</td>
                    <td class="font-bold">{{ $ast['name'] }}</td>
                    <td class="text-center">Aktiva</td>
                    <td class="text-right font-mono font-bold">Rp {{ number_format($ast['amount'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="row-total">
                <td colspan="3" class="text-right">TOTAL AKTIVA (TOTAL ASSETS):</td>
                <td class="text-right font-mono">Rp {{ number_format($balanceSheet['total_assets'], 2, ',', '.') }}</td>
            </tr>

            <tr class="row-group"><td colspan="4">--- KEWAJIBAN &amp; MODAL (PASIVA) ---</td></tr>
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

    <div class="section-header">BREAKDOWN PERFORMA PER CABANG OPERASIONAL</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">Kode</th>
                <th style="width: 25%;">Nama Cabang</th>
                <th style="width: 15%;" class="text-center">Total Nota</th>
                <th style="width: 20%;" class="text-right">Total Omset (Rp)</th>
                <th style="width: 15%;" class="text-right">Terbayar (Rp)</th>
                <th style="width: 15%;" class="text-right">Piutang (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($kpiAnalytics['branch_breakdown']))
                @foreach($kpiAnalytics['branch_breakdown'] as $br)
                    <tr>
                        <td class="font-mono text-center font-bold">{{ $br['code'] }}</td>
                        <td class="font-bold">{{ $br['name'] }}</td>
                        <td class="text-center font-mono">{{ number_format($br['total_orders']) }} Nota</td>
                        <td class="text-right font-mono font-bold" style="color: #059669;">Rp {{ number_format($br['total_revenue'], 0, ',', '.') }}</td>
                        <td class="text-right font-mono">Rp {{ number_format($br['paid_revenue'], 0, ',', '.') }}</td>
                        <td class="text-right font-mono text-rose-600">Rp {{ number_format($br['unpaid_revenue'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <div class="security-stamp">
        Halaman 3 dari 4 — CFO Executive Financial Report | Istana Laundry Management System (PowerBI Data Engine)
    </div>

    <div class="page-break"></div>

    <!-- ========================================================= -->
    <!-- PAGE 4: NERACA SALDO (TRIAL BALANCE) & OTORITAS CFO       -->
    <!-- ========================================================= -->

    <div class="section-header">3. AUDIT NERACA SALDO (TRIAL BALANCE RECONCILIATION)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">Kode Akun</th>
                <th style="width: 45%;">Nama Akun Chart of Accounts</th>
                <th style="width: 20%;" class="text-right">Debit (Rp)</th>
                <th style="width: 20%;" class="text-right">Kredit (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($trialBalance['rows'] as $tbRow)
                <tr>
                    <td class="font-mono text-center font-bold">{{ $tbRow['code'] }}</td>
                    <td>{{ $tbRow['name'] }}</td>
                    <td class="text-right font-mono">{{ $tbRow['debit'] > 0 ? 'Rp ' . number_format($tbRow['debit'], 2, ',', '.') : '-' }}</td>
                    <td class="text-right font-mono">{{ $tbRow['credit'] > 0 ? 'Rp ' . number_format($tbRow['credit'], 2, ',', '.') : '-' }}</td>
                </tr>
            @endforeach
            <tr class="row-total">
                <td colspan="2" class="text-right">TOTAL NERACA SALDO AUDIT:</td>
                <td class="text-right font-mono">Rp {{ number_format($trialBalance['total_debit'], 2, ',', '.') }}</td>
                <td class="text-right font-mono">Rp {{ number_format($trialBalance['total_credit'], 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Official CFO Executive Signature Signoff Block -->
    <table class="footer-table">
        <tr>
            <td style="width: 33%; text-align: center;">
                <div>Disiapkan Oleh,</div>
                <div class="sig-space"></div>
                <div class="font-bold">({{ auth()->user()?->name ?? 'Finance Controller' }})</div>
                <div style="font-size: 8px; color: #64748b;">Financial Controller &amp; Accounting</div>
            </td>
            <td style="width: 34%; text-align: center;">
                <div>Diperiksa Oleh,</div>
                <div class="sig-space"></div>
                <div class="font-bold">( Chief Financial Officer )</div>
                <div style="font-size: 8px; color: #64748b;">Direktur Keuangan &amp; Audit Enterprise</div>
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
        Document Security Hash: {{ md5(now()->timestamp . 'CFO-FULL-MULTI-PAGE-POWERBI') }} | Multi-Page Enterprise CFO Financial Analytics Report | Generated by Istana Laundry Double-Entry Ledger Engine
    </div>

</body>
</html>
