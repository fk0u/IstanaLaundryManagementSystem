<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use App\Models\Journal;
use Illuminate\Http\Request;

class AccountingPeriodController extends Controller
{
    public function index()
    {
        $periods = AccountingPeriod::with(['closedBy'])
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(15);

        return view('finance.periods.index', compact('periods'));
    }

    public function close($id)
    {
        $period = AccountingPeriod::findOrFail($id);

        if ($period->status === 'closed') {
            return redirect()->back()->with('error', 'Periode akuntansi sudah ditutup sebelumnya.');
        }

        // Validate that all journals in this period are posted or reversed (no drafts)
        $unpostedCount = Journal::where('accounting_period_id', $period->id)
            ->where('status', 'draft')
            ->count();

        if ($unpostedCount > 0) {
            return redirect()->back()->with('error', "Ada {$unpostedCount} jurnal draft yang belum diposting pada periode ini. Semua jurnal harus diposting sebelum penutupan.");
        }

        $period->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => auth()->id() ?? 1,
        ]);

        return redirect()->back()->with('success', "Periode akuntansi {$period->month}/{$period->year} berhasil ditutup.");
    }
}
