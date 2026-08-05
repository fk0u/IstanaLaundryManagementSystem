<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\PurchaseOrder;
use App\Models\Branch;

class WhatsAppService
{
    /**
     * Generate a corporate formatted receipt message for WhatsApp (Meta Business compatible).
     */
    public function generateReceiptMessage(Order $order): string
    {
        $order->load(['items.service', 'customer', 'branch', 'cashier']);

        $branchName = $order->branch?->name ?? 'Istana Laundry';
        $branchPhone = $order->branch?->phone ?? '-';
        $branchAddress = $order->branch?->address ?? '';
        $cashierName = $order->cashier?->name ?? '-';
        $customerName = $order->customer?->name ?? ($order->customer_name_walkin ?? 'Pelanggan Walk-In');

        $trackUrl = url('/track').'?order_number='.$order->order_number;
        $invoiceUrl = url('/invoices/'.$order->id);

        $lines = [];
        $lines[] = '*[ISTANA LAUNDRY - NOTA TRANSAKSI]*';
        $lines[] = '=================================';
        $lines[] = "*Outlet:* {$branchName}";
        if ($branchAddress) {
            $lines[] = "*Alamat:* {$branchAddress}";
        }
        if ($branchPhone && $branchPhone !== '-') {
            $lines[] = "*Kontak:* {$branchPhone}";
        }
        $lines[] = '---------------------------------';
        $lines[] = "*No. Order:* {$order->order_number}";
        $lines[] = '*Tanggal:* '.$order->created_at->format('d/m/Y H:i').' (UTC+8)';
        $lines[] = "*Pelanggan:* {$customerName}";
        $lines[] = "*Kasir:* {$cashierName}";
        $lines[] = '---------------------------------';
        $lines[] = '*DETAIL LAYANAN:*';

        foreach ($order->items as $idx => $item) {
            $n = $idx + 1;
            $serviceName = $item->service?->name ?? 'Layanan Laundry';
            $qty = rtrim(rtrim(number_format($item->quantity, 2, ',', '.'), '0'), ',');
            $unit = $item->unit ?? 'kg';
            $unitPrice = number_format($item->unit_price, 0, ',', '.');
            $subtotal = number_format($item->subtotal, 0, ',', '.');

            $lines[] = "{$n}. *{$serviceName}*";
            $lines[] = "   {$qty} {$unit} × Rp {$unitPrice} = Rp {$subtotal}";
        }

        $lines[] = '---------------------------------';
        $lines[] = 'Subtotal : Rp '.number_format($order->subtotal, 0, ',', '.');

        if ($order->discount_amount > 0) {
            $lines[] = 'Diskon   : -Rp '.number_format($order->discount_amount, 0, ',', '.');
        }
        if ($order->points_used > 0) {
            $lines[] = 'Poin     : -Rp '.number_format($order->points_used, 0, ',', '.');
        }
        if ($order->tax_amount > 0) {
            $lines[] = 'Pajak    : +Rp '.number_format($order->tax_amount, 0, ',', '.');
        }

        $lines[] = '*TOTAL    : Rp '.number_format($order->total, 0, ',', '.').'*';
        $lines[] = 'Bayar    : Rp '.number_format($order->paid_amount, 0, ',', '.');

        if ($order->change_amount > 0) {
            $lines[] = 'Kembalian: Rp '.number_format($order->change_amount, 0, ',', '.');
        }

        $lines[] = '---------------------------------';
        $lines[] = '*Pembayaran:* '.strtoupper($order->payment_method);
        $lines[] = '*Status:* '.($order->payment_status === 'paid' ? '[LUNAS]' : '['.strtoupper($order->payment_status).']');

        if ($order->estimated_done_at) {
            $lines[] = '*Est. Selesai:* '.$order->estimated_done_at->format('d M Y H:i').' (UTC+8)';
        }

        $lines[] = '=================================';
        $lines[] = '*AKSI & PELACAKAN CEPAT:*';
        $lines[] = "> *Lacak Cucian:* {$trackUrl}";
        $lines[] = "> *Struk Digital:* {$invoiceUrl}";
        $lines[] = '=================================';
        $lines[] = 'Terima kasih telah mempercayakan laundry Anda kepada Istana Laundry!';

        return implode("\n", $lines);
    }

