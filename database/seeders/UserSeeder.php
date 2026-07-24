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
        $branch1 = Branch::where('code', 'SMD01')->first();
        $branch2 = Branch::where('code', 'SMD02')->first();

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

            // Branch SMD01 Users
            [
                'name' => 'Branch Admin SMD01',
                'email' => 'admin.smd01@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branch1->id,
                'role' => 'Branch_Admin',
            ],
            [
                'name' => 'Cashier SMD01',
                'email' => 'cashier.smd01@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branch1->id,
                'role' => 'Cashier',
            ],
            [
                'name' => 'Workshop Admin SMD01',
                'email' => 'workshop.admin1@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branch1->id,
                'role' => 'Workshop_Admin',
            ],
            [
                'name' => 'Workshop Staff SMD01',
                'email' => 'staff.smd01@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branch1->id,
                'role' => 'Workshop_Staff',
            ],
            [
                'name' => 'CS Marketing SMD01',
                'email' => 'marketing.smd01@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branch1->id,
                'role' => 'CS_Marketing',
            ],
            [
                'name' => 'Finance SMD01',
                'email' => 'finance.smd01@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branch1->id,
                'role' => 'Finance',
            ],

            // Branch SMD02 Users
            [
                'name' => 'Branch Admin SMD02',
                'email' => 'admin.smd02@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branch2->id,
                'role' => 'Branch_Admin',
            ],
            [
                'name' => 'Cashier SMD02',
                'email' => 'cashier.smd02@istanalaundry.com',
                'password' => 'password',
                'branch_id' => $branch2->id,
                'role' => 'Cashier',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'branch_id' => $userData['branch_id'],
                'is_active' => true,
            ]);

            // Assign role
            $user->assignRole($userData['role']);
        }
    }
}
