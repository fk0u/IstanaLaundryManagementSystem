<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Slip Gaji - {{ $item->employee?->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; padding: 2rem; display: flex; flex-direction: column; align-items: center; }
        .payslip { background: white; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 2rem; max-width: 500px; width: 100%; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .header { text-align: center; border-bottom: 2px solid #FF6600; padding-bottom: 1rem; margin-bottom: 1.5rem; }
        .header h1 { font-size: 1.25rem; font-weight: 900; color: #FF6600; }
        .header p { font-size: 0.75rem; color: #64748b; font-weight: 600; }
        .meta { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.75rem; margin-bottom: 1.5rem; }
        .meta dt { color: #94a3b8; font-weight: 700; uppercase; }
        .meta dd { font-weight: 700; text-align: right; color: #1e293b; }
        .details { border-top: 1px border-bottom: 1px solid #e2e8f0; padding: 1rem 0; margin-bottom: 1.5rem; font-size: 0.8125rem; }
        .row { display: flex; justify-content: space-between; padding: 0.375rem 0; }
        .total { border-top: 2px solid #1e293b; padding-top: 0.75rem; font-size: 1rem; font-weight: 900; }
        .footer { text-align: center; font-size: 0.6875rem; color: #94a3b8; margin-top: 1.5rem; }
        .btn-print { background: #FF6600; color: white; border: none; padding: 0.75rem 1.5rem; font-weight: 700; border-radius: 0.75rem; cursor: pointer; margin-top: 1rem; font-size: 0.8125rem; }
        @media print { body { background: white; padding: 0; } .payslip { border: none; box-shadow: none; max-width: 100%; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="payslip">
        <div class="header">
            <h1>ISTANA LAUNDRY</h1>
            <p>SLIP GAJI KARYAWAN — {{ strtoupper(date('F Y', mktime(0, 0, 0, $item->payroll?->month ?? 1, 10))) }}</p>
            <p style="font-size:0.625rem;">Cabang: {{ $item->payroll?->branch?->name ?? 'Utama' }}</p>
        </div>

        <dl class="meta">
            <dt>NIK</dt><dd>{{ $item->employee?->nik }}</dd>
            <dt>NAMA</dt><dd>{{ $item->employee?->name }}</dd>
            <dt>JABATAN</dt><dd>{{ $item->employee?->position }}</dd>
            <dt>PRESENSI</dt><dd>{{ $item->attendance_days }} / {{ $item->work_days }} Hari</dd>
        </dl>

        <div class="details">
            <div style="font-weight:900; font-size:0.7rem; color:#64748b; margin-bottom:0.5rem; text-transform:uppercase;">Penerimaan (Earnings)</div>
            <div class="row">
                <span>Gaji Pokok</span>
                <span>Rp {{ number_format($item->base_salary, 0, ',', '.') }}</span>
            </div>
            <div class="row">
                <span>Tunjangan Operasional</span>
                <span>Rp {{ number_format($item->allowance, 0, ',', '.') }}</span>
            </div>
            @if($item->bonus_kg > 0)
            <div class="row">
                <span>Bonus Kiloan</span>
                <span>Rp {{ number_format($item->bonus_kg, 0, ',', '.') }}</span>
            </div>
            @endif
            @if($item->bonus_pcs > 0)
            <div class="row">
                <span>Bonus Satuan/Pcs</span>
                <span>Rp {{ number_format($item->bonus_pcs, 0, ',', '.') }}</span>
            </div>
            @endif
            @if($item->transport_allowance > 0)
            <div class="row">
                <span>Uang Transport</span>
                <span>Rp {{ number_format($item->transport_allowance, 0, ',', '.') }}</span>
            </div>
            @endif
            @if($item->overtime_pay > 0)
            <div class="row">
                <span>Upah Lembur</span>
                <span>Rp {{ number_format($item->overtime_pay, 0, ',', '.') }}</span>
            </div>
            @endif
            @if($item->attendance_bonus > 0)
            <div class="row">
                <span>Bonus Presensi</span>
                <span>Rp {{ number_format($item->attendance_bonus, 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="row" style="font-weight:700; border-top:1px dashed #e2e8f0; padding-top:0.5rem; margin-top:0.25rem;">
                <span>Total Penerimaan</span>
                <span>Rp {{ number_format($item->total_earnings ?: $item->base_salary + $item->allowance, 0, ',', '.') }}</span>
            </div>

            <div style="font-weight:900; font-size:0.7rem; color:#64748b; margin:1rem 0 0.5rem; text-transform:uppercase;">Potongan (Deductions)</div>
            @if($item->deduction > 0)
            <div class="row" style="color:#dc2626;">
                <span>Potongan / Absen</span>
                <span>-Rp {{ number_format($item->deduction, 0, ',', '.') }}</span>
            </div>
            @endif
            @if($item->tardiness_deduction > 0)
            <div class="row" style="color:#dc2626;">
                <span>Denda Keterlambatan</span>
                <span>-Rp {{ number_format($item->tardiness_deduction, 0, ',', '.') }}</span>
            </div>
            @endif
            @if($item->loan_deduction > 0)
            <div class="row" style="color:#dc2626;">
                <span>Cicilan Kasbon</span>
                <span>-Rp {{ number_format($item->loan_deduction, 0, ',', '.') }}</span>
            </div>
            @endif
            @if($item->damage_deduction > 0)
            <div class="row" style="color:#dc2626;">
                <span>Ganti Rugi Kerusakan</span>
                <span>-Rp {{ number_format($item->damage_deduction, 0, ',', '.') }}</span>
            </div>
            @endif
            @if($item->bpjs_deduction > 0)
            <div class="row" style="color:#dc2626;">
                <span>Potongan BPJS</span>
                <span>-Rp {{ number_format($item->bpjs_deduction, 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="row" style="font-weight:700; border-top:1px dashed #e2e8f0; padding-top:0.5rem; margin-top:0.25rem; color:#dc2626;">
                <span>Total Potongan</span>
                <span>-Rp {{ number_format($item->total_deductions ?: $item->deduction, 0, ',', '.') }}</span>
            </div>

            <div class="row total">
                <span>GAJI BERSIH (TAKE HOME PAY)</span>
                <span>Rp {{ number_format($item->net_salary, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="footer">
            Dokumen ini divalidasi secara elektronik oleh Sistem ERP Istana Laundry.<br>
            Dicetak pada {{ date('d/m/Y H:i') }} WIB.
        </div>
    </div>

    <button class="btn-print no-print" onclick="window.print()">Cetak Slip Gaji</button>
</body>
</html>