    /**
     * Generate corporate "Ready for Pickup" notification message for WhatsApp (Meta Business compatible).
     */
    public function generateReadyNotificationMessage(Order $order): string
    {
        $order->load(['customer', 'branch']);

        $branchName = $order->branch?->name ?? 'Istana Laundry';
        $customerName = $order->customer?->name ?? ($order->customer_name_walkin ?? 'Pelanggan');
        $trackUrl = url("/track?order_number={$order->order_number}");
        $invoiceUrl = url("/invoices/{$order->id}");

        $unpaidAmount = max(0, $order->total - $order->paid_amount);

        $lines = [];
        $lines[] = '*[ISTANA LAUNDRY - NOTIFIKASI CUCIAN SELESAI]*';
        $lines[] = '=================================';
        $lines[] = "Halo Kak *{$customerName}*,";
        $lines[] = 'Cucian Anda telah *SELESAI DIPROSES & SIAP DIAMBIL!*';
        $lines[] = '---------------------------------';
        $lines[] = "*No. Nota:* {$order->order_number}";
        $lines[] = "*Outlet:* {$branchName}";
        $lines[] = '*Total Tagihan:* Rp '.number_format($order->total, 0, ',', '.');

        if ($order->payment_status === 'paid') {
            $lines[] = '*Status Bayar:* [LUNAS]';
        } else {
            $lines[] = '*Status Bayar:* [BELUM LUNAS] (Sisa Tagihan: Rp '.number_format($unpaidAmount, 0, ',', '.').')';
        }

        $lines[] = '---------------------------------';
        $lines[] = '*AKSI & PELACAKAN CEPAT:*';
        $lines[] = "> *Lacak Detail Order:* {$trackUrl}";
        $lines[] = "> *Lihat Struk Digital:* {$invoiceUrl}";
        $lines[] = '=================================';
        $lines[] = 'Silakan datang ke outlet untuk pengambilan. Terima kasih telah memilih layanan Istana Laundry!';

        return implode("\n", $lines);
    }

    /**
     * Generate corporate Purchase Order message for Suppliers (Meta Business compatible).
     */
    public function generatePurchaseOrderMessage(PurchaseOrder $po): string
    {
        $po->load(['supplier', 'branch', 'items.item']);

        $supplierName = $po->supplier?->name ?? 'Supplier';
        $branchName = $po->branch?->name ?? 'Istana Laundry';
        $orderDateStr = $po->order_date ? $po->order_date->format('d/m/Y') : date('d/m/Y');
        $expectedDateStr = $po->expected_date ? $po->expected_date->format('d/m/Y') : '-';

        $lines = [];
        $lines[] = '*[ISTANA LAUNDRY - OFFICIAL PURCHASE ORDER]*';
        $lines[] = '=================================';
        $lines[] = "Yth. *{$supplierName}*,";
        $lines[] = 'Berikut adalah rincian Purchase Order (PO) resmi dari Istana Laundry:';
        $lines[] = '---------------------------------';
        $lines[] = "*No. PO:* {$po->po_number}";
        $lines[] = "*Tanggal PO:* {$orderDateStr}";
        $lines[] = "*Estimasi Diterima:* {$expectedDateStr}";
        $lines[] = "*Cabang Tujuan:* {$branchName}";
        $lines[] = '---------------------------------';
        $lines[] = '*DETAIL BARANG PESANAN:*';

        foreach ($po->items as $idx => $item) {
            $n = $idx + 1;
            $name = $item->item?->name ?? 'Barang #'.$item->item_id;
            $unit = $item->item?->unit ?? 'unit';
            $qty = number_format($item->quantity, 0, ',', '.');
            $cost = 'Rp '.number_format($item->unit_cost, 0, ',', '.');
            $sub = 'Rp '.number_format($item->subtotal, 0, ',', '.');

            $lines[] = "{$n}. *{$name}*";
            $lines[] = "   {$qty} {$unit} × {$cost} = {$sub}";
        }

        $lines[] = '---------------------------------';
        $lines[] = 'Subtotal : Rp '.number_format($po->subtotal, 0, ',', '.');
        if ($po->tax_amount > 0) {
            $lines[] = 'PPN (11%): +Rp '.number_format($po->tax_amount, 0, ',', '.');
        }
        $lines[] = '*GRAND TOTAL PO: Rp '.number_format($po->total, 0, ',', '.').'*';
        $lines[] = '=================================';
        $lines[] = 'Mohon konfirmasi dan diproses pengirimannya. Terima kasih atas kerja samanya!';

        return implode("\n", $lines);
    }

