<?php

namespace App\Services;

use App\Models\Order;

class WhatsAppService
{
    /**
     * Generate a formatted receipt message for WhatsApp.
     */
    public function generateReceiptMessage(Order $order): string
    {
        $order->load(['items.service', 'customer', 'branch', 'cashier']);

        $branchName = $order->branch?->name ?? 'Istana Laundry';
        $branchPhone = $order->branch?->phone ?? '-';
        $branchAddress = $order->branch?->address ?? '';
        $cashierName = $order->cashier?->name ?? '-';

        $lines = [];
        $lines[] = '🧺 *ISTANA LAUNDRY*';
        $lines[] = "📍 {$branchName}";
        if ($branchAddress) {
            $lines[] = $branchAddress;
        }
        $lines[] = '────────────────';
        $lines[] = "📋 *No. Order:* {$order->order_number}";
        $lines[] = "👤 Kasir: {$cashierName}";
        $lines[] = '📅 Tanggal: '.$order->created_at->format('d/m/Y H:i');

        if ($order->customer) {
            $lines[] = "🙋 Pelanggan: {$order->customer->name}";
        }

        $lines[] = '';
        $lines[] = '📋 *Detail Layanan:*';

        foreach ($order->items as $item) {
            $serviceName = $item->service?->name ?? 'Layanan';
            $qty = rtrim(rtrim(number_format($item->quantity, 2, ',', '.'), '0'), ',');
            $unit = $item->unit ?? 'kg';
            $unitPrice = number_format($item->unit_price, 0, ',', '.');
            $subtotal = number_format($item->subtotal, 0, ',', '.');

            $lines[] = "• {$serviceName}";
            $lines[] = "  {$qty} {$unit} × Rp {$unitPrice} = Rp {$subtotal}";
        }

        $lines[] = '';
        $lines[] = '────────────────';
        $lines[] = 'Subtotal: Rp '.number_format($order->subtotal, 0, ',', '.');

        if ($order->discount_amount > 0) {
            $lines[] = 'Diskon: -Rp '.number_format($order->discount_amount, 0, ',', '.');
        }
        if ($order->points_used > 0) {
            $lines[] = 'Poin: -Rp '.number_format($order->points_used, 0, ',', '.');
        }
        if ($order->tax_amount > 0) {
            $lines[] = 'Pajak: +Rp '.number_format($order->tax_amount, 0, ',', '.');
        }

        $lines[] = '*TOTAL: Rp '.number_format($order->total, 0, ',', '.').'*';
        $lines[] = 'Bayar: Rp '.number_format($order->paid_amount, 0, ',', '.');

        if ($order->change_amount > 0) {
            $lines[] = 'Kembalian: Rp '.number_format($order->change_amount, 0, ',', '.');
        }

        $lines[] = '';
        $lines[] = '💳 Metode: '.strtoupper($order->payment_method);
        $lines[] = '✅ Status: '.($order->payment_status === 'paid' ? 'LUNAS' : strtoupper($order->payment_status));

        if ($order->estimated_done_at) {
            $lines[] = '';
            $lines[] = '📅 Estimasi selesai: '.$order->estimated_done_at->format('d M Y');
        }

        $lines[] = '';
        $lines[] = 'Terima kasih telah menggunakan layanan kami! 🙏';
        $lines[] = 'Istana Laundry - Premium Care';

        return implode("\n", $lines);
    }

    /**
     * Generate "Ready for Pickup" notification message for WhatsApp.
     */
    public function generateReadyNotificationMessage(Order $order): string
    {
        $order->load(['customer', 'branch']);

        $branchName = $order->branch?->name ?? 'Istana Laundry';
        $customerName = $order->customer?->name ?? 'Pelanggan';
        $trackUrl = url("/track?order_number={$order->order_number}");

        $lines = [];
        $lines[] = "🧺 *ISTANA LAUNDRY — {$branchName}*";
        $lines[] = "Halo Kak {$customerName}, cucian Anda sudah *SELESAI & SIAP DIAMBIL!* 🎉";
        $lines[] = '────────────────';
        $lines[] = "📋 *No. Nota:* {$order->order_number}";
        $lines[] = '💰 *Total Tagihan:* Rp '.number_format($order->total, 0, ',', '.');
        $lines[] = '💳 *Status Bayar:* '.($order->payment_status === 'paid' ? '✅ LUNAS' : '⚠️ BELUM LUNAS (Rp '.number_format($order->total - $order->paid_amount, 0, ',', '.').')');
        $lines[] = '';
        $lines[] = "🔍 *Lacak Detail Order:* {$trackUrl}";
        $lines[] = '';
        $lines[] = 'Silakan datang ke outlet untuk pengambilan. Terima kasih telah mempercayakan laundry Anda kepada kami! 🙏';

        return implode("\n", $lines);
    }

    /**
     * Generate WhatsApp wa.me URL.
     */
    public function generateWhatsAppUrl(string $phone, string $message): string
    {
        // Normalize phone number to international format
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Convert 08xx to 628xx
        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        // Ensure it starts with country code
        if (! str_starts_with($phone, '62')) {
            $phone = '62'.$phone;
        }

        $encodedMessage = urlencode($message);

        return "https://wa.me/{$phone}?text={$encodedMessage}";
    }
}
