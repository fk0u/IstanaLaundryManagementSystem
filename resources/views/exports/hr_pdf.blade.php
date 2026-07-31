<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan SDM &amp; Payroll Karyawan</title>
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
        .badge {
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8.5px;
            text-transform: uppercase;
            display: inline-block;
        }
        .badge-success { background-color: #dcfce7; color: #15803d; }
        .badge-danger { background-color: #fee2e2; color: #b91c1c; }
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
                <div class="brand-sub">ERP &amp; Management System</div>
            </td>
            <td width="50%" style="text-align: right; vertical-align: middle;">
                <div class="doc-title">REKAPITULASI SDM &amp; KARYAWAN</div>
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
                    <div class="meta-label">Total Karyawan Aktif</div>
                    <div class="meta-val">{{ $employees->where('is_active', true)->count() }} Personel</div>
                </td>
                <td width="34%" style="text-align: right;">
                    <div class="meta-label">Total Beban Gaji Pokok</div>
                    <div class="meta-val" style="color: #ea580c;">
                        Rp {{ number_format($employees->sum('base_salary'), 0, ',', '.') }}
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
                <th width="15%" style="background-color: #1e293b; color: #ffffff;">NIK</th>
                <th width="25%" style="background-color: #1e293b; color: #ffffff;">Nama Karyawan</th>
                <th width="18%" style="background-color: #1e293b; color: #ffffff;">Jabatan / Posisi</th>
                <th width="12%" style="background-color: #1e293b; color: #ffffff;">Tanggal Bergabung</th>
                <th width="13%" style="text-align: right; background-color: #1e293b; color: #ffffff;">Gaji Pokok (Rp)</th>
                <th width="12%" style="text-align: center; background-color: #1e293b; color: #ffffff;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $index => $employee)
                <tr style="{{ $index % 2 == 1 ? 'background-color: #f8fafc;' : 'background-color: #ffffff;' }}">
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="font-weight: bold; font-family: monospace;">{{ $employee->nik }}</td>
                    <td style="font-weight: bold;">{{ $employee->name }}</td>
                    <td>{{ $employee->position }}</td>
                    <td>{{ $employee->joined_at?->format('d/m/Y') ?? '-' }}</td>
                    <td style="text-align: right; font-weight: bold;">
                        Rp {{ number_format($employee->base_salary, 0, ',', '.') }}
                    </td>
                    <td style="text-align: center;">
                        @if ($employee->is_active)
                            <span class="badge badge-success">Aktif</span>
                        @else
                            <span class="badge badge-danger">Non-Aktif</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #94a3b8; padding: 15px;">Tidak ada data Karyawan.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="row-total" style="background-color: #fff7ed; font-weight: bold; color: #c2410c;">
                <td colspan="5" style="text-align: right; font-weight: bold; background-color: #fff7ed; color: #c2410c;">TOTAL BEBAN GAJI POKOK:</td>
                <td style="text-align: right; font-weight: bold; background-color: #fff7ed; color: #c2410c;">
                    Rp {{ number_format($employees->sum('base_salary'), 0, ',', '.') }}
                </td>
                <td style="background-color: #fff7ed;"></td>
            </tr>
        </tfoot>
    </table>

    <!-- Footer Signatures -->
    <table class="footer-table" width="100%">
        <tr>
            <td width="50%" style="text-align: center;">
                <div style="font-size: 8.5px; color: #64748b; font-weight: bold;">HUMAN CAPITAL / HRD</div>
                <div class="sig-space"></div>
                <div style="font-weight: bold; text-decoration: underline;">Staf HRD</div>
                <div style="font-size: 8px; color: #64748b;">Istana Laundry ERP</div>
            </td>
            <td width="50%" style="text-align: center;">
                <div style="font-size: 8.5px; color: #64748b; font-weight: bold;">DIRECTOR / OWNER</div>
                <div class="sig-space"></div>
                <div style="font-weight: bold; text-decoration: underline;">Executive Director</div>
                <div style="font-size: 8px; color: #64748b;">Istana Laundry ERP</div>
            </td>
        </tr>
    </table>

</body>
</html>
