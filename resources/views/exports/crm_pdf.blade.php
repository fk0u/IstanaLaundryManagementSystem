<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Pelanggan ERP</title>
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
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 5px 6px;
            text-align: left;
            border: 1px solid #1e293b;
        }
        .data-table td {
            padding: 4px 6px;
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
        .badge-tier {
            font-weight: 800;
            padding: 1px 5px;
            border-radius: 4px;
            font-size: 8px;
            text-transform: uppercase;
            display: inline-block;
        }
        .tier-bronze { background-color: #e2e8f0; color: #475569; }
        .tier-silver { background-color: #e0f2fe; color: #0369a1; }
        .tier-gold { background-color: #fef3c7; color: #b45309; }
        .tier-platinum { background-color: #dcfce7; color: #15803d; }
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
    <table class="header-table">
        <tr>
            <td style="width: 50%;">
                <div class="brand-logo">🧺 ISTANA LAUNDRY</div>
                <div class="brand-sub">Customer Relationship Management (CRM)</div>
            </td>
            <td style="width: 50%;" class="text-right">
                <div class="doc-title">REKAPITULASI PELANGGAN &amp; LOYALTY</div>
                <div style="font-size: 9.5px; color: #64748b;">Database Member &amp; Stat Transaksi</div>
            </td>
        </tr>
    </table>

    <!-- Metadata Grid -->
    <div class="meta-box">
        <table class="meta-table">
            <tr>
                <td style="width: 25%;">
                    <div class="meta-label">Filter Pencarian</div>
                    <div class="meta-val">{{ $q !== '' ? $q : 'Semua Pelanggan (Global)' }}</div>
                </td>
                <td style="width: 25%;">
                    <div class="meta-label">Total Terdaftar</div>
                    <div class="meta-val">{{ number_format($customers->count()) }} Pelanggan</div>
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

    <!-- Data Table Customers -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">Kode</th>
                <th style="width: 22%;">Nama Pelanggan</th>
                <th style="width: 13%;">No. HP / WA</th>
                <th style="width: 10%;" class="text-center">Tier</th>
                <th style="width: 8%;" class="text-center">Poin</th>
                <th style="width: 10%;" class="text-center">Total Nota</th>
                <th style="width: 14%;" class="text-right">Total Belanja (Rp)</th>
                <th style="width: 13%;">Nota Terakhir</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalTxSum = 0;
                $totalSpentSum = 0;
            @endphp
            @forelse($customers as $c)
                @php
                    $spent = $c->orders_sum_total ?? $c->total_spent ?? 0;
                    $totalTxSum += $c->orders_count;
                    $totalSpentSum += $spent;

                    $tierClass = match($c->loyalty_tier) {
                        'Silver' => 'tier-silver',
                        'Gold' => 'tier-gold',
                        'Platinum' => 'tier-platinum',
                        default => 'tier-bronze',
                    };
                @endphp
                <tr>
                    <td class="font-bold">{{ $c->member_code }}</td>
                    <td class="font-bold">{{ $c->name }}</td>
                    <td>{{ $c->phone }}</td>
                    <td class="text-center"><span class="badge-tier {{ $tierClass }}">{{ $c->loyalty_tier }}</span></td>
                    <td class="text-center font-bold">{{ number_format($c->loyalty_points) }}</td>
                    <td class="text-center font-bold">{{ number_format($c->orders_count) }}</td>
                    <td class="text-right font-bold" style="color: #ea580c;">Rp {{ number_format($spent, 0, ',', '.') }}</td>
                    <td>
                        @if($c->latestOrder)
                            <div>#{{ $c->latestOrder->order_number }}</div>
                            <div style="font-size: 8px; color: #64748b;">{{ $c->latestOrder->created_at->format('d/m/Y') }}</div>
                        @else
                            <span style="color: #94a3b8;">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center">Belum ada pelanggan terdaftar.</td></tr>
            @endforelse
            <tr class="row-total">
                <td colspan="5" class="text-right">TOTAL AKUMULASI SELURUH PELANGGAN:</td>
                <td class="text-center">{{ number_format($totalTxSum) }} Nota</td>
                <td class="text-right" colspan="2">Rp {{ number_format($totalSpentSum, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Signature Footer -->
    <table class="footer-table">
        <tr>
            <td style="width: 50%; text-align: center;">
                <div>Dibuat Oleh,</div>
                <div class="sig-space"></div>
                <div class="font-bold">({{ auth()->user()?->name ?? 'CS &amp; Marketing' }})</div>
                <div style="font-size: 8.5px; color: #64748b;">Staff / Lead Customer Relationship</div>
            </td>
            <td style="width: 50%; text-align: center;">
                <div>Disetujui Oleh,</div>
                <div class="sig-space"></div>
                <div class="font-bold">( Owner / Direksi )</div>
                <div style="font-size: 8.5px; color: #64748b;">Istana Laundry Samarinda</div>
            </td>
        </tr>
    </table>

</body>
</html>
