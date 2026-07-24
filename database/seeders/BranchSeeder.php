<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Workshop;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Branch 1: Samarinda Kota
        $branch1 = Branch::create([
            'code' => 'SMD01',
            'name' => 'Istana Laundry Samarinda Kota',
            'address' => 'Jl. Jend. Sudirman No. 10, Samarinda',
            'phone' => '081122334455',
            'email' => 'smd01@istanalaundry.com',
            'lat' => -0.49490000,
            'lng' => 117.14250000,
            'is_active' => true,
        ]);

        // Workshop for Branch 1
        Workshop::create([
            'branch_id' => $branch1->id,
            'name' => 'Workshop Utama Samarinda Kota',
            'address' => 'Jl. Jend. Sudirman No. 10 (Belakang), Samarinda',
            'is_active' => true,
        ]);

        Workshop::create([
            'branch_id' => $branch1->id,
            'name' => 'Workshop Express Samarinda Kota',
            'address' => 'Jl. Bhayangkara No. 5, Samarinda',
            'is_active' => true,
        ]);

        // Branch 2: Samarinda Ulu
        $branch2 = Branch::create([
            'code' => 'SMD02',
            'name' => 'Istana Laundry Samarinda Ulu',
            'address' => 'Jl. M. Yamin No. 25, Samarinda',
            'phone' => '081155667788',
            'email' => 'smd02@istanalaundry.com',
            'lat' => -0.48520000,
            'lng' => 117.13500000,
            'is_active' => true,
        ]);

        // Workshop for Branch 2
        Workshop::create([
            'branch_id' => $branch2->id,
            'name' => 'Workshop Utama Samarinda Ulu',
            'address' => 'Jl. M. Yamin No. 25 (Samping), Samarinda',
            'is_active' => true,
        ]);
    }
}
