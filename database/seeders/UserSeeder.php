<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branchWjk = Branch::where('code', 'WJK')->first();
        $branchSut = Branch::where('code', 'SUT')->first();
        $branchHid = Branch::where('code', 'HID')->first();
        $branchLmg = Branch::where('code', 'LMG')->first();

        $users = [
            // Super level users (no branch scoping restriction)
            [
                'name' => 'System Developer',
                'email' => 'developer@istanalaundry.com',
                'password' => 'password',
                'branch_id' => null,
                'role' => 'Developer',
            ],
            [
                'name' => 'Istana Laundry Owner',
                'email' => 'owner@istanalaundry.com',
                'password' => 'password',
                'branch_id' => null,
                'role' => 'Owner',
            ],
            [
                'name' => 'Super Administrator',
                'email' => 'superadmin@istanalaundry.com',
                'password' => 'password',
                'branch_id' => null,
                'role' => 'Super_Admin',
            ],

            // Branch WJK (Pusat - Wijaya Kusuma)
            [
                'name' => 'Branch Admin WJK',
                'email' => 'admin.wjk@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchWjk->id,
                'role' => 'Branch_Admin',
            ],
            [
                'name' => 'Cashier WJK',
                'email' => 'cashier.wjk@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchWjk->id,
                'role' => 'Cashier',
            ],
            [
                'name' => 'Workshop Admin WJK',
                'email' => 'workshop.admin.wjk@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchWjk->id,
                'role' => 'Workshop_Admin',
            ],
            [
                'name' => 'Workshop Staff WJK',
                'email' => 'staff.wjk@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchWjk->id,
                'role' => 'Workshop_Staff',
            ],
            [
                'name' => 'CS Marketing WJK',
                'email' => 'marketing.wjk@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchWjk->id,
                'role' => 'CS_Marketing',
            ],
            [
                'name' => 'Finance WJK',
                'email' => 'finance.wjk@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchWjk->id,
                'role' => 'Finance',
            ],

            // Branch SUT (Cabang Dr. Sutomo)
            [
                'name' => 'Branch Admin SUT',
                'email' => 'admin.sut@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchSut->id,
                'role' => 'Branch_Admin',
            ],
            [
                'name' => 'Cashier SUT',
                'email' => 'cashier.sut@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchSut->id,
                'role' => 'Cashier',
            ],
            [
                'name' => 'Workshop Staff SUT',
                'email' => 'staff.sut@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchSut->id,
                'role' => 'Workshop_Staff',
            ],

            // Branch HID (Cabang Pangeran Hidayatullah)
            [
                'name' => 'Branch Admin HID',
                'email' => 'admin.hid@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchHid->id,
                'role' => 'Branch_Admin',
            ],
            [
                'name' => 'Cashier HID',
                'email' => 'cashier.hid@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchHid->id,
                'role' => 'Cashier',
            ],
            [
                'name' => 'Workshop Staff HID',
                'email' => 'staff.hid@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchHid->id,
                'role' => 'Workshop_Staff',
            ],

            // Branch LMG (Cabang Lambung Mangkurat)
            [
                'name' => 'Branch Admin LMG',
                'email' => 'admin.lmg@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchLmg->id,
                'role' => 'Branch_Admin',
            ],
            [
                'name' => 'Cashier LMG',
                'email' => 'cashier.lmg@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchLmg->id,
                'role' => 'Cashier',
            ],
            [
                'name' => 'Workshop Staff LMG',
                'email' => 'staff.lmg@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branchLmg->id,
                'role' => 'Workshop_Staff',
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

            // Assign role if not already assigned
            if (! $user->hasRole($userData['role'])) {
                $user->assignRole($userData['role']);
            }

            // Position mapping based on Spatie role
            $positionName = match ($userData['role']) {
                'Cashier' => 'Kasir Utama',
                'Workshop_Staff' => 'Operator Workshop',
                'Workshop_Admin' => 'Admin Workshop',
                'Branch_Admin' => 'Admin Cabang',
                'Finance' => 'Staf Keuangan',
                'CS_Marketing' => 'Staf CS & Marketing',
                'Super_Admin' => 'Super Administrator',
                'Owner' => 'Pemilik Utama',
                'Developer' => 'Developer Sistem',
                default => 'Staf Operational',
            };

            $branchId = $userData['branch_id'] ?? $branchWjk->id;

            \App\Models\Employee::withoutGlobalScopes()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nik' => 'NIK-STF-'.str_pad($user->id, 4, '0', STR_PAD_LEFT),
                    'name' => $user->name,
                    'position' => $positionName,
                    'branch_id' => $branchId,
                    'base_salary' => match ($userData['role']) {
                        'Developer', 'Owner' => 15000000.00,
                        'Super_Admin', 'Finance' => 8000000.00,
                        'Branch_Admin', 'Workshop_Admin' => 5000000.00,
                        'Cashier' => 3200000.00,
                        default => 2800000.00,
                    },
                    'phone' => '0812'.rand(10000000, 99999999),
                    'bank_name' => 'Bank BCA',
                    'bank_account_number' => '8830'.rand(100000, 999999),
                    'bank_account_holder' => $user->name,
                    'is_active' => true,
                    'joined_at' => now()->subMonths(rand(3, 24))->toDateString(),
                ]
            );
        }
    }
}
