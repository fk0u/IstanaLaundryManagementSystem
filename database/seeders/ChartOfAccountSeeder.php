<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $coaData = [
            // Level 1 Accounts
            ['code' => '1', 'name' => 'ASET', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 1, 'parent_code' => null],
            ['code' => '2', 'name' => 'LIABILITAS', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 1, 'parent_code' => null],
            ['code' => '3', 'name' => 'EKUITAS', 'type' => 'equity', 'normal_balance' => 'credit', 'level' => 1, 'parent_code' => null],
            ['code' => '4', 'name' => 'PENDAPATAN', 'type' => 'revenue', 'normal_balance' => 'credit', 'level' => 1, 'parent_code' => null],
            ['code' => '5', 'name' => 'BEBAN', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 1, 'parent_code' => null],

            // Level 2 Accounts
            // Assets
            ['code' => '1-1', 'name' => 'Aset Lancar', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 2, 'parent_code' => '1'],
            ['code' => '1-2', 'name' => 'Aset Tetap', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 2, 'parent_code' => '1'],
            // Liabilities
            ['code' => '2-1', 'name' => 'Liabilitas Jangka Pendek', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 2, 'parent_code' => '2'],
            ['code' => '2-2', 'name' => 'Liabilitas Jangka Panjang', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 2, 'parent_code' => '2'],
            // Expenses
            ['code' => '5-1', 'name' => 'Harga Pokok Penjualan', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 2, 'parent_code' => '5'],
            ['code' => '5-2', 'name' => 'Beban Operasional', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 2, 'parent_code' => '5'],
            ['code' => '5-3', 'name' => 'Beban Karyawan', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 2, 'parent_code' => '5'],
            ['code' => '5-4', 'name' => 'Beban Umum & Administrasi', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 2, 'parent_code' => '5'],

            // Level 3 Accounts
            // 1-1 Aset Lancar
            ['code' => '1-1101', 'name' => 'Kas Kecil', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '1-1'],
            ['code' => '1-1102', 'name' => 'Kas Bank BCA', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '1-1'],
            ['code' => '1-1103', 'name' => 'Kas Bank Mandiri', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '1-1'],
            ['code' => '1-1201', 'name' => 'Piutang Usaha', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '1-1'],
            ['code' => '1-1202', 'name' => 'Piutang Lain-lain', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '1-1'],
            ['code' => '1-1301', 'name' => 'Persediaan Bahan Habis Pakai', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '1-1'],
            ['code' => '1-1401', 'name' => 'Biaya Dibayar Di Muka', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '1-1'],
            
            // 1-2 Aset Tetap
            ['code' => '1-2101', 'name' => 'Mesin Cuci', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '1-2'],
            ['code' => '1-2102', 'name' => 'Mesin Pengering', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '1-2'],
            ['code' => '1-2103', 'name' => 'Peralatan Setrika', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '1-2'],
            ['code' => '1-2104', 'name' => 'Kendaraan', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '1-2'],
            ['code' => '1-2105', 'name' => 'Furniture & Perlengkapan', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '1-2'],
            ['code' => '1-2106', 'name' => 'Komputer & Perangkat IT', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '1-2'],
            ['code' => '1-2901', 'name' => 'Akum. Penyusutan Mesin Cuci', 'type' => 'asset', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '1-2'],
            ['code' => '1-2902', 'name' => 'Akum. Penyusutan Mesin Pengering', 'type' => 'asset', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '1-2'],
            ['code' => '1-2903', 'name' => 'Akum. Penyusutan Kendaraan', 'type' => 'asset', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '1-2'],
            ['code' => '1-2904', 'name' => 'Akum. Penyusutan Furniture', 'type' => 'asset', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '1-2'],

            // 2-1 Liabilitas Jangka Pendek
            ['code' => '2-1101', 'name' => 'Hutang Usaha', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '2-1'],
            ['code' => '2-1201', 'name' => 'Hutang Gaji', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '2-1'],
            
            // 2-2 Liabilitas Jangka Panjang
            ['code' => '2-2101', 'name' => 'Hutang PPN', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '2-2'],
            ['code' => '2-2102', 'name' => 'Hutang PPh 23', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '2-2'],
            ['code' => '2-2103', 'name' => 'Hutang Bank', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '2-2'],

            // 3 Ekuitas
            ['code' => '3-1101', 'name' => 'Modal Pemilik', 'type' => 'equity', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => null],
            ['code' => '3-1201', 'name' => 'Laba Ditahan', 'type' => 'equity', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => null],
            ['code' => '3-1301', 'name' => 'Laba/Rugi Tahun Berjalan', 'type' => 'equity', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => null],

            // 4 Pendapatan
            ['code' => '4-1001', 'name' => 'Pendapatan Jasa Laundry', 'type' => 'revenue', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => null],
            ['code' => '4-1002', 'name' => 'Pendapatan Jasa Ekspres', 'type' => 'revenue', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => null],
            ['code' => '4-1003', 'name' => 'Pendapatan Jasa Setrika', 'type' => 'revenue', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => null],
            ['code' => '4-1004', 'name' => 'Pendapatan Layanan Lain-lain', 'type' => 'revenue', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => null],
            ['code' => '4-2001', 'name' => 'Pendapatan Lain-lain', 'type' => 'revenue', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => null],

            // 5-1 Harga Pokok Penjualan
            ['code' => '5-1101', 'name' => 'COGS Bahan Detergen', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '5-1'],
            ['code' => '5-1102', 'name' => 'COGS Bahan Pelembut', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '5-1'],
            ['code' => '5-1103', 'name' => 'COGS Plastik/Kemasan', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '5-1'],

            // 5-2 Beban Operasional
            ['code' => '5-2101', 'name' => 'Beban Listrik', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '5-2'],
            ['code' => '5-2102', 'name' => 'Beban Air', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '5-2'],
            ['code' => '5-2103', 'name' => 'Beban Gas', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '5-2'],
            ['code' => '5-2104', 'name' => 'Beban Sewa Tempat', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '5-2'],
            ['code' => '5-2105', 'name' => 'Beban Telepon & Internet', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '5-2'],

            // 5-3 Beban Karyawan
            ['code' => '5-3101', 'name' => 'Beban Gaji', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '5-3'],
            ['code' => '5-3102', 'name' => 'Beban Tunjangan', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '5-3'],
            ['code' => '5-3103', 'name' => 'Beban BPJS', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '5-3'],

            // 5-4 Beban Umum & Administrasi
            ['code' => '5-4101', 'name' => 'Beban Penyusutan Aset', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '5-4'],
            ['code' => '5-4102', 'name' => 'Beban Pemeliharaan', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '5-4'],
            ['code' => '5-4103', 'name' => 'Beban Administrasi Bank', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '5-4'],
            ['code' => '5-4104', 'name' => 'Beban Pajak', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '5-4'],
            ['code' => '5-4105', 'name' => 'Beban Marketing & Promosi', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3, 'parent_code' => '5-4'],
        ];

        // Store code to id map for establishing parent relations
        $codeMap = [];

        // Insert in order of level to ensure parent exists
        usort($coaData, function ($a, $b) {
            return $a['level'] <=> $b['level'];
        });

        foreach ($coaData as $data) {
            $parentCode = $data['parent_code'];
            $parentId = $parentCode && isset($codeMap[$parentCode]) ? $codeMap[$parentCode] : null;

            $coa = ChartOfAccount::create([
                'parent_id' => $parentId,
                'code' => $data['code'],
                'name' => $data['name'],
                'type' => $data['type'],
                'normal_balance' => $data['normal_balance'],
                'level' => $data['level'],
                'is_active' => true,
                'is_system' => true, // Protected accounts
            ]);

            $codeMap[$data['code']] = $coa->id;
        }
    }
}
