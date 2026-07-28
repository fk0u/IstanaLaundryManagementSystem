<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SupplierSeeder extends Seeder
{
    /**
     * Data supplier contoh untuk kebutuhan pengadaan (Purchase Order).
     * Supplier bersifat lintas-cabang (tidak terikat branch_id), jadi
     * cukup seed sekali untuk seluruh aplikasi.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'PT Detergen Nusantara',
                'phone' => '0215551234',
                'email' => 'sales@detergennusantara.co.id',
                'address' => 'Jl. Industri Raya No. 12, Kawasan Industri Pulogadung, Jakarta Timur',
                'npwp' => '01.234.567.8-091.000',
            ],
            [
                'name' => 'CV Maju Jaya Kimia',
                'phone' => '0227778899',
                'email' => 'order@majujayakimia.com',
                'address' => 'Jl. Cibaduyut Lama No. 45, Bandung, Jawa Barat',
                'npwp' => '02.345.678.9-012.000',
            ],
            [
                'name' => 'PT Bersih Sejahtera',
                'phone' => '0318884455',
                'email' => 'info@bersihsejahtera.co.id',
                'address' => 'Jl. Raya Gresik No. 78, Surabaya, Jawa Timur',
                'npwp' => '03.456.789.0-123.000',
            ],
            [
                'name' => 'UD Sentosa Tekstil',
                'phone' => '02746661234',
                'email' => 'sentosatekstil@gmail.com',
                'address' => 'Jl. Magelang Km 8 No. 23, Yogyakarta',
                'npwp' => '04.567.890.1-234.000',
            ],
            [
                'name' => 'Toko Sumber Rezeki',
                'phone' => '081234567890',
                'email' => null,
                'address' => 'Pasar Tanah Abang Blok A No. 110, Jakarta Pusat',
                'npwp' => null,
            ],
        ];

        foreach ($suppliers as $data) {
            Supplier::firstOrCreate(
                ['name' => $data['name']],
                array_merge($data, ['is_active' => true])
            );
        }
    }
}
