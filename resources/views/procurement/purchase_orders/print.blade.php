<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order - {{ $po->po_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; background: #fff; }
            @page { size: A4; margin: 15mm; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sans text-xs p-6">

    <!-- Print Action Floating Bar -->
    <div class="no-print max-w-4xl mx-auto mb-6 flex justify-between items-center bg-white p-4 rounded-2xl shadow-md border border-slate-200">
        <div>
            <h1 class="font-bold text-sm text-slate-800">Dokumen Resmi Purchase Order</h1>
            <p class="text-2xs text-slate-500">Cetak atau simpan sebagai PDF untuk arsip pengadaan & supplier.</p>
        </div>
        <div class="flex gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 cursor-pointer">
                🖨️ Cetak / Simpan PDF
            </button>
            <button onclick="window.close()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs cursor-pointer">
                Tutup
            </button>
        </div>
    </div>

    <!-- Official PO Document Page -->
    <div class="max-w-4xl mx-auto bg-white p-8 rounded-2xl shadow-xl border border-slate-200">
        
        <!-- Document Header -->
        <div class="flex justify-between items-start pb-6 border-b-2 border-slate-900">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900 uppercase">ISTANA LAUNDRY</h1>
                <p class="text-xs font-semibold text-slate-600">Sistem Manajemen Pengadaan & Distribusi</p>
                <p class="text-2xs text-slate-500 mt-1">Cabang: {{ $po->branch?->name ?? 'Pusat' }}</p>
            </div>
            <div class="text-right">
                <span class="inline-block px-3 py-1 bg-slate-900 text-white font-black text-xs uppercase tracking-widest rounded-md mb-2">PURCHASE ORDER</span>
                <p class="font-mono font-bold text-sm text-slate-800">#{{ $po->po_number }}</p>
                <p class="text-2xs text-slate-500">Tanggal: {{ $po->order_date?->format('d M Y') ?? date('d M Y') }}</p>
            </div>
        </div>

        <!-- Supplier & Shipping Info -->
        <div class="grid grid-cols-2 gap-6 py-6 border-b border-slate-200">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Kepada Supplier (Vendor):</span>
                <h3 class="font-bold text-sm text-slate-900">{{ $po->supplier?->name }}</h3>
                <p class="text-2xs text-slate-600 mt-0.5">{{ $po->supplier?->address ?? '-' }}</p>
                <p class="text-2xs text-slate-600 mt-0.5">📞 Telp/WA: {{ $po->supplier?->phone ?? '-' }}</p>
                <p class="text-2xs text-slate-600">✉️ Email: {{ $po->supplier?->email ?? '-' }}</p>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Alamat Tujuan Pengiriman:</span>
                <h3 class="font-bold text-sm text-slate-900">{{ $po->branch?->name ?? 'Gudang Utama' }}</h3>
                <p class="text-2xs text-slate-600 mt-0.5">{{ $po->branch?->address ?? 'Alamat Operasional Cabang Istana Laundry' }}</p>
                <p class="text-2xs text-slate-600 mt-1">📅 <strong>Estimasi Tgl Diterima:</strong> {{ $po->expected_date?->format('d M Y') ?? '-' }}</p>
                @if ($po->pr)
                    <p class="text-2xs text-slate-600">🔗 <strong>Ref Purchase Request:</strong> #{{ $po->pr->pr_number }}</p>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <div class="py-6">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] tracking-wider border-y border-slate-300">
                        <th class="py-2.5 px-3">No</th>
                        <th class="py-2.5 px-3">Deskripsi Produk / Material</th>
                        <th class="py-2.5 px-3 text-center">Kuantitas</th>
                        <th class="py-2.5 px-3 text-right">Harga Satuan (Rp)</th>
                        <th class="py-2.5 px-3 text-right">Subtotal (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-xs">
                    @foreach ($po->items as $index => $item)
                        <tr>
                            <td class="py-3 px-3 text-slate-500 font-mono">{{ $index + 1 }}</td>
                            <td class="py-3 px-3 font-bold text-slate-800">
                                {{ $item->item?->name ?? 'Barang #' . $item->item_id }}
                                <span class="block text-[10px] font-normal text-slate-500">Satuan: {{ $item->item?->unit ?? 'unit' }}</span>
                            </td>
                            <td class="py-3 px-3 text-center font-bold text-slate-800 font-mono">
                                {{ number_format($item->quantity, 0, ',', '.') }} {{ $item->item?->unit }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono">
                                Rp {{ number_format($item->unit_cost, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-right font-bold font-mono text-slate-900">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Financial Summary -->
        <div class="flex justify-end border-t border-slate-300 pt-4">
            <div class="w-72 space-y-1 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal Barang:</span>
                    <span class="font-mono font-semibold">Rp {{ number_format($po->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>PPN Standard (11%):</span>
                    <span class="font-mono font-semibold">Rp {{ number_format($po->tax_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm font-black text-slate-900 pt-2 border-t border-slate-400">
                    <span>TOTAL ORDER:</span>
                    <span class="font-mono">Rp {{ number_format($po->total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Signatures Section -->
        <div class="grid grid-cols-2 gap-12 pt-16 mt-6 border-t border-slate-200 text-center text-xs">
            <div>
                <p class="text-slate-500 font-semibold mb-12">Disetujui Oleh (Purchasing/Owner),</p>
                <div class="w-40 border-b border-slate-800 mx-auto"></div>
                <p class="font-bold text-slate-800 mt-1">Istana Laundry Management</p>
            </div>
            <div>
                <p class="text-slate-500 font-semibold mb-12">Dikonfirmasi Oleh Supplier,</p>
                <div class="w-40 border-b border-slate-800 mx-auto"></div>
                <p class="font-bold text-slate-800 mt-1">{{ $po->supplier?->name ?? 'Vendor' }}</p>
            </div>
        </div>

    </div>

</body>
</html>
