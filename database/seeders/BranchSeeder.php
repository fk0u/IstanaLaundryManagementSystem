<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Workshop;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Menggunakan updateOrCreate dengan data outlet riil Istana Laundry Samarinda dan koordinat presisi tinggi.
     */
    public function run(): void
    {
        // Branch 1: Pusat - Wijaya Kusuma
        $branch1 = Branch::updateOrCreate(
            ['code' => 'WJK'],
            [
                'name' => 'Istana Laundry - Wijaya Kusuma (Pusat)',
                'address' => 'Jl. Wijaya Kusuma Blok V-C Gg. Rina, Air Hitam, Kec. Samarinda Ulu, Kota Samarinda, Kalimantan Timur 75117',
                'phone' => '08115550001',
                'email' => 'wjk@istanalaundry.com',
                'google_maps_url' => 'https://maps.app.goo.gl/vGYX4GPX8qbyVC5t6',
                'lat' => -0.48696232,
                'lng' => 117.12927615,
                'is_active' => true,
            ]
        );

        Workshop::updateOrCreate(
            ['branch_id' => $branch1->id, 'name' => 'Workshop Utama Wijaya Kusuma'],
            [
                'address' => 'Jl. Wijaya Kusuma Blok V-C Gg. Rina, Air Hitam, Kec. Samarinda Ulu, Kota Samarinda, Kalimantan Timur 75117',
                'is_active' => true,
            ]
        );

        // Branch 2: Dr Sutomo
        $branch2 = Branch::updateOrCreate(
            ['code' => 'SUT'],
            [
                'name' => 'Istana Laundry - Dr Sutomo',
                'address' => 'Jl. Dr. Sutomo, Sidodadi, Kec. Samarinda Ulu, Kota Samarinda, Kalimantan Timur 75243',
                'phone' => '08115550002',
                'email' => 'sutomo@istanalaundry.com',
                'google_maps_url' => 'https://maps.app.goo.gl/hfExyB2DF99JDhYr8',
                'lat' => -0.47985591,
                'lng' => 117.14684332,
                'is_active' => true,
            ]
        );

        Workshop::updateOrCreate(
            ['branch_id' => $branch2->id, 'name' => 'Workshop Dr Sutomo'],
            [
                'address' => 'Jl. Dr. Sutomo, Sidodadi, Kec. Samarinda Ulu, Kota Samarinda, Kalimantan Timur 75243',
                'is_active' => true,
            ]
        );

        // Branch 3: Pangeran Hidayatullah
        $branch3 = Branch::updateOrCreate(
            ['code' => 'HID'],
            [
                'name' => 'Istana Laundry - Pangeran Hidayatullah',
                'address' => 'Jl. Pangeran Hidayatullah, Karang Mumus, Kec. Samarinda Kota, Kota Samarinda, Kalimantan Timur 75242',
                'phone' => '08115550003',
                'email' => 'hidayatullah@istanalaundry.com',
                'google_maps_url' => 'https://maps.app.goo.gl/La8rGoQ6kxgtHrnEA',
                'lat' => -0.50231714,
                'lng' => 117.15582759,
                'is_active' => true,
            ]
        );

        Workshop::updateOrCreate(
            ['branch_id' => $branch3->id, 'name' => 'Workshop Pangeran Hidayatullah'],
            [
                'address' => 'Jl. Pangeran Hidayatullah, Karang Mumus, Kec. Samarinda Kota, Kota Samarinda, Kalimantan Timur 75242',
                'is_active' => true,
            ]
        );

        // Branch 4: Lambung Mangkurat
        $branch4 = Branch::updateOrCreate(
            ['code' => 'LMG'],
            [
                'name' => 'Istana Laundry - Lambung Mangkurat',
                'address' => 'Jl. Lambung Mangkurat, Sungai Pinang Dalam, Kec. Sungai Pinang, Kota Samarinda, Kalimantan Timur 75242',
                'phone' => '08115550004',
                'email' => 'lambung@istanalaundry.com',
                'google_maps_url' => 'https://maps.app.goo.gl/eAduH777U6U3mqj7A',
                'lat' => -0.48598488,
                'lng' => 117.16400973,
                'is_active' => true,
            ]
        );

        Workshop::updateOrCreate(
            ['branch_id' => $branch4->id, 'name' => 'Workshop Lambung Mangkurat'],
            [
                'address' => 'Jl. Lambung Mangkurat, Sungai Pinang Dalam, Kec. Sungai Pinang, Kota Samarinda, Kalimantan Timur 75242',
                'is_active' => true,
            ]
        );

        // Branch 5: Grand Taman Sari
        $branch5 = Branch::updateOrCreate(
            ['code' => 'GTS'],
            [
                'name' => 'Istana Laundry - Grand Taman Sari',
                'address' => 'Kawasan Perumahan Grand Taman Sari, Harapan Baru, Kec. Loa Janan Ilir, Kota Samarinda, Kalimantan Timur 75131',
                'phone' => '08115550005',
                'email' => 'gts@istanalaundry.com',
                'google_maps_url' => 'https://maps.app.goo.gl/zYeoDMZBqKdB1CUR8',
                'lat' => -0.56096529,
                'lng' => 117.11677339,
                'is_active' => true,
            ]
        );

        Workshop::updateOrCreate(
            ['branch_id' => $branch5->id, 'name' => 'Workshop Grand Taman Sari'],
            [
                'address' => 'Kawasan Perumahan Grand Taman Sari, Harapan Baru, Kec. Loa Janan Ilir, Kota Samarinda, Kalimantan Timur 75131',
                'is_active' => true,
            ]
        );
    }
}
