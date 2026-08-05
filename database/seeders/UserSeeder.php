<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds with Real Indonesian Names for all modules.
     */
    public function run(): void
    {
        if (! Branch::where('code', 'WJK')->exists()) {
            $this->call(BranchSeeder::class);
        }

        $branchWjk = Branch::where('code', 'WJK')->first();
        $branchSut = Branch::where('code', 'SUT')->first();
        $branchHid = Branch::where('code', 'HID')->first();
        $branchLmg = Branch::where('code', 'LMG')->first();

        $users = [
            // Super level users (no branch scoping restriction)
            [
                'name' => 'Rian Ardiansyah (Developer)',
                'email' => 'developer@istanalaundry.com',
                'password' => 'password',
                'branch_id' => null,
                'role' => 'Developer',
                'nik' => 'NIK-DEV-0001',
                'position' => 'Developer Utama',
                'salary' => 15000000.00,
            ],
            [
                'name' => 'H. Bambang Setiawan, S.E. (Owner)',
                'email' => 'owner@istanalaundry.com',
                'password' => 'password',
                'branch_id' => null,
                'role' => 'Owner',
                'nik' => 'NIK-OWN-0001',
                'position' => 'Pemilik Utama',
                'salary' => 20000000.00,
            ],
            [
                'name' => 'Siti Nurhaliza, M.M. (Super Admin)',
                'email' => 'superadmin@istanalaundry.com',
                'password' => 'password',
                'branch_id' => null,
                'role' => 'Super_Admin',
                'nik' => 'NIK-ADM-0001',
                'position' => 'Super Administrator',
                'salary' => 10000000.00,
            ],

            // Branch WJK (Pusat - Wijaya Kusuma)
            [
                'name' => 'Rahmat Hidayat',
                'email' => 'admin.wjk@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchWjk->id,
                'role' => 'Branch_Admin',
                'nik' => 'NIK-WJK-0001',
                'position' => 'Manager Cabang Wijaya Kusuma',
                'salary' => 5500000.00,
            ],
            [
                'name' => 'Dewi Anggraini',
                'email' => 'cashier.wjk@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchWjk->id,
                'role' => 'Cashier',
                'nik' => 'NIK-WJK-0002',
                'position' => 'Kasir Senior WJK',
                'salary' => 3500000.00,
            ],
            [
                'name' => 'Agus Prasetyo',
                'email' => 'workshop.admin.wjk@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchWjk->id,
                'role' => 'Workshop_Admin',
                'nik' => 'NIK-WJK-0003',
                'position' => 'Supervisor Workshop WJK',
                'salary' => 4500000.00,
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'staff.wjk@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchWjk->id,
                'role' => 'Workshop_Staff',
                'nik' => 'NIK-WJK-0004',
                'position' => 'Operator Cuci & Setrika',
                'salary' => 3000000.00,
            ],
            [
                'name' => 'Indah Permatasari',
                'email' => 'marketing.wjk@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchWjk->id,
                'role' => 'CS_Marketing',
                'nik' => 'NIK-WJK-0005',
                'position' => 'Staf CS & Marketing',
                'salary' => 3200000.00,
            ],
            [
                'name' => 'Sri Wahyuni, A.Md.',
                'email' => 'finance.wjk@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchWjk->id,
                'role' => 'Finance',
                'nik' => 'NIK-WJK-0006',
                'position' => 'Kepala Akuntan & Keuangan',
                'salary' => 7500000.00,
            ],

            // Branch SUT (Cabang Dr. Sutomo)
            [
                'name' => 'Eko Kurniawan',
                'email' => 'admin.sut@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchSut->id,
                'role' => 'Branch_Admin',
                'nik' => 'NIK-SUT-0001',
                'position' => 'Manager Cabang Dr. Sutomo',
                'salary' => 5000000.00,
            ],
            [
                'name' => 'Nia Ramadhani',
                'email' => 'cashier.sut@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchSut->id,
                'role' => 'Cashier',
                'nik' => 'NIK-SUT-0002',
                'position' => 'Kasir Utama Sutomo',
                'salary' => 3300000.00,
            ],
            [
                'name' => 'Dedi Kurnia',
                'email' => 'staff.sut@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchSut->id,
                'role' => 'Workshop_Staff',
                'nik' => 'NIK-SUT-0003',
                'position' => 'Operator Workshop Sutomo',
                'salary' => 2900000.00,
            ],

            // Branch HID (Cabang Pangeran Hidayatullah)
            [
                'name' => 'Fajar Nugraha',
                'email' => 'admin.hid@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchHid->id,
                'role' => 'Branch_Admin',
                'nik' => 'NIK-HID-0001',
                'position' => 'Manager Cabang Hidayatullah',
                'salary' => 5000000.00,
            ],
            [
                'name' => 'Rina Astuti',
                'email' => 'cashier.hid@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchHid->id,
                'role' => 'Cashier',
                'nik' => 'NIK-HID-0002',
                'position' => 'Kasir Utama Hidayatullah',
                'salary' => 3300000.00,
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'staff.hid@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchHid->id,
                'role' => 'Workshop_Staff',
                'nik' => 'NIK-HID-0003',
                'position' => 'Operator Workshop Hidayatullah',
                'salary' => 2900000.00,
            ],

            // Branch LMG (Cabang Lambung Mangkurat)
            [
                'name' => 'Hendra Kusuma',
                'email' => 'admin.lmg@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchLmg->id,
                'role' => 'Branch_Admin',
                'nik' => 'NIK-LMG-0001',
                'position' => 'Manager Cabang Lambung Mangkurat',
                'salary' => 5000000.00,
            ],
            [
                'name' => 'Maya Safitri',
                'email' => 'cashier.lmg@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchLmg->id,
                'role' => 'Cashier',
                'nik' => 'NIK-LMG-0002',
                'position' => 'Kasir Utama Lambung',
                'salary' => 3300000.00,
            ],
            [
                'name' => 'Rizky Febrian',
                'email' => 'staff.lmg@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchLmg->id,
                'role' => 'Workshop_Staff',
                'nik' => 'NIK-LMG-0003',
                'position' => 'Operator Workshop Lambung',
                'salary' => 2900000.00,
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'branch_id' => $userData['branch_id'],
                    'is_active' => true,
                ]
            );

            // Update name and properties if user already exists
            $user->update([
                'name' => $userData['name'],
                'is_active' => true,
            ]);

            // Assign role if not already assigned
            if (! $user->hasRole($userData['role'])) {
                $user->assignRole($userData['role']);
            }

            $branchId = $userData['branch_id'] ?? $branchWjk->id;

            \App\Models\Employee::withoutGlobalScopes()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nik' => $userData['nik'],
                    'name' => $userData['name'],
                    'position' => $userData['position'],
                    'branch_id' => $branchId,
                    'base_salary' => $userData['salary'],
                    'phone' => '0812'.rand(10000000, 99999999),
                    'bank_name' => 'Bank BCA',
                    'bank_account_number' => '8830'.rand(100000, 999999),
                    'bank_account_holder' => $userData['name'],
                    'is_active' => true,
                    'joined_at' => now()->subMonths(rand(3, 24))->toDateString(),
                ]
            );
        }
    }
}
