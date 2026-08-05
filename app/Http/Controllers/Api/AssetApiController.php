<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\FixedAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetApiController extends Controller
{
    public function index(Request $request)
    {
        $assets = FixedAsset::with('branch')
            ->orderBy('name', 'asc')
            ->paginate($request->query('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $assets,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'asset_code' => 'required|string|unique:fixed_assets,asset_code',
            'category' => 'required|string|max:255',
            'acquisition_cost' => 'required|numeric|min:0',
            'salvage_value' => 'required|numeric|min:0',
            'useful_life_months' => 'required|integer|min:1',
            'depreciation_method' => 'required|in:straight_line,double_declining',
            'acquisition_date' => 'required|date',
        ]);

        $branchId = session('scoped_branch_id') ?? auth()->user()?->branch_id ?? 1;
        $coa = ChartOfAccount::where('type', 'asset')->first();

        $asset = FixedAsset::create([
            'branch_id' => $branchId,
            'account_id' => $coa?->id ?? 1,
            'asset_code' => strtoupper($validated['asset_code']),
            'name' => $validated['name'],
            'category' => $validated['category'],
            'acquisition_date' => $validated['acquisition_date'],
            'acquisition_cost' => $validated['acquisition_cost'],
            'salvage_value' => $validated['salvage_value'],
            'useful_life_months' => $validated['useful_life_months'],
            'depreciation_method' => $validated['depreciation_method'],
            'accumulated_depreciation' => 0,
            'book_value' => $validated['acquisition_cost'],
            'is_active' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Aset tetap berhasil didaftarkan!',
            'data' => $asset,
        ], 201);
    }

    public function show(FixedAsset $fixedAsset)
    {
        $fixedAsset->load(['branch', 'account']);

        return response()->json([
            'status' => 'success',
            'data' => $fixedAsset,
        ]);
    }

    public function update(Request $request, FixedAsset $fixedAsset)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        $fixedAsset->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Data aset tetap berhasil diperbarui!',
            'data' => $fixedAsset,
        ]);
    }

    public function depreciate(Request $request)
    {
        $activeAssets = FixedAsset::where('is_active', true)->where('book_value', '>', DB::raw('salvage_value'))->get();

        if ($activeAssets->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada aset aktif yang memerlukan kalkulasi depresiasi.',
            ], 422);
        }

        return DB::transaction(function () use ($activeAssets) {
            $totalDepreciated = 0;

            foreach ($activeAssets as $asset) {
                $monthlyDep = ($asset->acquisition_cost - $asset->salvage_value) / max(1, $asset->useful_life_months);
                $newAccum = min($asset->acquisition_cost - $asset->salvage_value, $asset->accumulated_depreciation + $monthlyDep);
                $newBookValue = max($asset->salvage_value, $asset->acquisition_cost - $newAccum);

                $asset->update([
                    'accumulated_depreciation' => $newAccum,
                    'book_value' => $newBookValue,
                ]);

                $totalDepreciated += $monthlyDep;
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Jurnal depresiasi bulanan berhasil dijalankan untuk ' . $activeAssets->count() . ' aset!',
                'data' => [
                    'assets_count' => $activeAssets->count(),
                    'total_depreciation_amount' => $totalDepreciated,
                ],
            ]);
        });
    }
}
