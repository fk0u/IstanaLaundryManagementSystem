<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Purchase Requests (PR)</title>
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
        .badge {
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8.5px;
            text-transform: uppercase;
            display: inline-block;
        }
        .badge-success { background-color: #dcfce7; color: #15803d; }
        .badge-warning { background-color: #fef3c7; color: #b45309; }
        .badge-danger { background-color: #fee2e2; color: #b91c1c; }
        .badge-info { background-color: #e0f2fe; color: #0369a1; }
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
                <div class="brand-logo">ISTANA LAUNDRY</div>
                <div class="brand-sub">ERP & Management System</div>
            </td>
            <td width="50%" style="text-align: right; vertical-align: middle;">
                <div class="doc-title">REKAPITULASI PURCHASE REQUESTS</div>
                <div style="font-size: 9px; color: #64748b; margin-top: 3px;">
                    Dicetak Pada: {{ now()->translatedFormat('d F Y H:i') }} WIB
                </div>
            </td>
        </tr>
    </table>

    <!-- Metadata Card -->
    <div class="meta-box">
        <table class="meta-table" width="100%">
            <tr>
                <td width="33%">
                    <div class="meta-label">Cabang Scope</div>
                    <div class="meta-val">{{ $branchName ?? 'Semua Cabang' }}</div>
                </td>
                <td width="33%">
                    <div class="meta-label">Total Pengajuan PR</div>
                    <div class="meta-val">{{ count($purchaseRequests) }} Document</div>
                </td>
                <td width="34%" style="text-align: right;">
                    <div class="meta-label">Disetujui / Order</div>
                    <div class="meta-val" style="color: #16a34a;">
                        {{ $purchaseRequests->whereIn('status', ['approved', 'ordered'])->count() }} Document
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Main Data Table -->
    <table class="data-table" width="100%" border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr style="background-color: #1e293b; color: #ffffff;">
                <th width="5%" style="text-align: center; background-color: #1e293b; color: #ffffff;">No</th>
                <th width="15%" style="background-color: #1e293b; color: #ffffff;">Nomor PR</th>
                <th width="12%" style="background-color: #1e293b; color: #ffffff;">Tanggal</th>
                <th width="18%" style="background-color: #1e293b; color: #ffffff;">Diajukan Oleh</th>
                <th width="35%" style="background-color: #1e293b; color: #ffffff;">Rincian Item Permintaan</th>
                <th width="15%" style="text-align: center; background-color: #1e293b; color: #ffffff;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchaseRequests as $index => $pr)
                <tr style="{{ $index % 2 == 1 ? 'background-color: #f8fafc;' : 'background-color: #ffffff;' }}">
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="font-weight: bold; font-family: monospace;">{{ $pr->pr_number }}</td>
                    <td>{{ $pr->request_date?->format('d/m/Y') }}</td>
                    <td style="font-weight: bold;">{{ $pr->requestedBy?->name ?? '-' }}</td>
                    <td>
                        @foreach($pr->items as $item)
                            <div>• {{ $item->item?->name }} ({{ number_format($item->quantity, 0) }} {{ $item->item?->unit }})</div>
                        @endforeach
                    </td>
                    <td style="text-align: center;">
                        @if ($pr->status === 'approved')
                            <span class="badge badge-success">Disetujui</span>
                        @elseif ($pr->status === 'ordered')
                            <span class="badge badge-info">Di-PO</span>
                        @elseif ($pr->status === 'rejected')
                            <span class="badge badge-danger">Ditolak</span>
                        @else
                            <span class="badge badge-warning">Pending</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #94a3b8; padding: 15px;">Tidak ada data Purchase Request.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer Signatures -->
    <table class="footer-table" width="100%">
        <tr>
            <td width="33%" style="text-align: center;">
                <div style="font-size: 8.5px; color: #64748b; font-weight: bold;">DIAJUKAN OLEH</div>
                <div class="sig-space"></div>
                <div style="font-weight: bold; text-decoration: underline;">Stok / Admin Cabang</div>
                <div style="font-size: 8px; color: #64748b;">Staff Operational</div>
            </td>
            <td width="34%" style="text-align: center;">
                <div style="font-size: 8.5px; color: #64748b; font-weight: bold;">DIPERIKSA OLEH</div>
                <div class="sig-space"></div>
                <div style="font-weight: bold; text-decoration: underline;">Manajer Operasional</div>
                <div style="font-size: 8px; color: #64748b;">Supervisor</div>
            </td>
            <td width="33%" style="text-align: center;">
                <div style="font-size: 8.5px; color: #64748b; font-weight: bold;">DISETUJUI OLEH</div>
                <div class="sig-space"></div>
                <div style="font-weight: bold; text-decoration: underline;">Finance / Owner</div>
                <div style="font-size: 8px; color: #64748b;">Executive Management</div>
            </td>
        </tr>
    </table>

</body>
</html>
