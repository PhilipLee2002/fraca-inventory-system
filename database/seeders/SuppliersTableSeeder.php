<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SuppliersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $suppliers = [
            [
                'name' => 'Tech Supplies Inc.',
                'contact_person' => 'John Doe',
                'email' => 'john@techsupplies.com',
                'phone' => '123-456-7890',
                'address' => '123 Tech Street, City'
            ],
            [
                'name' => 'Office World',
                'contact_person' => 'Jane Smith',
                'email' => 'jane@officeworld.com',
                'phone' => '987-654-3210',
                'address' => '456 Office Ave, Town'
            ],
        ];
        
        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
    
}
