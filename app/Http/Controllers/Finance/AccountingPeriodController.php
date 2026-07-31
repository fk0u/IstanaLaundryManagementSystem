<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use App\Models\Journal;

class AccountingPeriodController extends Controller
{
    public function index()
    {
        $periods = AccountingPeriod::with(['closedBy', 'branch'])
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(15);

        return view('finance.periods.index', compact('periods'));
    }

    public function show($id)
    {
        $period = AccountingPeriod::with(['branch', 'closedBy'])->findOrFail($id);

        $journals = Journal::with(['journalLines', 'createdByUser'])
            ->where('accounting_period_id', $period->id)
            ->orderBy('date', 'desc')
            ->take(50)
            ->get();

        $allJournals = Journal::with('journalLines')
            ->where('accounting_period_id', $period->id)
            ->get();

        $totalDebit = 0;
        $totalCredit = 0;
        $draftCount = 0;
        $postedCount = 0;
        $reversedCount = 0;

        foreach ($allJournals as $journal) {
            if ($journal->status === 'draft') {
                $draftCount++;
            } elseif ($journal->status === 'posted') {
                $postedCount++;
            } elseif ($journal->status === 'reversed') {
                $reversedCount++;
            }

            foreach ($journal->journalLines as $line) {
                $totalDebit += (float) $line->debit;
                $totalCredit += (float) $line->credit;
            }
        }

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $data = [
            'period' => [
                'id' => $period->id,
                'year' => $period->year,
                'month' => $period->month,
                'month_name' => ($monthNames[$period->month] ?? $period->month) . ' ' . $period->year,
                'status' => $period->status,
                'closed_at' => $period->closed_at ? $period->closed_at->format('d M Y H:i') : null,
                'closed_by_name' => $period->closedBy?->name ?? '-',
                'branch_name' => $period->branch?->name ?? 'Global (Semua Cabang)',
            ],
            'summary' => [
                'total_journals' => $allJournals->count(),
                'posted_journals' => $postedCount,
                'draft_journals' => $draftCount,
                'reversed_journals' => $reversedCount,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
            ],
            'journals' => $journals->map(function ($j) {
                $jDebit = $j->journalLines->sum('debit');
                $jCredit = $j->journalLines->sum('credit');
                return [
                    'id' => $j->id,
                    'reference' => $j->reference ?? ('JRN-' . str_pad($j->id, 5, '0', STR_PAD_LEFT)),
                    'description' => $j->description ?? 'Jurnal Transaksi',
                    'date' => $j->date ? $j->date->format('d/m/Y') : '-',
                    'status' => $j->status,
                    'debit' => $jDebit,
                    'credit' => $jCredit,
                    'created_by' => $j->createdByUser?->name ?? '-',
                ];
            }),
        ];

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($data);
        }

        return view('finance.periods.show', array_merge($data, compact('period')));
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
