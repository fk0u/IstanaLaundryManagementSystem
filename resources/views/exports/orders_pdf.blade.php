<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PowerBI Executive Sales &amp; Order Transactions — Istana Laundry ERP</title>
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
            border-left: 4px solid #ff6600;
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
        .badge-paid {
            background-color: #dcfce7;
            color: #166534;
            font-size: 8px;
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 4px;
        }
        .badge-unpaid {
            background-color: #fee2e2;
            color: #991b1b;
            font-size: 8px;
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 4px;
        }
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

    @php
        $totalGross = $orders->sum('total');
        $totalPaid = $orders->where('payment_status', 'paid')->sum('total');
        $totalUnpaid = $orders->where('payment_status', '!=', 'paid')->sum('total');
        $avgBasket = count($orders) > 0 ? $totalGross / count($orders) : 0;
    @endphp

    @if(isset($isExcel) && $isExcel)
        <!-- Excel Specific Header & KPI Cards (Perfect Colspan Grid) -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
            <tr>
                <td colspan="7" style="background-color: #0F172A; color: #FF6600; font-size: 16px; font-weight: bold; padding: 12px; text-align: left;">
                    ISTANA LAUNDRY ERP — EXECUTIVE SALES REPORT
                </td>
            </tr>
            <tr>
                <td colspan="7" style="background-color: #1E293B; color: #CBD5E1; font-size: 9px; padding: 6px; text-align: left;">
                    Scope Cabang: {{ $branchName }} | Total Transaksi: {{ count($orders) }} Nota | Tanggal Ekspor: {{ now()->format('d/m/Y H:i') }} WITA | Dicetak Oleh: {{ auth()->user()?->name ?? 'Kasir / Admin' }}
                </td>
            </tr>
            <tr><td colspan="7"></td></tr>
            <tr>
                <td colspan="2" style="background-color: #FFF7ED; color: #EA580C; font-weight: bold; font-size: 9px; text-align: center; border: 1px solid #FED7AA; padding: 6px;">TOTAL OMSET PENJUALAN</td>
                <td colspan="2" style="background-color: #ECFDF5; color: #047857; font-weight: bold; font-size: 9px; text-align: center; border: 1px solid #A7F3D0; padding: 6px;">TOTAL OMSET TERBAYAR (PAID)</td>
                <td colspan="2" style="background-color: #FEF2F2; color: #B91C1C; font-weight: bold; font-size: 9px; text-align: center; border: 1px solid #FECACA; padding: 6px;">TOTAL PIUTANG BELUM BAYAR</td>
                <td style="background-color: #EFF6FF; color: #1D4ED8; font-weight: bold; font-size: 9px; text-align: center; border: 1px solid #BFDBFE; padding: 6px;">RATA-RATA NOTA</td>
            </tr>
            <tr>
                <td colspan="2" style="background-color: #FFF7ED; color: #FF6600; font-weight: bold; font-size: 13px; text-align: center; border: 1px solid #FED7AA; padding: 6px;">Rp {{ number_format($totalGross, 0, ',', '.') }}</td>
                <td colspan="2" style="background-color: #ECFDF5; color: #059669; font-weight: bold; font-size: 13px; text-align: center; border: 1px solid #A7F3D0; padding: 6px;">Rp {{ number_format($totalPaid, 0, ',', '.') }}</td>
                <td colspan="2" style="background-color: #FEF2F2; color: #DC2626; font-weight: bold; font-size: 13px; text-align: center; border: 1px solid #FECACA; padding: 6px;">Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</td>
                <td style="background-color: #EFF6FF; color: #2563EB; font-weight: bold; font-size: 13px; text-align: center; border: 1px solid #BFDBFE; padding: 6px;">Rp {{ number_format($avgBasket, 0, ',', '.') }}</td>
            </tr>
            <tr><td colspan="7"></td></tr>
        </table>
    @else
        <!-- Header Ribbon -->
        <div class="header-ribbon">
            <table class="header-table">
                <tr>
                    <td style="width: 55%;">
                        <div class="brand-logo">ISTANA LAUNDRY ERP</div>
                        <div class="brand-sub">PowerBI Sales &amp; POS Transaction Dashboard</div>
                    </td>
                    <td style="width: 45%;" class="text-right">
                        <div class="doc-title">EXECUTIVE SALES REPORT</div>
                        <div class="doc-sub">Laporan Transaksi Penjualan &amp; Kasir POS</div>
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
                        <div class="meta-label">Total Transaksi</div>
                        <div class="meta-val">{{ count($orders) }} Nota Transaksi</div>
                    </td>
                    <td style="width: 25%;">
                        <div class="meta-label">Tanggal Ekspor</div>
                        <div class="meta-val">{{ now()->format('d/m/Y H:i') }} WITA</div>
                    </td>
                    <td style="width: 25%;">
                        <div class="meta-label">Dicetak Oleh</div>
                        <div class="meta-val">{{ auth()->user()?->name ?? 'Kasir / Admin' }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Top KPI Cards Row -->
        <table class="kpi-table">
            <tr>
                <td style="width: 25%;">
                    <div class="kpi-card">
                        <div class="kpi-label">Total Omset Penjualan</div>
                        <div class="kpi-val" style="color: #ff6600;">Rp {{ number_format($totalGross, 0, ',', '.') }}</div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="kpi-card kpi-card-emerald">
                        <div class="kpi-label">Total Omset Terbayar (Paid)</div>
                        <div class="kpi-val" style="color: #059669;">Rp {{ number_format($totalPaid, 0, ',', '.') }}</div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="kpi-card kpi-card-purple">
                        <div class="kpi-label">Total Piutang Belum Bayar</div>
                        <div class="kpi-val" style="color: #dc2626;">Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="kpi-card kpi-card-blue">
                        <div class="kpi-label">Rata-rata Nota (Basket Size)</div>
                        <div class="kpi-val" style="color: #2563eb;">Rp {{ number_format($avgBasket, 0, ',', '.') }}</div>
                    </div>
                </td>
            </tr>
        </table>
    @endif

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 14%;">No. Nota</th>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 22%;">Pelanggan</th>
                <th style="width: 14%;">Cabang Scope</th>
                <th style="width: 12%;">Status Bayar</th>
                <th style="width: 12%;">Status Cuci</th>
                <th style="width: 14%;" class="text-right">Total Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr>
                    <td class="font-mono font-bold text-center">{{ $order->order_number }}</td>
                    <td class="text-center">{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                    <td class="font-bold">{{ $order->customer?->name ?? 'Pelanggan Umum' }}</td>
                    <td>{{ $order->branch?->name ?? '-' }}</td>
                    <td class="text-center">
                        @if($order->payment_status === 'paid')
                            <span class="badge-paid">LUNAS</span>
                        @else
                            <span class="badge-unpaid">BELUM BAYAR</span>
                        @endif
                    </td>
                    <td class="text-center font-bold" style="text-transform: uppercase; font-size: 8px;">{{ $order->production_status ?? '-' }}</td>
                    <td class="text-right font-mono font-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-4">Belum ada transaksi ditemukan untuk filter ini.</td>
                </tr>
            @endforelse
            <tr class="row-total">
                <td colspan="6" class="text-right">TOTAL OMSET TRANSAKSI:</td>
                <td class="text-right font-mono">Rp {{ number_format($totalGross, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Signature Footer -->
    <table class="footer-table">
        <tr>
            <td style="width: 50%; text-align: center;">
                <div>Disiapkan Oleh,</div>
                <div class="sig-space"></div>
                <div class="font-bold">({{ auth()->user()?->name ?? 'Admin Kasir' }})</div>
                <div style="font-size: 8px; color: #64748b;">Kasir / Head Counter</div>
            </td>
            <td style="width: 50%; text-align: center;">
                <div>Disetujui Oleh,</div>
                <div class="sig-space"></div>
                <div class="font-bold">( Supervisor Operasional )</div>
                <div style="font-size: 8px; color: #64748b;">Istana Laundry Samarinda</div>
            </td>
        </tr>
    </table>

    <div class="security-stamp">
        Document Security Hash: {{ md5(now()->timestamp . 'SALES-POWERBI') }} | Generated by Istana Laundry POS &amp; ERP System | Confidential Executive Report
    </div>

</body>
</html>
