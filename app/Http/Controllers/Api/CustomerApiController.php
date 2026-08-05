<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\LoyaltyPointLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('member_code', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('name', 'asc')->paginate($request->query('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $customers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:25|unique:customers,phone',
            'address' => 'nullable|string|max:500',
        ]);

        $branchId = session('scoped_branch_id') ?? auth()->user()?->branch_id ?? 1;

        $customer = Customer::create([
            'branch_id' => $branchId,
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'address' => $validated['address'] ?? null,
            'member_code' => 'MBR-'.strtoupper(Str::random(6)),
            'loyalty_tier' => 'Bronze',
            'loyalty_points' => 0,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Member baru berhasil didaftarkan!',
            'data' => $customer,
        ], 201);
    }

    public function show(Customer $customer)
    {
        $customer->load(['loyaltyPointLogs' => fn ($q) => $q->latest()->take(20)]);

        return response()->json([
            'status' => 'success',
            'data' => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:25|unique:customers,phone,'.$customer->id,
            'address' => 'nullable|string|max:500',
        ]);

        $customer->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Data member berhasil diperbarui!',
            'data' => $customer,
        ]);
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Member berhasil dihapus.',
        ]);
    }

    public function adjustPoints(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'points' => 'required|numeric',
            'type' => 'required|in:earn,spend,adjustment',
            'description' => 'required|string|max:255',
        ]);

        $points = (float) $validated['points'];
        $newTotal = $customer->loyalty_points + $points;

        $customer->update(['loyalty_points' => max(0, $newTotal)]);

        LoyaltyPointLog::create([
            'customer_id' => $customer->id,
            'points' => $points,
            'type' => $validated['type'],
            'description' => $validated['description'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Poin loyalitas berhasil disesuaikan!',
            'data' => [
                'customer_id' => $customer->id,
                'current_points' => $customer->loyalty_points,
            ],
        ]);
    }
}
