<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\FixedAsset;
use Illuminate\Http\Request;

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
}
