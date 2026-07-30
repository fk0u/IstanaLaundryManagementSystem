<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kinerja & Produktivitas — Istana Laundry ERP</title>
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
            font-size: 11px;
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
            margin-top: 30px;
            border-collapse: collapse;
        }
        .sig-space { height: 45px; }
    </style>
</head>
<body>

    <!-- Header Banner -->
    <table class="header-table">
        <tr>
            <td style="width: 50%;">
                <div class="brand-logo">🧺 ISTANA LAUNDRY</div>
                <div class="brand-sub">Premium Care & Performance Analytics</div>
            </td>
            <td style="width: 50%;" class="text-right">
                <div class="doc-title">LAPORAN KINERJA & PRODUKTIVITAS</div>
                <div style="font-size: 10px; color: #64748b;">Evaluasi Transaksi Kasir & Workshop</div>
            </td>
        </tr>
    </table>

    <!-- Metadata Grid -->
    <div class="meta-box">
        <table class="meta-table">
            <tr>
                <td style="width: 25%;">
                    <div class="meta-label">Cabang Scope</div>
                    <div class="meta-val">{{ $branchName }}</div>
                </td>
                <td style="width: 25%;">
                    <div class="meta-label">Periode Tanggal</div>
                    <div class="meta-val">{{ $dateFrom }} s/d {{ $dateTo }}</div>
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

    <!-- Section 1: Leaderboard Kasir -->
    <div class="section-title">1. LEADERBOARD OMSET TRANSAKSI KASIR</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 40%;">Nama Kasir / Staf</th>
                <th style="width: 15%;" class="text-center">Total Nota</th>
                <th style="width: 20%;" class="text-right">Omset Lunas (Rp)</th>
                <th style="width: 20%;" class="text-right">Omset Pending (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cashiers as $index => $c)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="font-bold">{{ $c->name }}</td>
                    <td class="text-center">{{ number_format($c->total_orders) }} Nota</td>
                    <td class="text-right">Rp {{ number_format($c->total_revenue ?? 0, 2, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($c->total_pending_revenue ?? 0, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">Belum ada transaksi recorded pada periode ini.</td></tr>
            @endforelse
            <tr class="row-total">
                <td colspan="3" class="text-right">TOTAL PENDAPATAN PERIODE INI:</td>
                <td class="text-right" colspan="2">Rp {{ number_format($periodRevenue, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Section 2: Rincian Harian -->
    <div class="section-title">2. RINCIAN HARIAN TRANSAKSI PER KASIR</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 35%;">Nama Kasir</th>
                <th style="width: 12%;" class="text-center">Jumlah Nota</th>
                <th style="width: 15%;" class="text-right">Omset Lunas (Rp)</th>
                <th style="width: 13%;" class="text-right">Pending (Rp)</th>
                <th style="width: 10%;" class="text-right">Diskon (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cashierDailyBreakdown as $row)
                <tr>
                    <td>{{ $row->date }}</td>
                    <td>{{ $row->cashier?->name ?? '-' }}</td>
                    <td class="text-center">{{ $row->total_orders }}</td>
                    <td class="text-right">Rp {{ number_format($row->paid_revenue, 2, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($row->pending_revenue, 2, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($row->total_discount, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">Belum ada data rincian harian.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- Section 3: Produktivitas Workshop -->
    <div class="section-title">3. PRODUKTIVITAS STAF WORKSHOP</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 45%;">Nama Staf Workshop</th>
                <th style="width: 15%;" class="text-center">Total Aksi Status</th>
                <th style="width: 15%;" class="text-center">Order Selesai (SIAP)</th>
                <th style="width: 20%;" class="text-center">Order Diambil</th>
            </tr>
        </thead>
        <tbody>
            @forelse($staffProductivity as $st)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="font-bold">{{ $st->staff_name }}</td>
                    <td class="text-center">{{ number_format($st->total_actions) }} Aksi</td>
                    <td class="text-center font-bold" style="color: #16a34a;">{{ number_format($st->completed_orders) }} Nota</td>
                    <td class="text-center">{{ number_format($st->picked_up_orders) }} Nota</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">Belum ada aktivitas produksi recorded.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- Signature Footer -->
    <table class="footer-table">
        <tr>
            <td style="width: 50%; text-align: center;">
                <div>Dibuat Oleh,</div>
                <div class="sig-space"></div>
                <div class="font-bold">({{ auth()->user()?->name ?? 'Supervisor Operational' }})</div>
                <div style="font-size: 9px; color: #64748b;">Staff / Manager Operational</div>
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
