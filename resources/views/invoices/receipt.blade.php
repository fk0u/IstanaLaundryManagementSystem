<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk #{{ $order->order_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', monospace, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1rem;
        }

        .receipt {
            background: white;
            width: 100%;
            max-width: 320px;
            padding: 1.5rem 1.25rem;
            border-radius: 1rem;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            margin-bottom: 1rem;
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px dashed #e2e8f0;
        }

        .receipt-header h1 {
            font-size: 1.25rem;
            font-weight: 900;
            color: #FF6600;
            letter-spacing: -0.03em;
        }

        .receipt-header .branch-info {
            font-size: 0.625rem;
            color: #94a3b8;
            font-weight: 600;
            margin-top: 0.25rem;
            line-height: 1.4;
        }

        .receipt-meta {
            font-size: 0.625rem;
            color: #64748b;
            margin-bottom: 1rem;
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 0.25rem 0.5rem;
        }
        .receipt-meta dt { font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; }
        .receipt-meta dd { font-weight: 600; text-align: right; }

        .divider {
            border: none;
            border-top: 1px dashed #e2e8f0;
            margin: 0.75rem 0;
        }

        .items-table {
            width: 100%;
            font-size: 0.6875rem;
            border-collapse: collapse;
        }
        .items-table th {
            font-size: 0.5625rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.25rem 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .items-table td {
            padding: 0.375rem 0;
            font-weight: 500;
            vertical-align: top;
        }
        .items-table .item-name { font-weight: 700; color: #334155; }
        .items-table .item-qty { color: #64748b; font-size: 0.625rem; }
        .items-table .item-price { text-align: right; font-weight: 700; font-variant-numeric: tabular-nums; }

        .totals {
            font-size: 0.6875rem;
        }
        .totals .row {
            display: flex;
            justify-content: space-between;
            padding: 0.25rem 0;
            font-weight: 500;
            color: #64748b;
        }
        .totals .row.discount { color: #dc2626; }
        .totals .row.grand-total {
            font-size: 1rem;
            font-weight: 900;
            color: #0f172a;
            padding: 0.5rem 0;
            border-top: 2px solid #0f172a;
            margin-top: 0.25rem;
        }
        .totals .row.payment { font-weight: 600; color: #334155; }
        .totals .row.change { color: #16a34a; font-weight: 700; }

        .footer {
            text-align: center;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px dashed #e2e8f0;
        }
        .footer .thank-you {
            font-size: 0.8125rem;
            font-weight: 800;
            color: #334155;
            margin-bottom: 0.25rem;
        }
        .footer .sub {
            font-size: 0.5625rem;
            color: #94a3b8;
            font-weight: 500;
            line-height: 1.5;
        }

        .status-badge {
            display: inline-block;
            padding: 0.125rem 0.5rem;
            border-radius: 999px;
            font-size: 0.5625rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .status-paid { background: #dcfce7; color: #16a34a; }
        .status-pending { background: #fef3c7; color: #d97706; }

        /* Action Buttons */
        .actions {
            width: 100%;
            max-width: 320px;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 1rem;
            border-radius: 0.75rem;
            font-size: 0.8125rem;
            font-weight: 700;
            cursor: pointer;
            border: none;
            transition: all 0.15s;
            text-decoration: none;
            text-align: center;
        }
        .btn:active { transform: scale(0.98); }
        .btn-primary {
            background: #FF6600;
            color: white;
            box-shadow: 0 4px 12px rgba(255,102,0,0.3);
        }
        .btn-primary:hover { background: #E65C00; }
        .btn-whatsapp {
            background: #25D366;
            color: white;
            box-shadow: 0 4px 12px rgba(37,211,102,0.3);
        }
        .btn-whatsapp:hover { background: #1DA851; }
        .btn-outline {
            background: white;
            color: #334155;
            border: 1px solid #e2e8f0;
        }
        .btn-outline:hover { background: #f8fafc; }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            .receipt {
                box-shadow: none;
                border-radius: 0;
                max-width: 80mm;
                padding: 0;
            }
            .actions, .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="receipt" id="receipt">
        <!-- Header -->
        <div class="receipt-header">
            <h1>ISTANA LAUNDRY</h1>
            <div class="branch-info">
                {{ $order->branch?->name ?? 'Cabang Utama' }}<br>
                {{ $order->branch?->address ?? '' }}<br>
                {{ $order->branch?->phone ? 'Telp: ' . $order->branch->phone : '' }}
            </div>
        </div>

        <!-- Meta Info -->
        <dl class="receipt-meta">
            <dt>No. Order</dt>
            <dd>{{ $order->order_number }}</dd>
            <dt>Tanggal</dt>
            <dd>{{ $order->created_at->format('d/m/Y H:i') }}</dd>
            <dt>Kasir</dt>
            <dd>{{ $order->cashier?->name ?? '-' }}</dd>
            @if($order->customer)
                <dt>Pelanggan</dt>
                <dd>{{ $order->customer->name }}</dd>
            @endif
            <dt>Status</dt>
            <dd>
                <span class="status-badge {{ $order->payment_status === 'paid' ? 'status-paid' : 'status-pending' }}">
                    {{ $order->payment_status === 'paid' ? 'LUNAS' : strtoupper($order->payment_status) }}
                </span>
            </dd>
        </dl>

        <hr class="divider">

        <!-- Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="text-align:left;">Layanan</th>
                    <th style="text-align:right;">Harga</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>
                            <div class="item-name">{{ $item->service?->name ?? 'Layanan' }}</div>
                            <div class="item-qty">{{ rtrim(rtrim(number_format($item->quantity, 2, ',', '.'), '0'), ',') }} {{ $item->unit ?? 'kg' }} × Rp {{ number_format($item->unit_price, 0, ',', '.') }}</div>
                        </td>
                        <td class="item-price">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <hr class="divider">

        <!-- Totals -->
        <div class="totals">
            <div class="row">
                <span>Subtotal</span>
                <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
            </div>
            @if($order->discount_amount > 0)
                <div class="row discount">
                    <span>Diskon</span>
                    <span>-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                </div>
            @endif
            @if($order->points_used > 0)
                <div class="row discount">
                    <span>Poin Terpakai</span>
                    <span>-Rp {{ number_format($order->points_used, 0, ',', '.') }}</span>
                </div>
            @endif
            @if($order->tax_amount > 0)
                <div class="row">
                    <span>Pajak</span>
                    <span>Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="row grand-total">
                <span>TOTAL</span>
                <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
            </div>
            <div class="row payment">
                <span>Bayar ({{ strtoupper($order->payment_method) }})</span>
                <span>Rp {{ number_format($order->paid_amount, 0, ',', '.') }}</span>
            </div>
            @if($order->change_amount > 0)
                <div class="row change">
                    <span>Kembalian</span>
                    <span>Rp {{ number_format($order->change_amount, 0, ',', '.') }}</span>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="thank-you">Terima Kasih! 🙏</div>
            <div class="sub">
                @if($order->estimated_done_at)
                    Estimasi selesai: {{ $order->estimated_done_at->format('d M Y') }}<br>
                @endif
                Simpan struk ini sebagai bukti transaksi.
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="actions no-print">
        <button class="btn btn-primary" onclick="window.print()">
            <span class="material-symbols-outlined" style="font-size:18px;">print</span>
            Cetak Struk
        </button>

        @if($order->customer?->phone)
            <a href="{{ route('invoices.whatsapp', $order) }}" class="btn btn-whatsapp" target="_blank">
                <span style="font-size:18px;">📱</span>
                Kirim via WhatsApp
            </a>
        @else
            <a href="{{ route('invoices.whatsapp', ['order' => $order->id, 'phone' => '']) }}" 
               class="btn btn-whatsapp" 
               onclick="event.preventDefault(); let p = prompt('Masukkan nomor WhatsApp pelanggan (contoh: 08123456789):'); if(p) window.open('{{ route('invoices.whatsapp', $order) }}?phone=' + encodeURIComponent(p), '_blank');">
                <span style="font-size:18px;">📱</span>
                Kirim via WhatsApp
            </a>
        @endif

        <a href="{{ route('invoices.show', $order) }}" class="btn btn-outline">
            <span class="material-symbols-outlined" style="font-size:18px;">description</span>
            Lihat Invoice Lengkap
        </a>

        <a href="{{ route('pos.index') }}" class="btn btn-outline">
            <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
            Kembali ke POS
        </a>
    </div>
</body>
</html>
