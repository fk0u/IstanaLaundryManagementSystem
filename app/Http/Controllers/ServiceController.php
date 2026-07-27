<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Service;
use App\Models\ServiceBranchPrice;
use App\Models\ServicePriceHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::with('branchPrices.branch')
            ->orderBy('name')
            ->paginate(15);

        $branches = Branch::orderBy('name')->get();

        return view('services.index', compact('services', 'branches'));
    }

    public function store(Request $request)
    {
        $valid = $request->validate([
            'name'                => 'required|string|max:255|unique:services,name',
            'type'                => 'required|in:kilogram,satuan,kategori',
            'unit'                => 'required|string|max:10',
            'base_price'          => 'required|numeric|min:0',
            'est_duration_hours'  => 'required|integer|min:1',
            'description'         => 'nullable|string',
            'is_active'           => 'nullable|boolean',
            'branch_prices'       => 'nullable|array',
            'branch_prices.*'     => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($valid, $request) {
            $service = Service::create([
                'name'               => $valid['name'],
                'type'               => $valid['type'],
                'unit'               => $valid['unit'],
                'base_price'         => $valid['base_price'],
                'est_duration_hours' => $valid['est_duration_hours'],
                'description'        => $valid['description'] ?? null,
                'is_active'          => $request->boolean('is_active', true),
            ]);

            $now = Carbon::now();
            $userId = Auth::id();
            $branchPrices = $valid['branch_prices'] ?? [];

            foreach ($branchPrices as $branchId => $price) {
                if ($price === null || $price === '') {
                    continue;
                }
                $branchId = (int) $branchId;

                ServiceBranchPrice::updateOrCreate(
                    ['service_id' => $service->id, 'branch_id' => $branchId],
                    ['price' => $price, 'is_active' => true]
                );

                ServicePriceHistory::create([
                    'service_id' => $service->id,
                    'branch_id'  => $branchId,
                    'old_price'  => 0,
                    'new_price'  => $price,
                    'changed_by' => $userId,
                    'changed_at' => $now,
                ]);
            }
        });

        return redirect()->route('services.index')->with('success', 'Jenis layanan baru berhasil dibuat!');
    }

    public function update(Request $request, Service $service)
    {
        $valid = $request->validate([
            'name'                => 'required|string|max:255|unique:services,name,' . $service->id,
            'type'                => 'required|in:kilogram,satuan,kategori',
            'unit'                => 'required|string|max:10',
            'base_price'          => 'required|numeric|min:0',
            'est_duration_hours'  => 'required|integer|min:1',
            'description'         => 'nullable|string',
            'is_active'           => 'nullable|boolean',
            'branch_prices'       => 'nullable|array',
            'branch_prices.*'     => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($valid, $request, $service) {
            $oldBasePrice = $service->base_price;
            $newBasePrice = $valid['base_price'];

            $service->update([
                'name'               => $valid['name'],
                'type'               => $valid['type'],
                'unit'               => $valid['unit'],
                'base_price'         => $newBasePrice,
                'est_duration_hours' => $valid['est_duration_hours'],
                'description'        => $valid['description'] ?? null,
                'is_active'          => $request->boolean('is_active', true),
            ]);

            $now = Carbon::now();
            $userId = Auth::id();

            if ((float) $oldBasePrice !== (float) $newBasePrice) {
                $globalBranchId = session('scoped_branch_id') ?? auth()->user()?->branch_id;

                ServicePriceHistory::create([
                    'service_id' => $service->id,
                    'branch_id'  => $globalBranchId,
                    'old_price'  => $oldBasePrice,
                    'new_price'  => $newBasePrice,
                    'changed_by' => $userId,
                    'changed_at' => $now,
                ]);
            }

            $branchPrices = $valid['branch_prices'] ?? [];

            foreach ($branchPrices as $branchId => $price) {
                $branchId = (int) $branchId;

                $existing = ServiceBranchPrice::where('service_id', $service->id)
                    ->where('branch_id', $branchId)
                    ->first();

                $oldPrice = $existing?->price ?? 0;

                if ($price === null || $price === '') {
                    if ($existing) {
                        $existing->delete();
                    }
                    continue;
                }

                ServiceBranchPrice::updateOrCreate(
                    ['service_id' => $service->id, 'branch_id' => $branchId],
                    ['price' => $price, 'is_active' => true]
                );

                if ((float) $oldPrice !== (float) $price) {
                    ServicePriceHistory::create([
                        'service_id' => $service->id,
                        'branch_id'  => $branchId,
                        'old_price'  => $oldPrice,
                        'new_price'  => $price,
                        'changed_by' => $userId,
                        'changed_at' => $now,
                    ]);
                }
            }
        });

        return redirect()->route('services.index')->with('success', 'Data layanan berhasil diperbarui!');
    }

    public function toggleActive(Service $service)
    {
        $service->is_active = ! $service->is_active;
        $service->save();

        $label = $service->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('services.index')->with('success', "Layanan \"{$service->name}\" berhasil {$label}.");
    }
}
