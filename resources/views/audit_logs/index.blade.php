<x-app-layout>
    <div class="flex flex-col gap-6">
        <x-page-header title="Audit Trail & Log Aktivitas Global" :breadcrumbs="['Audit Logs' => '/audit-logs']" />

        <x-card title="Daftar Aktivitas Pengguna">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Tanggal & Waktu</th>
                            <th class="py-3 px-4">Pengguna</th>
                            <th class="py-3 px-4">Aksi</th>
                            <th class="py-3 px-4">Model & ID</th>
                            <th class="py-3 px-4">IP Address</th>
                            <th class="py-3 px-4">User Agent</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="py-4 px-4 text-slate-800 dark:text-slate-200 font-semibold whitespace-nowrap">
                                    {{ $log->created_at->format('d M Y H:i:s') }}
                                </td>
                                <td class="py-4 px-4 font-bold text-slate-800 dark:text-slate-200">
                                    {{ $log->user?->name ?? 'Guest / System' }}
                                    <span class="block text-[10px] text-slate-400 font-normal mt-0.5">{{ $log->user?->email ?? '-' }}</span>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2 py-1 rounded font-bold text-[10px] uppercase
                                        {{ Str::contains(strtolower($log->action), ['gagal', 'locked', 'delete']) 
                                           ? 'bg-red-50 text-red-600 dark:bg-red-950/20 dark:text-red-400' 
                                           : (Str::contains(strtolower($log->action), ['berhasil', 'create', 'update'])
                                              ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/20 dark:text-emerald-400'
                                              : 'bg-slate-100 text-slate-650 dark:bg-slate-800 dark:text-slate-400') }}">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-slate-600 dark:text-slate-400">
                                    <span class="font-bold">{{ class_basename($log->model_type) }}</span>
                                    @if ($log->model_id)
                                        <span class="text-slate-400">#{{ $log->model_id }}</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 font-mono font-semibold text-slate-500">
                                    {{ $log->ip_address }}
                                </td>
                                <td class="py-4 px-4 text-slate-450 dark:text-slate-500 max-w-[200px] truncate" title="{{ $log->user_agent }}">
                                    {{ $log->user_agent }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">Belum ada rekaman audit log terekam.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $logs->links() }}
            </div>
        </x-card>
    </div>
</x-app-layout>
