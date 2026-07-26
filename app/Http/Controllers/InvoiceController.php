<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    protected WhatsAppService $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Show invoice page (full A4-like view).
     */
    public function show(Order $order)
    {
        $order->load(['items.service', 'customer', 'branch', 'cashier', 'promo']);

        return view('invoices.show', compact('order'));
    }

    /**
     * Show thermal receipt view (58mm/80mm format).
     */
    public function receipt(Order $order)
    {
        $order->load(['items.service', 'customer', 'branch', 'cashier']);

        return view('invoices.receipt', compact('order'));
    }

    /**
     * Redirect to WhatsApp with receipt message.
     */
    public function sendWhatsApp(Order $order, Request $request)
    {
        $order->load(['items.service', 'customer', 'branch', 'cashier']);

        // Get phone number: from request, from customer, or prompt
        $phone = $request->query('phone')
                 ?? $order->customer?->phone
                 ?? null;

        if (! $phone) {
            return redirect()->back()->with('error', 'Nomor telepon pelanggan tidak tersedia. Silakan isi data pelanggan terlebih dahulu.');
        }

        $message = $this->whatsAppService->generateReceiptMessage($order);
        $url = $this->whatsAppService->generateWhatsAppUrl($phone, $message);

        return redirect()->away($url);
    }
}
