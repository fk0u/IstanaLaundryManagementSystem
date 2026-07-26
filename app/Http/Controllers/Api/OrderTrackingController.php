<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
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
        $order = Order::with(['customer', 'branch', 'items.service', 'productionStatusLogs.updater'])
            ->where('order_number', $orderNumber)
            ->first();

        if (! $order) {
            return response()->json([
                'message' => 'Nomor nota tidak ditemukan.',
            ], 404);
        }

        return new OrderResource($order);
    }
}
