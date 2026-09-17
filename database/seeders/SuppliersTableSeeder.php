<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SuppliersTableSeeder extends Seeder
{
    public function run(): void
    {
        Supplier::updateOrCreate(
            ['email' => 'purchasing@fracaservcomltd.co.ke'],
            [
                'name' => 'External vendor',
                'contact_person' => 'Purchasing',
                'phone' => '+254725151495',
                'address' => 'Musco Towers, Eldoret',
            ]
        );
    }
}
