<x-app-layout>
    <div class="flex flex-col gap-6">
        <x-page-header title="Bagan Akun Keuangan (Chart of Accounts)" :breadcrumbs="['Keuangan' => '/finance']" />

        <x-card title="Bagan Akun Akuntansi (COA)">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Nomor Akun (Code)</th>
                            <th class="py-3 px-4">Nama Akun</th>
                            <th class="py-3 px-4">Tipe Akun</th>
                            <th class="py-3 px-4">Saldo Normal</th>
                            <th class="py-3 px-4">Sistem</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @forelse($coas as $coa)
                            @php
                                // indent by level
                                $padding = ($coa->level - 1) * 16;
                            @endphp
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-800 dark:text-slate-200">
                                    {{ $coa->code }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-800 dark:text-slate-200 text-sm" style="padding-left: {{ 16 + $padding }}px">
                                    @if ($coa->level > 1)
                                        <span class="text-slate-400 font-normal mr-1">└─</span>
                                    @endif
                                    <span class="{{ $coa->level === 1 ? 'font-extrabold text-primary' : 'font-semibold' }}">
                                        {{ $coa->name }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-650 dark:text-slate-400 uppercase font-bold text-[10px]">
                                    {{ $coa->type }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 font-bold uppercase text-[10px]">
                                    {{ $coa->normal_balance }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if ($coa->is_system)
                                        <x-badge type="info">System</x-badge>
                                    @else
                                        <x-badge type="gray">User</x-badge>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    @if ($coa->is_active)
                                        <x-badge type="success">Aktif</x-badge>
                                    @else
                                        <x-badge type="gray">Nonaktif</x-badge>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">Belum ada bagan akun yang di-seed.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $coas->links() }}
            </div>
        </x-card>
    </div>
</x-app-layout>
