<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PowerBI Executive Report — Istana Laundry ERP</title>

    <!-- Google Fonts & Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet" />

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: #0F172A; 
            color: #F8FAFC; 
            padding: 2rem; 
            -webkit-font-smoothing: antialiased;
        }

        /* PowerBI Dark Canvas Container */
        .powerbi-canvas {
            background: #1E293B;
            border: 1px solid #334155;
            border-radius: 1.5rem;
            padding: 2rem;
            max-width: 1280px;
            margin: 0 auto;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        /* Header Navigation & Branding */
        .pbi-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 1.5rem;
            border-b: 1px solid #334155;
            margin-bottom: 1.75rem;
        }

        .pbi-title-group h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.75rem;
            font-weight: 900;
            color: #FFFFFF;
            letter-spacing: -0.025em;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .pbi-badge {
            background: linear-gradient(135deg, #FF6600 0%, #E55C00 100%);
            color: #FFFFFF;
            font-size: 0.75rem;
            font-weight: 800;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .pbi-meta-sub {
            font-size: 0.8125rem;
            color: #94A3B8;
            margin-top: 0.25rem;
            font-weight: 600;
        }

        .pbi-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn-pbi {
            background: #334155;
            color: #F8FAFC;
            border: 1px solid #475569;
            padding: 0.625rem 1.25rem;
            border-radius: 0.75rem;
            font-weight: 700;
            font-size: 0.8125rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-pbi:hover {
            background: #475569;
            color: #FFFFFF;
        }

        .btn-pbi-primary {
            background: #FF6600;
            border-color: #FF6600;
            color: #FFFFFF;
            box-shadow: 0 4px 14px rgba(255, 102, 0, 0.4);
        }

        .btn-pbi-primary:hover {
            background: #E55C00;
        }

        /* KPI Cards Grid (PowerBI Style Tiles) */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem;
            margin-bottom: 1.75rem;
        }

        .kpi-card {
            background: #0F172A;
            border: 1px solid #334155;
            border-radius: 1.25rem;
            padding: 1.25rem;
            position: relative;
            overflow: hidden;
        }

        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }

        .kpi-card.green::before { background: #10B981; }
        .kpi-card.red::before { background: #F43F5E; }
        .kpi-card.blue::before { background: #0EA5E9; }
        .kpi-card.amber::before { background: #F59E0B; }

        .kpi-label {
            font-size: 0.6875rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94A3B8;
            margin-bottom: 0.5rem;
        }

        .kpi-value {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 900;
            color: #FFFFFF;
            line-height: 1;
        }

        .kpi-sub {
            font-size: 0.75rem;
            color: #64748B;
            margin-top: 0.5rem;
            font-weight: 600;
        }

        /* Dashboard Visual Grid Layout */
        .visual-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.75rem;
        }

        .chart-box {
            background: #0F172A;
            border: 1px solid #334155;
            border-radius: 1.25rem;
            padding: 1.5rem;
        }

        .chart-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
            font-weight: 800;
            color: #F8FAFC;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Data Tables Inside PowerBI Dashboard */
        .pbi-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8125rem;
        }

        .pbi-table th {
            text-align: left;
            padding: 0.625rem 0.875rem;
            background: #1E293B;
            color: #94A3B8;
            font-weight: 800;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #334155;
        }

        .pbi-table td {
            padding: 0.625rem 0.875rem;
            border-bottom: 1px solid #1E293B;
            color: #E2E8F0;
            font-weight: 600;
        }

        .pbi-table td.num {
            text-align: right;
            font-family: monospace;
            font-weight: 700;
        }

        .pbi-table tr:hover td {
            background: #1E293B;
        }

        /* Print Override for Clean PDF Export */
        @media print {
            body {
                background: #FFFFFF !important;
                color: #0F172A !important;
                padding: 0 !important;
            }
            .powerbi-canvas {
                background: #FFFFFF !important;
                border: none !important;
                box-shadow: none !important;
                max-width: 100% !important;
                padding: 0 !important;
            }
            .pbi-header { border-bottom-color: #E2E8F0 !important; }
            .pbi-title-group h1 { color: #0F172A !important; }
            .pbi-meta-sub { color: #64748B !important; }
            .no-print { display: none !important; }
            .kpi-card {
                background: #F8FAFC !important;
                border-color: #E2E8F0 !important;
            }
            .kpi-value { color: #0F172A !important; }
            .chart-box {
                background: #FFFFFF !important;
                border-color: #E2E8F0 !important;
            }
            .chart-title { color: #0F172A !important; }
            .pbi-table th {
                background: #F1F5F9 !important;
                color: #475569 !important;
            }
            .pbi-table td {
                color: #1E293B !important;
                border-bottom-color: #F1F5F9 !important;
            }
        }
    </style>
</head>
<body>

    <div class="powerbi-canvas">
        
        <!-- Header -->
        <div class="pbi-header">
            <div class="pbi-title-group">
                <h1>
                    <span class="material-symbols-outlined text-amber-400 text-3xl">analytics</span>
                    Executive Financial Analytics (PowerBI Style)
                    <span class="pbi-badge">Executive View</span>
                </h1>
                <div class="pbi-meta-sub">
                    Filter Cabang: <strong>{{ $selectedBranch?->name ?? 'Konsolidasi (Seluruh Cabang)' }}</strong> • 
                    Periode: <strong>{{ $month ? date('F Y', mktime(0,0,0,$month,10,$year)) : "Tahun {$year}" }}</strong>
                </div>
            </div>

            <div class="pbi-actions no-print">
                <a href="{{ route('finance.reports.index', request()->all()) }}" class="btn-pbi">
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    Kembali ke ERP
                </a>
                <button onclick="window.print()" class="btn-pbi btn-pbi-primary">
                    <span class="material-symbols-outlined text-base">print</span>
                    Cetak Dashboard PDF
                </button>
            </div>
        </div>

        <!-- 4 Tile KPI Executive Cards -->
        <div class="kpi-grid">
            <div class="kpi-card green">
                <div class="kpi-label">Total Pendapatan</div>
                <div class="kpi-value">Rp {{ number_format($incomeStatement['total_revenue'], 0, ',', '.') }}</div>
                <div class="kpi-sub">Total Revenue Postings</div>
            </div>

            <div class="kpi-card red">
                <div class="kpi-label">Total Beban Operasional</div>
                <div class="kpi-value">Rp {{ number_format($incomeStatement['total_expense'], 0, ',', '.') }}</div>
                <div class="kpi-sub">Total Operational Costs</div>
            </div>

            <div class="kpi-card blue">
                <div class="kpi-label">Laba (Rugi) Bersih</div>
                <div class="kpi-value" style="color: {{ $incomeStatement['net_income'] >= 0 ? '#10B981' : '#F43F5E' }};">
                    Rp {{ number_format($incomeStatement['net_income'], 0, ',', '.') }}
                </div>
                <div class="kpi-sub">Net Profit / Margin Performance</div>
            </div>

            <div class="kpi-card amber">
                <div class="kpi-label">Total Aktiva (Balance)</div>
                <div class="kpi-value">Rp {{ number_format($balanceSheet['total_assets'], 0, ',', '.') }}</div>
                <div class="kpi-sub">Verified Asset Balance</div>
            </div>
        </div>

        <!-- Charts Grid (Visual 1 & Visual 2) -->
        <div class="visual-grid">
            
            <!-- Left Visual: Revenue vs Expenses Breakdown Bar Chart -->
            <div class="chart-box">
                <div class="chart-title">
                    <span>Perbandingan Pendapatan & Beban</span>
                    <span class="text-xs font-normal text-slate-400">PowerBI Interactive Analytics</span>
                </div>
                <div style="height: 260px; position: relative;">
                    <canvas id="financialChart"></canvas>
                </div>
            </div>

            <!-- Right Visual: Profit Margin Donut Chart -->
            <div class="chart-box">
                <div class="chart-title">
                    <span>Net Margin Ratio</span>
                    <span class="text-xs font-normal text-slate-400">Ratio %</span>
                </div>
                <div style="height: 260px; position: relative;">
                    <canvas id="marginDonutChart"></canvas>
                </div>
            </div>

        </div>

        <!-- Detail Financial Tables Grid -->
        <div class="visual-grid" style="grid-template-columns: 1fr 1fr;">
            
            <!-- Table 1: Top Revenue Accounts -->
            <div class="chart-box">
                <div class="chart-title">Rincian Pos Pendapatan</div>
                <table class="pbi-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Akun</th>
                            <th style="text-align: right;">Jumlah (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incomeStatement['revenues'] as $rev)
                            <tr>
                                <td>{{ $rev['code'] }}</td>
                                <td>{{ $rev['name'] }}</td>
                                <td class="num" style="color: #10B981;">{{ number_format($rev['amount'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" style="text-align:center; color:#64748B;">Tidak ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Table 2: Top Expense Accounts -->
            <div class="chart-box">
                <div class="chart-title">Rincian Pos Beban</div>
                <table class="pbi-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Akun</th>
                            <th style="text-align: right;">Jumlah (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incomeStatement['expenses'] as $exp)
                            <tr>
                                <td>{{ $exp['code'] }}</td>
                                <td>{{ $exp['name'] }}</td>
                                <td class="num" style="color: #F43F5E;">{{ number_format($exp['amount'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" style="text-align:center; color:#64748B;">Tidak ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

    </div>

    <!-- Chart.js Render Script -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Bar Chart: Revenue vs Expense
            const ctx1 = document.getElementById('financialChart').getContext('2d');
            new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: ['Pendapatan (Revenue)', 'Beban (Expenses)', 'Laba Bersih (Net Income)'],
                    datasets: [{
                        label: 'Nilai (Rupiah)',
                        data: [
                            {{ $incomeStatement['total_revenue'] }},
                            {{ $incomeStatement['total_expense'] }},
                            {{ $incomeStatement['net_income'] }}
                        ],
                        backgroundColor: ['#10B981', '#F43F5E', '#0EA5E9'],
                        borderRadius: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: { grid: { color: '#334155' }, ticks: { color: '#94A3B8' } },
                        y: { grid: { color: '#334155' }, ticks: { color: '#94A3B8' } }
                    }
                }
            });

            // Donut Chart: Margin Distribution
            const ctx2 = document.getElementById('marginDonutChart').getContext('2d');
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: ['Beban %', 'Laba Bersih %'],
                    datasets: [{
                        data: [
                            {{ $incomeStatement['total_revenue'] > 0 ? round(($incomeStatement['total_expense'] / $incomeStatement['total_revenue']) * 100, 1) : 0 }},
                            {{ $incomeStatement['total_revenue'] > 0 ? round((max(0, $incomeStatement['net_income']) / $incomeStatement['total_revenue']) * 100, 1) : 0 }}
                        ],
                        backgroundColor: ['#F43F5E', '#10B981'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { color: '#94A3B8' } }
                    }
                }
            });
        });
    </script>
</body>
</html>
