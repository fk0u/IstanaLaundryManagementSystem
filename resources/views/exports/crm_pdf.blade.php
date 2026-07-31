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
        .badge-tier {
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8.5px;
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
    <table class="header-table" width="100%">
        <tr>
            <td width="50%" style="vertical-align: middle;">
                <div class="brand-logo" style="background-color: #ff6600; color: #ffffff; font-weight: 900; font-size: 18px; padding: 6px 12px; border-radius: 6px; display: inline-block;">ISTANA LAUNDRY</div>
                <div class="brand-sub" style="font-size: 9px; color: #64748b; text-transform: uppercase; margin-top: 3px;">Customer Relationship Management (CRM)</div>
            </td>
            <td width="50%" style="text-align: right; vertical-align: middle;">
                <div class="doc-title" style="font-size: 15px; font-weight: 800; color: #0f172a;">REKAPITULASI PELANGGAN &amp; LOYALTY</div>
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
                    <div class="meta-label" style="font-size: 8.5px; color: #64748b; text-transform: uppercase; font-weight: 700;">Filter Pencarian</div>
                    <div class="meta-val" style="font-size: 10.5px; color: #0f172a; font-weight: 700;">{{ $q !== '' ? $q : 'Semua Pelanggan (Global)' }}</div>
                </td>
                <td width="25%">
                    <div class="meta-label" style="font-size: 8.5px; color: #64748b; text-transform: uppercase; font-weight: 700;">Total Terdaftar</div>
                    <div class="meta-val" style="font-size: 10.5px; color: #0f172a; font-weight: 700;">{{ number_format($customers->count()) }} Pelanggan</div>
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

    <!-- Data Table Customers -->
    <table class="data-table" width="100%" border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse;">
        <thead>
            <tr style="background-color: #1e293b; color: #ffffff;">
                <th width="12%" style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 6px 8px; border: 1px solid #1e293b;">Kode Member</th>
                <th width="22%" style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 6px 8px; border: 1px solid #1e293b;">Nama Pelanggan</th>
                <th width="14%" style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 6px 8px; border: 1px solid #1e293b;">No. HP / WA</th>
                <th width="10%" style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 6px 8px; border: 1px solid #1e293b; text-align: center;">Tier</th>
                <th width="8%" style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 6px 8px; border: 1px solid #1e293b; text-align: center;">Poin</th>
                <th width="10%" style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 6px 8px; border: 1px solid #1e293b; text-align: center;">Total Nota</th>
                <th width="14%" style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 6px 8px; border: 1px solid #1e293b; text-align: right;">Total Belanja (Rp)</th>
                <th width="10%" style="background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 6px 8px; border: 1px solid #1e293b;">Nota Terakhir</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalTxSum = 0;
                $totalSpentSum = 0;
            @endphp
            @forelse($customers as $index => $c)
                @php
                    $spent = $c->orders_sum_total ?? $c->total_spent ?? 0;
                    $totalTxSum += $c->orders_count;
                    $totalSpentSum += $spent;

                    $tierBg = match($c->loyalty_tier) {
                        'Silver' => '#e0f2fe',
                        'Gold' => '#fef3c7',
                        'Platinum' => '#dcfce7',
                        default => '#e2e8f0',
                    };
                    $tierColor = match($c->loyalty_tier) {
                        'Silver' => '#0369a1',
                        'Gold' => '#b45309',
                        'Platinum' => '#15803d',
                        default => '#475569',
                    };
                @endphp
                <tr style="{{ $index % 2 == 1 ? 'background-color: #f8fafc;' : 'background-color: #ffffff;' }}">
                    <td style="font-weight: bold; font-family: monospace;">{{ $c->member_code }}</td>
                    <td style="font-weight: bold;">{{ $c->name }}</td>
                    <td>{{ $c->phone }}</td>
                    <td style="text-align: center;">
                        <span style="background-color: {{ $tierBg }}; color: {{ $tierColor }}; font-weight: bold; padding: 2px 6px; border-radius: 4px; font-size: 8.5px; text-transform: uppercase;">
                            {{ $c->loyalty_tier }}
                        </span>
                    </td>
                    <td style="text-align: center; font-weight: bold;">{{ number_format($c->loyalty_points) }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ number_format($c->orders_count) }}</td>
                    <td style="text-align: right; font-weight: bold; color: #ea580c;">Rp {{ number_format($spent, 0, ',', '.') }}</td>
                    <td>
                        @if($c->latestOrder)
                            <div style="font-weight: bold;">#{{ $c->latestOrder->order_number }}</div>
                            <div style="font-size: 8px; color: #64748b;">{{ $c->latestOrder->created_at->format('d/m/Y') }}</div>
                        @else
                            <span style="color: #94a3b8;">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align: center; color: #94a3b8; padding: 15px;">Belum ada pelanggan terdaftar.</td></tr>
            @endforelse
            <tr class="row-total" style="background-color: #fff7ed; font-weight: bold; color: #c2410c;">
                <td colspan="5" style="text-align: right; font-weight: bold; background-color: #fff7ed; color: #c2410c;">TOTAL AKUMULASI SELURUH PELANGGAN:</td>
                <td style="text-align: center; font-weight: bold; background-color: #fff7ed; color: #c2410c;">{{ number_format($totalTxSum) }} Nota</td>
                <td style="text-align: right; font-weight: bold; background-color: #fff7ed; color: #c2410c;" colspan="2">Rp {{ number_format($totalSpentSum, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Signature Footer -->
    <table class="footer-table" width="100%">
        <tr>
            <td width="50%" style="text-align: center;">
                <div style="font-size: 8.5px; color: #64748b; font-weight: bold;">DIBUAT OLEH (CRM)</div>
                <div class="sig-space" style="height: 40px;"></div>
                <div style="font-weight: bold; text-decoration: underline;">{{ auth()->user()?->name ?? 'CS &amp; Marketing' }}</div>
                <div style="font-size: 8px; color: #64748b;">Staff / Lead Customer Relationship</div>
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
