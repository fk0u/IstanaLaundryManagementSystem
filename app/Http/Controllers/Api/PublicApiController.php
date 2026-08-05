<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductionStatusLog;
use App\Models\Service;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PublicApiController extends Controller
{
    protected WhatsAppService $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * GET /api/v1/branches
     * List active outlets/branches for online order form & company profile.
     */
    public function branches()
    {
        $branches = Branch::query()
            ->where('is_active', true)
            ->select(['id', 'name', 'code', 'phone', 'address'])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $branches,
        ]);
    }

    /**
     * GET /api/v1/services
     * List available laundry services and base/branch prices.
     */
    public function services(Request $request)
    {
        $branchId = $request->query('branch_id');

        $services = Service::query()
            ->where('is_active', true)
            ->with('branchPrices')
            ->get()
            ->map(function ($s) use ($branchId) {
            $price = (float) $s->base_price;
            if ($branchId) {
                $bp = $s->branchPrices->where('branch_id', $branchId)->first();
                if ($bp) {
                    $price = (float) $bp->price;
                }
            }

            return [
                'id' => $s->id,
                'name' => $s->name,
                'type' => $s->type,
                'unit' => $s->unit,
                'price' => $price,
                'base_price' => (float) $s->base_price,
                'description' => $s->description,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $services,
        ]);
    }

    /**
     * GET /api/v1/track/{orderNumber} or POST /api/v1/track
     * Public order status tracking with production timeline and masked customer details.
     */
    public function track(Request $request, ?string $orderNumber = null)
    {
        $number = $orderNumber ?? $request->input('order_number') ?? $request->query('order_number');

        if (! $number) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nomor nota/order wajib diisi.',
            ], 422);
        }

        $cleanNumber = strtoupper(trim($number));

        $order = Order::with([
            'branch',
            'customer',
            'items.service',
            'productionStatusLogs',
        ])
            ->where('order_number', $cleanNumber)
            ->first();

        if (! $order) {
            return response()->json([
                'status' => 'error',
                'message' => "Order dengan nomor nota '{$cleanNumber}' tidak ditemukan.",
            ], 404);
        }

        $phone = $order->customer?->phone ?? $order->delivery_phone;
        $maskedPhone = $phone ? (substr($phone, 0, 4).'****'.substr($phone, -2)) : '-';

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'order_type' => $order->order_type,
                'branch' => [
                    'id' => $order->branch?->id,
                    'name' => $order->branch?->name ?? 'Istana Laundry',
                    'code' => $order->branch?->code,
                    'phone' => $order->branch?->phone,
                    'address' => $order->branch?->address,
                ],
                'customer' => [
                    'name' => $order->customer_display_name,
                    'phone_masked' => $maskedPhone,
                ],
                'delivery' => [
                    'address' => $order->delivery_address,
                    'phone' => $maskedPhone,
                    'latitude' => $order->latitude ? (float) $order->latitude : null,
                    'longitude' => $order->longitude ? (float) $order->longitude : null,
                    'google_maps_url' => $order->google_maps_url,
                    'pickup_scheduled_at' => $order->pickup_scheduled_at?->format('Y-m-d H:i:s'),
                ],
                'production_status' => $order->production_status,
                'payment_status' => $order->payment_status,
                'total' => (float) $order->total,
                'estimated_done_at' => $order->estimated_done_at?->format('d M Y H:i').' (UTC+8)',
                'created_at' => $order->created_at?->format('d/m/Y H:i').' (UTC+8)',
                'items' => $order->items->map(fn ($item) => [
                    'service_name' => $item->service?->name ?? 'Layanan Laundry',
                    'quantity' => (float) $item->quantity,
                    'unit' => $item->unit ?? 'kg',
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => (float) $item->subtotal,
                ]),
                'timeline' => $order->productionStatusLogs->map(fn ($log) => [
                    'status' => $log->status,
                    'notes' => $log->notes,
                    'timestamp' => $log->created_at?->format('d/m/Y H:i').' (UTC+8)',
                ]),
                'tracking_url' => url('/track?order_number='.$order->order_number),
                'invoice_url' => url('/invoices/'.$order->id),
            ],
        ]);
    }

    /**
     * POST /api/v1/orders/online
     * Public endpoint for online pickup order submissions with precise GPS coordinates.
     */
    public function storeOnlineOrder(Request $request)
    {
        $validated = $request->validate([
            'branch_code' => 'nullable|string',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:25',
            'delivery_address' => 'required|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'google_maps_url' => 'nullable|string|max:500',
            'service_id' => 'nullable|integer|exists:services,id',
            'service_name' => 'nullable|string|max:100',
            'quantity' => 'nullable|numeric|min:0.1',
            'pickup_scheduled_at' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        return DB::transaction(function () use ($validated) {
            // Determine branch
            $branch = null;
            if (! empty($validated['branch_id'])) {
                $branch = Branch::find($validated['branch_id']);
            } elseif (! empty($validated['branch_code'])) {
                $branch = Branch::where('code', strtoupper($validated['branch_code']))->first();
            }
            if (! $branch) {
                $branch = Branch::where('is_active', true)->first() ?? Branch::first();
            }

            // Assign cashier fallback (System Admin / First User)
            $cashier = User::where('branch_id', $branch->id)->first()
                ?? User::whereHas('roles', fn ($q) => $q->whereIn('name', ['Owner', 'Developer', 'Kasir']))->first()
                ?? User::first();

            // Normalize customer phone
            $phone = $this->whatsAppService->normalizePhoneNumber($validated['customer_phone']);

            // Find or create customer
            $customer = Customer::where('phone', $phone)
                ->orWhere('phone', $validated['customer_phone'])
                ->first();

            if (! $customer) {
                $customer = Customer::create([
                    'branch_id' => $branch->id,
                    'name' => $validated['customer_name'],
                    'phone' => $phone ?: $validated['customer_phone'],
                    'address' => $validated['delivery_address'],
                    'member_code' => 'MBR-'.strtoupper(Str::random(6)),
                    'loyalty_tier' => 'Bronze',
                    'loyalty_points' => 0,
                ]);
            }

            // Determine Service & Pricing
            $service = null;
            if (! empty($validated['service_id'])) {
                $service = Service::find($validated['service_id']);
            } elseif (! empty($validated['service_name'])) {
                $service = Service::where('name', 'like', '%'.$validated['service_name'].'%')->first();
            }
            if (! $service) {
                $service = Service::where('type', 'kilogram')->first() ?? Service::first();
            }

            $qty = (float) ($validated['quantity'] ?? 1.0);
            $unitPrice = (float) $service->base_price;
            $bp = $service->branchPrices()->where('branch_id', $branch->id)->first();
            if ($bp) {
                $unitPrice = (float) $bp->price;
            }
            $subtotal = $qty * $unitPrice;
            $total = $subtotal;

            // Generate Order Number
            $dateStr = now()->format('Ymd');
            $seq = str_pad((string) rand(100, 9999), 4, '0', STR_PAD_LEFT);
            $orderNumber = "ONLINE-{$branch->code}-{$dateStr}-{$seq}";

            // Construct Google Maps URL if lat/lng available and maps URL not provided
            $lat = isset($validated['latitude']) ? (float) $validated['latitude'] : null;
            $lng = isset($validated['longitude']) ? (float) $validated['longitude'] : null;
            $mapsUrl = $validated['google_maps_url'] ?? null;

            if (! $mapsUrl && $lat && $lng) {
                $mapsUrl = "https://www.google.com/maps?q={$lat},{$lng}";
            }

            // Create Order
            $order = Order::create([
                'order_number' => $orderNumber,
                'branch_id' => $branch->id,
                'customer_id' => $customer->id,
                'cashier_id' => $cashier?->id ?? 1,
                'order_type' => 'pickup_delivery',
                'delivery_address' => $validated['delivery_address'],
                'delivery_phone' => $phone ?: $validated['customer_phone'],
                'latitude' => $lat,
                'longitude' => $lng,
                'google_maps_url' => $mapsUrl,
                'pickup_scheduled_at' => ! empty($validated['pickup_scheduled_at']) ? $validated['pickup_scheduled_at'] : now()->addHours(2),
                'production_status' => 'TERIMA',
                'payment_method' => 'cash',
                'payment_status' => 'pending',
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'points_used' => 0,
                'tax_amount' => 0,
                'total' => $total,
                'paid_amount' => 0,
                'change_amount' => 0,
                'notes' => $validated['notes'] ?? 'Order online via website',
                'estimated_done_at' => now()->addDays(2),
            ]);

            // Create Order Item
            OrderItem::create([
                'order_id' => $order->id,
                'service_id' => $service->id,
                'quantity' => $qty,
                'unit' => $service->unit ?? 'kg',
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ]);

            // Log Initial Production Status
            ProductionStatusLog::create([
                'order_id' => $order->id,
                'status' => 'TERIMA',
                'notes' => 'Pemesanan online berhasil diterima dari website.',
                'updated_by' => $cashier->id,
            ]);

            // Construct WhatsApp Notification Message for Branch
            $waMessage = "*[PEMESANAN ONLINE BARU]*\n";
            $waMessage .= "===================\n";
            $waMessage .= "*Outlet:* {$branch->name}\n";
            $waMessage .= "*No. Order:* {$order->order_number}\n";
            $waMessage .= "*Pemesan:* {$customer->name}\n";
            $waMessage .= "*Telepon:* {$phone}\n";
            $waMessage .= "*Alamat Penjemputan:* {$order->delivery_address}\n";
            if ($mapsUrl) {
                $waMessage .= "*Titik GPS:* {$mapsUrl}\n";
            }
            $waMessage .= "-------------------\n";
            $waMessage .= "*Layanan:* {$service->name} ({$qty} {$service->unit})\n";
            $waMessage .= "*Estimasi Total:* Rp ".number_format($total, 0, ',', '.')."\n";
            if ($order->notes) {
                $waMessage .= "*Catatan:* {$order->notes}\n";
            }
            $waMessage .= "===================\n";
            $waMessage .= "Mohon segera atur kurir penjemputan. Terima kasih!";

            $waUrl = $this->whatsAppService->generateWhatsAppUrl($branch->phone ?: '08115550001', $waMessage);
            $trackUrl = url('/track?order_number='.$order->order_number);

            return response()->json([
                'status' => 'success',
                'message' => 'Pemesanan online berhasil dibuat!',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'branch_name' => $branch->name,
                    'customer_name' => $customer->name,
                    'delivery_address' => $order->delivery_address,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'google_maps_url' => $mapsUrl,
                    'estimated_total' => $total,
                    'tracking_url' => $trackUrl,
                    'whatsapp_url' => $waUrl,
                ],
            ], 201);
        });
    }
}
