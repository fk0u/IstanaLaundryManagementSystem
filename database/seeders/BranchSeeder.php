<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Workshop;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Menggunakan firstOrCreate agar idempotent (aman dijalankan berulang kali).
     */
    public function run(): void
    {
        // Branch 1: Pusat - Wijaya Kusuma
        $branch1 = Branch::firstOrCreate(
            ['code' => 'WJK'],
            [
                'name' => 'Pusat - Wijaya Kusuma',
                'address' => 'Jl. Wijaya Kusuma Blok V-C Gg. Rina, Air Hitam, Kec. Samarinda Ulu, Kota Samarinda, Kalimantan Timur 75117',
                'phone' => '081100000001',
                'email' => 'pusat@istanalaundry.com',
                'lat' => -0.47890000,
                'lng' => 117.13250000,
                'is_active' => true,
            ]
        );

        Workshop::firstOrCreate(
            ['branch_id' => $branch1->id, 'name' => 'Workshop Utama Wijaya Kusuma'],
            [
                'address' => 'Jl. Wijaya Kusuma Blok V-C Gg. Rina, Air Hitam, Kec. Samarinda Ulu, Kota Samarinda, Kalimantan Timur 75117',
                'is_active' => true,
            ]
        );

        // Branch 2: Cabang Dr. Sutomo
        $branch2 = Branch::firstOrCreate(
            ['code' => 'SUT'],
            [
                'name' => 'Cabang Dr. Sutomo',
                'address' => 'Jl. Dr. Sutomo No.25, Sidodadi, Kec. Samarinda Ulu, Kota Samarinda, Kalimantan Timur 75243',
                'phone' => '081100000002',
                'email' => 'sutomo@istanalaundry.com',
                'lat' => -0.48520000,
                'lng' => 117.13500000,
                'is_active' => true,
            ]
        );

        Workshop::firstOrCreate(
            ['branch_id' => $branch2->id, 'name' => 'Workshop Dr. Sutomo'],
            [
                'address' => 'Jl. Dr. Sutomo No.25, Sidodadi, Kec. Samarinda Ulu, Kota Samarinda, Kalimantan Timur 75243',
                'is_active' => true,
            ]
        );

        // Branch 3: Cabang Pangeran Hidayatullah
        $branch3 = Branch::firstOrCreate(
            ['code' => 'HID'],
            [
                'name' => 'Cabang Pangeran Hidayatullah',
                'address' => 'Jl. Pangeran Hidayatullah, Karang Mumus, Kec. Samarinda Kota, Kota Samarinda, Kalimantan Timur 75242',
                'phone' => '081100000003',
                'email' => 'hidayatullah@istanalaundry.com',
                'lat' => -0.49520000,
                'lng' => 117.15100000,
                'is_active' => true,
            ]
        );

        Workshop::firstOrCreate(
            ['branch_id' => $branch3->id, 'name' => 'Workshop Pangeran Hidayatullah'],
            [
                'address' => 'Jl. Pangeran Hidayatullah, Karang Mumus, Kec. Samarinda Kota, Kota Samarinda, Kalimantan Timur 75242',
                'is_active' => true,
            ]
        );

        // Branch 4: Cabang Lambung Mangkurat
        $branch4 = Branch::firstOrCreate(
            ['code' => 'LMG'],
            [
                'name' => 'Cabang Lambung Mangkurat',
                'address' => 'Jl. Lambung Mangkurat, Sungai Pinang Dalam, Kec. Sungai Pinang, Kota Samarinda, Kalimantan Timur 75242',
                'phone' => '081100000004',
                'email' => 'lambung@istanalaundry.com',
                'lat' => -0.49200000,
                'lng' => 117.16200000,
                'is_active' => true,
            ]
        );

        Workshop::firstOrCreate(
            ['branch_id' => $branch4->id, 'name' => 'Workshop Lambung Mangkurat'],
            [
                'address' => 'Jl. Lambung Mangkurat, Sungai Pinang Dalam, Kec. Sungai Pinang, Kota Samarinda, Kalimantan Timur 75242',
                'is_active' => true,
            ]
        );
    }
}
