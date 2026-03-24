<?php
// app/Console/Commands/GenerateStockAlerts.php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Alert;
use App\Models\User;
use Illuminate\Console\Command;

class GenerateStockAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate stock alerts for low stock and expiring items';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating stock alerts...');

        // 1. Check for low stock (below reorder level but not zero)
        $lowStockProducts = Product::whereColumn('current_stock', '<=', 'reorder_level')
            ->where('current_stock', '>', 0)
            ->get();

        foreach ($lowStockProducts as $product) {
            Alert::firstOrCreate(
                ['product_id' => $product->id, 'type' => 'low_stock', 'is_read' => false],
                ['message' => "Low stock: {$product->name} has {$product->current_stock} units (reorder level: {$product->reorder_level})"]
            );
        }

        // 2. Check for out of stock
        $outOfStockProducts = Product::where('current_stock', 0)->get();

        foreach ($outOfStockProducts as $product) {
            Alert::firstOrCreate(
                ['product_id' => $product->id, 'type' => 'out_of_stock', 'is_read' => false],
                ['message' => "Out of stock: {$product->name} has 0 units remaining"]
            );
        }

        // 3. Check for products that will expire soon (if you have expiry_date field)
        // Not implemented — products table has no expiry_date column

        $this->info('Alerts generated successfully.');
        
        // Display summary
        $this->table(
            ['Alert Type', 'Count'],
            [
                ['Low Stock', $lowStockProducts->count()],
                ['Out of Stock', $outOfStockProducts->count()]
            ]
        );
    }
}