<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderTrackingResource extends JsonResource
{
    /**
     * Transform the resource into an array for public tracking.
     * Limits PII exposure - customer phone is masked.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'branch' => $this->whenLoaded('branch', fn () => [
                'name' => $this->branch->name,
                'code' => $this->branch->code,
            ]),
            'customer' => $this->whenLoaded('customer', fn () => [
                'name' => $this->customer?->name,
                // Phone masked for public tracking - show only last 4 digits
                'phone' => $this->customer?->phone ? substr($this->customer->phone, 0, -4) . '****' : null,
            ]),
            'production_status' => $this->production_status,
            'payment_status' => $this->payment_status,
            'total' => (float) $this->total,
            'estimated_done_at' => $this->estimated_done_at,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'service_name' => $item->service?->name,
                'quantity' => (float) $item->quantity,
            ])),
            'production_status_logs' => $this->whenLoaded('productionStatusLogs', fn () => $this->productionStatusLogs->map(fn ($log) => [
                'status' => $log->status,
                'notes' => $log->notes,
                'created_at' => $log->created_at,
            ])),
            'created_at' => $this->created_at,
        ];
    }
}
