<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Service;
use App\Models\ServiceBranchPrice;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'Cuci Kiloan Reguler',
                'type' => 'kilogram',
                'unit' => 'kg',
                'base_price' => 7000.00,
                'est_duration_hours' => 48,
                'description' => 'Cuci + lipat + setrika reguler 2 hari selesai',
            ],
            [
                'name' => 'Cuci Kiloan Kilat',
                'type' => 'kilogram',
                'unit' => 'kg',
                'base_price' => 12000.00,
                'est_duration_hours' => 24,
                'description' => 'Cuci + lipat + setrika kilat 1 hari selesai',
            ],
            [
                'name' => 'Cuci Kiloan Express',
                'type' => 'kilogram',
                'unit' => 'kg',
                'base_price' => 18000.00,
                'est_duration_hours' => 6,
                'description' => 'Cuci + lipat + setrika express 6 jam selesai',
            ],
            [
                'name' => 'Setrika Saja Kiloan',
                'type' => 'kilogram',
                'unit' => 'kg',
                'base_price' => 5000.00,
                'est_duration_hours' => 24,
                'description' => 'Setrika pakaian kiloan saja',
            ],
            [
                'name' => 'Cuci Kemeja',
                'type' => 'satuan',
                'unit' => 'pcs',
                'base_price' => 10000.00,
                'est_duration_hours' => 48,
                'description' => 'Cuci + setrika kemeja satuan',
            ],
            [
                'name' => 'Cuci Jas / Blazer',
                'type' => 'satuan',
                'unit' => 'pcs',
                'base_price' => 35000.00,
                'est_duration_hours' => 72,
                'description' => 'Cuci + setrika jas premium',
            ],
            [
                'name' => 'Cuci Gaun / Dress',
                'type' => 'satuan',
                'unit' => 'pcs',
                'base_price' => 25000.00,
                'est_duration_hours' => 72,
                'description' => 'Cuci + setrika gaun satuan',
            ],
            [
                'name' => 'Cuci Bed Cover King Size',
                'type' => 'satuan',
                'unit' => 'pcs',
                'base_price' => 30000.00,
                'est_duration_hours' => 48,
                'description' => 'Cuci + kering + lipat bed cover king size',
            ],
            [
                'name' => 'Cuci Sepatu Premium',
                'type' => 'kategori',
                'unit' => 'pcs',
                'base_price' => 45000.00,
                'est_duration_hours' => 96,
                'description' => 'Pencucian sepatu deep clean premium',
            ],
            [
                'name' => 'Dry Cleaning Jas Set',
                'type' => 'kategori',
                'unit' => 'set',
                'base_price' => 60000.00,
                'est_duration_hours' => 72,
                'description' => 'Dry cleaning jas set lengkap jas dan celana',
            ]
        ];

        // Seed Services
        $seededServices = [];
        foreach ($services as $serviceData) {
            $seededServices[] = Service::create($serviceData);
        }

        // Let's seed some price overrides for Branch 2 (Samarinda Ulu) to test scoping pricing overrides
        $branch2 = Branch::where('code', 'SMD02')->first();
        if ($branch2) {
            // SMD02 has slightly higher prices (e.g. +1000 or +5000)
            ServiceBranchPrice::create([
                'service_id' => $seededServices[0]->id, // Cuci Kiloan Reguler
                'branch_id' => $branch2->id,
                'price' => 8000.00, // base was 7000
                'is_active' => true,
            ]);

            ServiceBranchPrice::create([
                'service_id' => $seededServices[2]->id, // Cuci Kiloan Express
                'branch_id' => $branch2->id,
                'price' => 20000.00, // base was 18000
                'is_active' => true,
            ]);
        }
    }
}