    /**
     * Generate corporate CRM Greeting message for Members.
     */
    public function generateCustomerGreetingMessage(Customer $customer, ?Branch $branch = null): string
    {
        $branchName = $branch?->name ?? 'Istana Laundry';
        $tierName = strtoupper($customer->member_tier ?? 'BRONZE');

        $lines = [];
        $lines[] = '*[ISTANA LAUNDRY - INFORMASI MEMBER]*';
        $lines[] = '=================================';
        $lines[] = "Halo Kak *{$customer->name}*,";
        $lines[] = "Terima kasih telah menjadi pelanggan setia {$branchName}!";
        $lines[] = '---------------------------------';
        $lines[] = "*Tier Member:* {$tierName}";
        $lines[] = '*Saldo Poin:* '.number_format($customer->loyalty_points, 0, ',', '.').' Poin';
        $lines[] = '---------------------------------';
        $lines[] = 'Nikmati berbagai kemudahan cuci, promo menarik, dan layanan cuci antar-jemput kami!';
        $lines[] = '=================================';
        $lines[] = 'Salam Hangat, Tim Istana Laundry';

        return implode("\n", $lines);
    }

    /**
     * Generate normalized, RFC 3986 rawurlencode WhatsApp wa.me URL.
     */
    public function generateWhatsAppUrl(string $phone, string $message): string
    {
        $normalizedPhone = $this->normalizePhoneNumber($phone);
        $normalizedMessage = str_replace(["\r\n", "\r"], "\n", $message);
        $encodedMessage = rawurlencode($normalizedMessage);

        return "https://wa.me/{$normalizedPhone}?text={$encodedMessage}";
    }

    /**
     * Normalize phone number to international Indonesian format (628...).
     */
    public function normalizePhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        if (! str_starts_with($phone, '62') && ! empty($phone)) {
            $phone = '62'.$phone;
        }

        return $phone;
    }

    /**
     * Generate Meta WhatsApp Cloud API Interactive Button Payload (JSON schema).
     */
    public function generateMetaInteractivePayload(string $phone, string $headerText, string $bodyText, string $footerText = 'Istana Laundry Enterprise', array $buttons = []): array
    {
        $normalizedPhone = $this->normalizePhoneNumber($phone);

        $actionButtons = [];
        foreach ($buttons as $idx => $btn) {
            $actionButtons[] = [
                'type' => 'reply',
                'reply' => [
                    'id' => $btn['id'] ?? ('btn_'.($idx + 1)),
                    'title' => mb_substr($btn['title'] ?? 'Aksi', 0, 20),
                ],
            ];
        }

        return [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $normalizedPhone,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'header' => [
                    'type' => 'text',
                    'text' => mb_substr($headerText, 0, 60),
                ],
                'body' => [
                    'text' => $bodyText,
                ],
                'footer' => [
                    'text' => mb_substr($footerText, 0, 60),
                ],
                'action' => [
                    'buttons' => $actionButtons,
                ],
            ],
        ];
    }

    /**
     * Generate Gateway API Payload for third-party providers (Fonnte, Wablas, etc.).
     */
    public function generateGatewayPayload(string $phone, string $message, array $buttons = []): array
    {
        return [
            'target' => $this->normalizePhoneNumber($phone),
            'message' => $message,
            'buttons' => $buttons,
            'countryCode' => '62',
        ];
    }
}

