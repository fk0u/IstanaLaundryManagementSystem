<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Slip Gaji - {{ $item->employee?->name }} ({{ date('F Y', mktime(0, 0, 0, $item->payroll?->month ?? 1, 10)) }})</title>
    
    <!-- Fonts & Material Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet" />

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: #F8FAFC; 
            color: #1E293B; 
            padding: 2.5rem 1rem; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            -webkit-font-smoothing: antialiased;
        }

        .payslip-container { 
            background: #FFFFFF; 
            border: 1px solid #E2E8F0; 
            border-radius: 1.5rem; 
            padding: 2.5rem; 
            max-width: 820px; 
            width: 100%; 
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01); 
            position: relative;
        }

        /* Top Bar & Header */
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
        }

        .confidential-badge {
            border: 2px solid #FF6600;
            color: #FF6600;
            font-weight: 800;
            font-size: 0.8125rem;
            padding: 0.375rem 1.25rem;
            border-radius: 0.5rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .brand-logo {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            text-align: right;
        }

        .brand-logo img {
            height: 44px;
            width: auto;
            object-fit: contain;
            margin-bottom: 0.25rem;
        }

        .brand-logo h2 {
            font-family: 'Outfit', sans-serif;
            font-weight: 900;
            font-size: 1.125rem;
            color: #0F172A;
            letter-spacing: -0.025em;
        }

        .brand-logo p {
            font-size: 0.6875rem;
            color: #64748B;
            font-weight: 600;
        }

        /* Document Title & Period */
        .doc-title {
            margin-bottom: 1.75rem;
        }

        .doc-title h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 900;
            color: #0F172A;
            margin-bottom: 0.25rem;
        }

        .doc-title p {
            font-size: 0.8125rem;
            font-weight: 700;
            color: #64748B;
        }

        /* Meta Information Grid (4 Columns Layout) */
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem 2.5rem;
            background: #F8FAFC;
            border: 1px solid #F1F5F9;
            padding: 1.25rem 1.5rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
            font-size: 0.8125rem;
        }

        .meta-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .meta-label {
            color: #64748B;
            font-weight: 600;
            width: 140px;
        }

        .meta-value {
            color: #0F172A;
            font-weight: 800;
            text-align: left;
            flex: 1;
        }

        /* Dual Table Breakdown */
        .breakdown-tables {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .table-box {
            border: 1px solid #E2E8F0;
            border-radius: 1rem;
            overflow: hidden;
        }

        .table-box table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8125rem;
        }

        .table-box th {
            padding: 0.75rem 1rem;
            text-align: left;
            font-weight: 800;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #FFFFFF;
        }

        .table-box.earnings th {
            background: #FF6600;
        }

        .table-box.deductions th {
            background: #E11D48;
        }

        .table-box th:last-child {
            text-align: right;
        }

        .table-box td {
            padding: 0.625rem 1rem;
            border-bottom: 1px solid #F1F5F9;
            color: #334155;
            font-weight: 600;
        }

        .table-box td:last-child {
            text-align: right;
            font-weight: 700;
            color: #0F172A;
            font-family: monospace, monospace;
            font-size: 0.875rem;
        }

        .table-box.deductions td:last-child {
            color: #E11D48;
        }

        .table-box tr.total-row td {
            background: #F8FAFC;
            font-weight: 800;
            color: #0F172A;
            border-bottom: none;
            border-top: 2px solid #E2E8F0;
            padding: 0.75rem 1rem;
        }

        /* Summary Take Home Pay Box */
        .thp-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #FFF3E0;
            border: 1.5px solid #FFDBC9;
            padding: 1.25rem 1.75rem;
            border-radius: 1.25rem;
            margin-bottom: 2.5rem;
        }

        .bank-info {
            font-size: 0.8125rem;
            line-height: 1.5;
        }

        .bank-info p {
            color: #64748B;
            font-weight: 600;
        }

        .bank-info strong {
            color: #0F172A;
            font-weight: 800;
        }

        .thp-value {
            text-align: right;
        }

        .thp-value span {
            display: block;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #77574E;
            margin-bottom: 0.25rem;
        }

        .thp-value h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.75rem;
            font-weight: 900;
            color: #FF6600;
            line-height: 1;
        }

        /* Bottom Footer Note */
        .payslip-footer {
            border-top: 1px solid #F1F5F9;
            padding-top: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.6875rem;
            color: #94A3B8;
            font-weight: 600;
        }

        .btn-print {
            background: #FF6600;
            color: white;
            border: none;
            padding: 0.875rem 2rem;
            font-weight: 800;
            font-size: 0.875rem;
            border-radius: 9999px;
            cursor: pointer;
            margin-top: 1.5rem;
            box-shadow: 0 4px 12px rgba(255, 102, 0, 0.25);
            display: inline-flex;
            items-center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .btn-print:hover {
            background: #E55C00;
            transform: translateY(-1px);
        }

        @media print {
            body { background: white; padding: 0; }
            .payslip-container { border: none; box-shadow: none; max-width: 100%; padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="payslip-container">
        
        <!-- Header Top Bar -->
        <div class="top-header">
            <div class="confidential-badge">
                Pribadi & Rahasia
            </div>
            <div class="brand-logo">
                <img alt="Istana Laundry Logo" src="{{ asset('images/logo.webp') }}"/>
                <h2>Istana Laundry Samarinda</h2>
                <p>Enterprise Garment Care & POS Portal</p>
            </div>
        </div>

        <!-- Document Title -->
        <div class="doc-title">
            <h1>Slip Gaji Karyawan</h1>
            <p>Periode : 01 {{ date('M Y', mktime(0, 0, 0, $item->payroll?->month ?? 1, 10)) }} - 28 {{ date('M Y', mktime(0, 0, 0, $item->payroll?->month ?? 1, 10)) }}</p>
        </div>

        <!-- Employee Metadata Grid -->
        <div class="meta-grid">
            <div class="meta-item">
                <span class="meta-label">Nama Lengkap Staf</span>
                <span class="meta-value">: {{ $item->employee?->name ?? 'N/A' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Jabatan (Sebagai Apa)</span>
                <span class="meta-value">: {{ $item->employee?->position ?? 'Staf Operational' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">NIK Staf</span>
                <span class="meta-value">: {{ $item->employee?->nik ?? '-' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Cabang Penempatan</span>
                <span class="meta-value">: {{ $item->employee?->branch?->name ?? $item->payroll?->branch?->name ?? 'Konsolidasi Utama' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Tempat, Tgl Lahir (Usia)</span>
                <span class="meta-value">: {{ $item->employee?->birth_place ?? '' }} {{ $item->employee?->birth_date ? $item->employee?->birth_date->format('d/m/Y') : '' }} {{ $item->employee?->age ? '('.$item->employee?->age.' th)' : '' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">No. Telepon / WA</span>
                <span class="meta-value">: {{ $item->employee?->phone ?? '-' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Total Kehadiran</span>
                <span class="meta-value">: {{ $item->attendance_days }} / {{ $item->work_days }} Hari Kerja</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Alamat Staf</span>
                <span class="meta-value">: {{ $item->employee?->address ?? '-' }}</span>
            </div>
        </div>

        @php
            $totalEarnings = $item->total_earnings ?: ($item->base_salary + $item->allowance + $item->bonus_kg + $item->bonus_pcs + $item->transport_allowance + $item->overtime_pay + $item->attendance_bonus + $item->special_bonus);
            $totalDeductions = $item->total_deductions ?: ($item->deduction + $item->tardiness_deduction + $item->loan_deduction + $item->damage_deduction + $item->bpjs_kesehatan_deduction + $item->bpjs_ketenagakerjaan_deduction + $item->bpjs_deduction);
        @endphp

        <!-- Dual Table Structure (Earnings vs Deductions) -->
        <div class="breakdown-tables">
            
            <!-- 1. Komponen Pendapatan -->
            <div class="table-box earnings">
                <table>
                    <thead>
                        <tr>
                            <th>Komponen Pendapatan</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Gaji Pokok (Upah Utama)</td>
                            <td>{{ number_format($item->base_salary, 0, ',', '.') }}</td>
                        </tr>
                        @if($item->allowance > 0)
                        <tr>
                            <td>Tunjangan Jabatan / Ops</td>
                            <td>{{ number_format($item->allowance, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($item->transport_allowance > 0)
                        <tr>
                            <td>Tunjangan Transportasi</td>
                            <td>{{ number_format($item->transport_allowance, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($item->bonus_kg > 0)
                        <tr>
                            <td>Insentif Hasil Workload (Kg)</td>
                            <td>{{ number_format($item->bonus_kg, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($item->bonus_pcs > 0)
                        <tr>
                            <td>Insentif Hasil Workload (Pcs)</td>
                            <td>{{ number_format($item->bonus_pcs, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($item->overtime_pay > 0)
                        <tr>
                            <td>Upah Lembur Jam Kerja</td>
                            <td>{{ number_format($item->overtime_pay, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($item->attendance_bonus > 0)
                        <tr>
                            <td>Bonus Presensi Penuh</td>
                            <td>{{ number_format($item->attendance_bonus, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($item->special_bonus > 0)
                        <tr>
                            <td>Bonus Khusus / Kinerja</td>
                            <td>{{ number_format($item->special_bonus, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        
                        <!-- Filler rows to equalize height if needed -->
                        @for($i = 0; $i < max(0, 3 - count(array_filter([$item->allowance, $item->transport_allowance, $item->bonus_kg, $item->bonus_pcs, $item->overtime_pay, $item->attendance_bonus, $item->special_bonus]))); $i++)
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        @endfor

                        <tr class="total-row">
                            <td>Total Pendapatan</td>
                            <td>{{ number_format($totalEarnings, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- 2. Komponen Potongan -->
            <div class="table-box deductions">
                <table>
                    <thead>
                        <tr>
                            <th>Komponen Potongan</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($item->bpjs_kesehatan_deduction > 0)
                        <tr>
                            <td>BPJS Kesehatan (1%)</td>
                            <td>{{ number_format($item->bpjs_kesehatan_deduction, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($item->bpjs_ketenagakerjaan_deduction > 0)
                        <tr>
                            <td>BPJS Ketenagakerjaan (2%)</td>
                            <td>{{ number_format($item->bpjs_ketenagakerjaan_deduction, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($item->bpjs_deduction > 0 && !($item->bpjs_kesehatan_deduction > 0))
                        <tr>
                            <td>Iuran BPJS Ketenagakerjaan/Kes</td>
                            <td>{{ number_format($item->bpjs_deduction, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($item->deduction > 0)
                        <tr>
                            <td>Potongan Ketidakhadiran</td>
                            <td>{{ number_format($item->deduction, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($item->tardiness_deduction > 0)
                        <tr>
                            <td>Denda Keterlambatan</td>
                            <td>{{ number_format($item->tardiness_deduction, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($item->loan_deduction > 0)
                        <tr>
                            <td>Cicilan Kasbon Pegawai</td>
                            <td>{{ number_format($item->loan_deduction, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($item->damage_deduction > 0)
                        <tr>
                            <td>Ganti Rugi Kerusakan Goods</td>
                            <td>{{ number_format($item->damage_deduction, 0, ',', '.') }}</td>
                        </tr>
                        @endif

                        <!-- Filler rows for height alignment -->
                        @for($i = 0; $i < max(0, 3 - count(array_filter([$item->bpjs_kesehatan_deduction, $item->bpjs_ketenagakerjaan_deduction, $item->bpjs_deduction, $item->deduction, $item->tardiness_deduction, $item->loan_deduction, $item->damage_deduction]))); $i++)
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        @endfor

                        <tr class="total-row">
                            <td>Total Potongan</td>
                            <td>{{ number_format($totalDeductions, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- Take Home Pay & Bank Details Banner -->
        <div class="thp-container">
            <div class="bank-info">
                <p>Transfer via Bank : <strong>{{ $item->employee?->bank_name ?? 'Bank BCA / Mandiri' }}</strong></p>
                <p>Nomor Rekening : <strong>{{ $item->employee?->bank_account_number ?? '-' }}</strong></p>
                <p>Nama Pemilik Rekening : <strong>{{ strtoupper($item->employee?->bank_account_holder ?? $item->employee?->name ?? 'N/A') }}</strong></p>
            </div>
            <div class="thp-value">
                <span>TAKE HOME PAY (GAJI BERSIH)</span>
                <h2>Rp {{ number_format($item->net_salary, 0, ',', '.') }}</h2>
            </div>
        </div>

        <!-- Payslip Footer Validation -->
        <div class="payslip-footer">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-500 text-lg">verified</span>
                <span>Dokumen ini divalidasi secara digital & sah tanpa tanda tangan basah.</span>
            </div>
            <div>
                Dicetak: {{ date('d/m/Y H:i') }} (UTC+8) • Istana Laundry System
            </div>
        </div>

    </div>

    <!-- Print Button -->
    <button class="btn-print no-print" onclick="window.print()">
        <span class="material-symbols-outlined">print</span>
        Cetak Slip Gaji (PDF)
    </button>
</body>
</html>
