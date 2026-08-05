<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Service;
use App\Models\ServiceBranchPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterApiController extends Controller
{
    public function storeService(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:kilogram,satuan,kategori',
            'unit' => 'required|string|max:20',
            'base_price' => 'required|numeric|min:0',
            'est_duration_hours' => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:500',
        ]);

        $service = Service::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'unit' => $validated['unit'],
            'base_price' => $validated['base_price'],
            'est_duration_hours' => $validated['est_duration_hours'] ?? 24,
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Layanan laundry baru berhasil ditambahkan!',
            'data' => $service,
        ], 201);
    }

    public function updateService(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:kilogram,satuan,kategori',
            'unit' => 'required|string|max:20',
            'base_price' => 'required|numeric|min:0',
            'est_duration_hours' => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:500',
            'is_active' => 'required|boolean',
            'branch_prices' => 'nullable|array',
            'branch_prices.*.branch_id' => 'required|exists:branches,id',
            'branch_prices.*.price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated, $service) {
            $service->update([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'unit' => $validated['unit'],
                'base_price' => $validated['base_price'],
                'est_duration_hours' => $validated['est_duration_hours'] ?? 24,
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'],
            ]);

            if (! empty($validated['branch_prices'])) {
                foreach ($validated['branch_prices'] as $bp) {
                    ServiceBranchPrice::updateOrCreate(
                        [
                            'service_id' => $service->id,
                            'branch_id' => $bp['branch_id'],
                        ],
                        [
                            'price' => $bp['price'],
                        ]
                    );
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Layanan laundry berhasil diperbarui!',
                'data' => $service->load('branchPrices'),
            ]);
        });
    }

    public function storeBranch(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:branches,code',
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:25',
        ]);

        $branch = Branch::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'address' => $validated['address'],
            'phone' => $validated['phone'],
            'is_active' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Cabang outlet baru berhasil didaftarkan!',
            'data' => $branch,
        ], 201);
    }
}
