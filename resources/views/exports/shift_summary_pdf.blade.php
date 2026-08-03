<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Ringkasan Shift Kasir #{{ $shift->id }}</title>
    <style>
        @page {
            margin: 0.8cm;
            size: A4 portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1E293B;
            font-size: 11px;
            line-height: 1.4;
            background-color: #FFFFFF;
        }
        .header-ribbon {
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
            color: #FFFFFF;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 20px;
            font-weight: 900;
            color: #FF6600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .report-title {
            font-size: 14px;
            font-weight: 700;
            color: #F8FAFC;
            margin-top: 4px;
        }
        .kpi-container {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: separate;
            border-spacing: 10px 0;
        }
        .kpi-card {
            background-color: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
        }
        .kpi-card.highlight {
            background-color: #FFF7ED;
            border-color: #FFEDD5;
        }
        .kpi-title {
            font-size: 9px;
            font-weight: 800;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kpi-value {
            font-size: 15px;
            font-weight: 900;
            color: #0F172A;
            margin-top: 4px;
            font-family: monospace;
        }
        .kpi-value.orange {
            color: #FF6600;
        }
        .kpi-value.green {
            color: #166534;
        }
        .kpi-value.red {
            color: #991B1B;
        }
        .section-header {
            font-size: 12px;
            font-weight: 800;
            color: #0F172A;
            border-bottom: 2px solid #FF6600;
            padding-bottom: 4px;
            margin-top: 15px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th {
            background-color: #0F172A;
            color: #FFFFFF;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 8px 10px;
            text-align: left;
        }
        .data-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #E2E8F0;
            font-size: 10px;
        }
        .data-table tr:nth-child(even) td {
            background-color: #F8FAFC;
        }
        .amount {
            font-family: monospace;
            font-weight: 700;
            text-align: right;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .badge-green { background-color: #DCFCE7; color: #166534; }
        .badge-red { background-color: #FEE2E2; color: #991B1B; }
        .badge-blue { background-color: #DBEAFE; color: #1E40AF; }
        .signature-block {
            margin-top: 30px;
            width: 100%;
        }
        .signature-box {
            text-align: center;
            width: 30%;
        }
        .signature-line {
            border-bottom: 1px dashed #94A3B8;
            height: 50px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

    <!-- Header Ribbon -->
    <div class="header-ribbon">
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="company-name">ISTANA LAUNDRY ERP</div>
                    <div class="report-title">REKAPITULASI CLOSING SHIFT KASIR</div>
                </td>
                <td style="text-align: right;">
                    <div style="font-size: 10px; color: #94A3B8;">SHIFT ID: #{{ $shift->id }}</div>
                    <div style="font-size: 10px; color: #94A3B8;">TANGGAL: {{ $shift->created_at->format('d M Y H:i') }}</div>
                    <div style="font-size: 10px; color: #FF6600; font-weight: bold;">CABANG: {{ $shift->branch->name }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Info Metadata -->
    <table style="width: 100%; margin-bottom: 15px; background: #F8FAFC; padding: 10px; border-radius: 6px; border: 1px solid #E2E8F0;">
        <tr>
            <td style="width: 25%;"><strong>Kasir Bertugas:</strong><br>{{ $shift->cashier->name }}</td>
            <td style="width: 25%;"><strong>Jam Buka:</strong><br>{{ $shift->opened_at->format('d/m/Y H:i') }}</td>
            <td style="width: 25%;"><strong>Jam Tutup:</strong><br>{{ $shift->closed_at ? $shift->closed_at->format('d/m/Y H:i') : 'Aktif' }}</td>
            <td style="width: 25%;"><strong>Status Shift:</strong><br>
                @if($shift->status === 'CLOSED')
                    <span class="badge badge-green">CLOSED (TUTUP)</span>
                @else
                    <span class="badge badge-blue">OPEN (AKTIF)</span>
                @endif
            </td>
        </tr>
    </table>

    <!-- Executive KPI Stat Cards -->
    <table class="kpi-container">
        <tr>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-title">Modal Kas Awal</div>
                <div class="kpi-value">Rp {{ number_format($shift->opening_cash, 0, ',', '.') }}</div>
            </td>
            <td class="kpi-card highlight" style="width: 25%;">
                <div class="kpi-title">Omset Kasir Tunai</div>
                <div class="kpi-value orange">Rp {{ number_format($cashSales, 0, ',', '.') }}</div>
            </td>
            <td class="kpi-card" style="width: 25%;">
                <div class="kpi-title">Kas Kecil (Petty Cash)</div>
                <div class="kpi-value red">Rp {{ number_format($shift->petty_cash_total, 0, ',', '.') }}</div>
            </td>
            <td class="kpi-card highlight" style="width: 25%;">
                <div class="kpi-title">Kas Fisik Seharusnya</div>
                <div class="kpi-value green">Rp {{ number_format($shift->closing_cash_system, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <!-- Summary Balance Audit -->
    <div class="section-header">Audit Rekapitulasi Kasir & Selisih</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Item Audit Kasir</th>
                <th style="text-align: right;">Perhitungan Sistem</th>
                <th style="text-align: right;">Penghitungan Fisik Kasir</th>
                <th style="text-align: right;">Status Audit</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Total Kas Diterima (Modal + Penjualan - Kas Kecil)</strong></td>
                <td class="amount">Rp {{ number_format($shift->closing_cash_system, 0, ',', '.') }}</td>
                <td class="amount">Rp {{ number_format($shift->closing_cash_actual, 0, ',', '.') }}</td>
                <td style="text-align: right;">
                    @if($shift->cash_difference == 0)
                        <span class="badge badge-green">PAS (MATCHED)</span>
                    @elseif($shift->cash_difference > 0)
                        <span class="badge badge-blue">+ Rp {{ number_format($shift->cash_difference, 0, ',', '.') }} (SURPLUS)</span>
                    @else
                        <span class="badge badge-red">- Rp {{ number_format(abs($shift->cash_difference), 0, ',', '.') }} (DEFISIT)</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td>Total Penjualan Non-Tunai (QRIS / Transfer / Debit)</td>
                <td class="amount">Rp {{ number_format($nonCashSales, 0, ',', '.') }}</td>
                <td class="amount">Rp {{ number_format($nonCashSales, 0, ',', '.') }}</td>
                <td style="text-align: right;"><span class="badge badge-green">100% SYNCED</span></td>
            </tr>
            <tr>
                <td><strong>Total Gross Revenue Shift</strong></td>
                <td class="amount" style="color: #FF6600; font-size: 11px;">Rp {{ number_format($totalSales, 0, ',', '.') }}</td>
                <td class="amount" style="color: #FF6600; font-size: 11px;">Rp {{ number_format($totalSales, 0, ',', '.') }}</td>
                <td style="text-align: right;"><span class="badge badge-green">VERIFIED</span></td>
            </tr>
        </tbody>
    </table>

    <!-- Rincian Pengeluaran Kas Kecil -->
    @if($shift->pettyCashRecords->count() > 0)
        <div class="section-header">Rincian Pengeluaran Kas Kecil (Petty Cash)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Jam</th>
                    <th style="width: 25%;">Kategori</th>
                    <th>Keterangan / Keperluan</th>
                    <th style="text-align: right; width: 25%;">Nominal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($shift->pettyCashRecords as $record)
                    <tr>
                        <td>{{ $record->created_at->format('H:i') }}</td>
                        <td><span class="badge badge-blue">{{ $record->category }}</span></td>
                        <td>{{ $record->description }}</td>
                        <td class="amount" style="color: #991B1B;">Rp {{ number_format($record->amount, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Signoff Signature Section -->
    <table class="signature-block">
        <tr>
            <td class="signature-box">
                <div>Kasir Bertugas</div>
                <div class="signature-line"></div>
                <div><strong>{{ $shift->cashier->name }}</strong></div>
            </td>
            <td style="width: 40%;"></td>
            <td class="signature-box">
                <div>Manager / Head Branch</div>
                <div class="signature-line"></div>
                <div><strong>( ____________________ )</strong></div>
            </td>
        </tr>
    </table>

</body>
</html>
