<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderTrackingResource;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    /**
     * Public order tracking by order number (no authentication required),
     * mirroring the existing `/track` web page.
     */
    public function show(Request $request, string $orderNumber)
    {
        // Validate order number format (basic alphanumeric check)
        if (! preg_match('/^[A-Z0-9-]+$/', $orderNumber)) {
            return response()->json([
                'message' => 'Format nomor nota tidak valid.',
            ], 400);
        }

        $order = Order::with(['customer', 'branch', 'items.service', 'productionStatusLogs'])
            ->where('order_number', $orderNumber)
            ->first();

        if (! $order) {
            return response()->json([
                'message' => 'Nomor nota tidak ditemukan.',
            ], 404);
        }

        return new OrderTrackingResource($order);
    }
}
