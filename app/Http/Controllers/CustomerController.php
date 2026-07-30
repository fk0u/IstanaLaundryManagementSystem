<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->query('q', ''));

        $customersQuery = Customer::with(['branch', 'latestOrder', 'orders' => function ($ordQ) {
            $ordQ->orderBy('created_at', 'desc')->take(10);
        }])
            ->withCount('orders')
            ->withSum('orders', 'total')
            ->when($q !== '', function ($query) use ($q) {
                $like = "%{$q}%";
                $query->where(function ($sub) use ($like) {
                    $sub->where('name', 'LIKE', $like)
                        ->orWhere('phone', 'LIKE', $like)
                        ->orWhere('member_code', 'LIKE', $like);
                });
            })
            ->orderBy('name', 'asc');

        $customers = $customersQuery->paginate(10)->withQueryString();

        return view('customers.index', compact('customers', 'q'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:customers,phone',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $branchId = session('scoped_branch_id') ?? auth()->user()->branch_id ?? Branch::first()->id;

        Customer::create([
            'branch_id' => $branchId,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'member_code' => 'CUST-'.now()->format('Ymd').'-'.strtoupper(Str::random(4)),
            'loyalty_tier' => 'Bronze',
            'loyalty_points' => 0,
        ]);

        return redirect()->back()->with('success', 'Pelanggan baru berhasil didaftarkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:customers,phone,'.$id,
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'loyalty_tier' => 'required|in:Bronze,Silver,Gold,Platinum',
            'loyalty_points' => 'required|integer|min:0',
        ]);

        $customer = Customer::findOrFail($id);
        $customer->update($request->only('name', 'phone', 'email', 'address', 'loyalty_tier', 'loyalty_points'));

        return redirect()->back()->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return redirect()->back()->with('success', 'Pelanggan berhasil dihapus.');
    }

    public function exportCsv(Request $request)
    {
        $q = trim($request->query('q', ''));

        $customers = Customer::with(['latestOrder'])
            ->withCount('orders')
            ->withSum('orders', 'total')
            ->when($q !== '', function ($query) use ($q) {
                $like = "%{$q}%";
                $query->where(function ($sub) use ($like) {
                    $sub->where('name', 'LIKE', $like)
                        ->orWhere('phone', 'LIKE', $like)
                        ->orWhere('member_code', 'LIKE', $like);
                });
            })
            ->orderBy('name', 'asc')
            ->get();

        $fileName = 'laporan-crm-pelanggan-' . now()->format('Ymd-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($q, $customers) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['ISTANA LAUNDRY ERP — REKAPITULASI PELANGGAN & CRM']);
            fputcsv($file, ['Kata Kunci Pencarian: ' . ($q !== '' ? $q : 'Semua Pelanggan'), 'Tanggal Cetak: ' . now()->format('d/m/Y H:i')]);
            fputcsv($file, []);

            fputcsv($file, [
                'KODE MEMBER',
                'NAMA PELANGGAN',
                'NOMOR HP / WA',
                'EMAIL',
                'ALAMAT',
                'LOYALTY TIER',
                'POIN LOYALITAS',
                'TOTAL TRANSAKSI (NOTA)',
                'TOTAL BELANJA (RP)',
                'NOTA TERAKHIR',
                'TANGGAL TRANSAKSI TERAKHIR'
            ]);

            $totalSpentSum = 0;
            $totalOrdersCount = 0;

            foreach ($customers as $c) {
                $totalSpent = $c->orders_sum_total ?? $c->total_spent ?? 0;
                $totalSpentSum += $totalSpent;
                $totalOrdersCount += $c->orders_count;

                fputcsv($file, [
                    $c->member_code,
                    $c->name,
                    $c->phone,
                    $c->email ?? '-',
                    $c->address ?? '-',
                    $c->loyalty_tier,
                    $c->loyalty_points,
                    $c->orders_count,
                    number_format($totalSpent, 2, '.', ''),
                    $c->latestOrder ? '#' . $c->latestOrder->order_number : '-',
                    $c->latestOrder ? $c->latestOrder->created_at->format('d/m/Y H:i') : '-'
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['', 'TOTAL KESELURUHAN PELANGGAN: ' . $customers->count(), '', '', '', '', '', $totalOrdersCount, number_format($totalSpentSum, 2, '.', '')]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $q = trim($request->query('q', ''));

        $customers = Customer::with(['latestOrder'])
            ->withCount('orders')
            ->withSum('orders', 'total')
            ->when($q !== '', function ($query) use ($q) {
                $like = "%{$q}%";
                $query->where(function ($sub) use ($like) {
                    $sub->where('name', 'LIKE', $like)
                        ->orWhere('phone', 'LIKE', $like)
                        ->orWhere('member_code', 'LIKE', $like);
                });
            })
            ->orderBy('name', 'asc')
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.crm_pdf', compact('q', 'customers'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('laporan-crm-pelanggan-' . now()->format('Ymd-His') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $q = trim($request->query('q', ''));

        $customers = Customer::with(['latestOrder'])
            ->withCount('orders')
            ->withSum('orders', 'total')
            ->when($q !== '', function ($query) use ($q) {
                $like = "%{$q}%";
                $query->where(function ($sub) use ($like) {
                    $sub->where('name', 'LIKE', $like)
                        ->orWhere('phone', 'LIKE', $like)
                        ->orWhere('member_code', 'LIKE', $like);
                });
            })
            ->orderBy('name', 'asc')
            ->get();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\GenericViewExport('exports.crm_pdf', compact('q', 'customers'), 'CRM Pelanggan'),
            'laporan-crm-pelanggan-' . now()->format('Ymd-His') . '.xlsx'
        );
    }
}
