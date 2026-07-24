<x-app-layout>
    <div class="flex flex-col gap-6">
        <x-page-header title="Manajemen Karyawan & SDM" :breadcrumbs="['Karyawan' => '/hr']" />

        <x-card title="Daftar Staf Cabang & Penggajian">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Nama Lengkap</th>
                            <th class="py-3 px-4">NIK</th>
                            <th class="py-3 px-4">Jabatan</th>
                            <th class="py-3 px-4">Gaji Pokok</th>
                            <th class="py-3 px-4">Tanggal Masuk</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @forelse($employees as $employee)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="py-4 px-4 font-bold text-slate-800 dark:text-slate-200 text-sm">
                                    {{ $employee->name }}
                                </td>
                                <td class="py-4 px-4 font-mono text-slate-500">
                                    {{ $employee->nik }}
                                </td>
                                <td class="py-4 px-4 font-semibold text-slate-700 dark:text-slate-350">
                                    {{ $employee->position }}
                                </td>
                                <td class="py-4 px-4 font-bold text-slate-850 dark:text-slate-150">
                                    Rp {{ number_format($employee->base_salary, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4 text-slate-500">
                                    {{ \Carbon\Carbon::parse($employee->joined_at)->format('d M Y') }}
                                </td>
                                <td class="py-4 px-4">
                                    @if ($employee->is_active)
                                        <x-badge type="success">Aktif</x-badge>
                                    @else
                                        <x-badge type="gray">Nonaktif</x-badge>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">Belum ada data karyawan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $employees->links() }}
            </div>
        </x-card>
    </div>
</x-app-layout>
