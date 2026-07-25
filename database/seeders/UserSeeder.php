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
            if (!$user->hasRole($userData['role'])) {
                $user->assignRole($userData['role']);
            }
        }
    }
}
