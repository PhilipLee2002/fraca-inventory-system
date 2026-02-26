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
            Alert::firstOrCreate([
                'product_id' => $product->id,
                'alert_type' => 'low_stock',
                'resolved' => false
            ], [
                'message' => "Low stock alert: {$product->name} is below reorder level. Current: {$product->current_stock}, Reorder: {$product->reorder_level}",
                'priority' => 'medium',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // 2. Check for out of stock
        $outOfStockProducts = Product::where('current_stock', 0)->get();

        foreach ($outOfStockProducts as $product) {
            Alert::firstOrCreate([
                'product_id' => $product->id,
                'alert_type' => 'out_of_stock',
                'resolved' => false
            ], [
                'message' => "Out of stock: {$product->name} has 0 units in stock",
                'priority' => 'high',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // 3. Check for products that will expire soon (if you have expiry_date field)
        if (\Schema::hasColumn('products', 'expiry_date')) {
            $expiringSoon = Product::where('expiry_date', '>=', now())
                ->where('expiry_date', '<=', now()->addDays(7))
                ->where('current_stock', '>', 0)
                ->get();

            foreach ($expiringSoon as $product) {
                Alert::firstOrCreate([
                    'product_id' => $product->id,
                    'alert_type' => 'expiring_soon',
                    'resolved' => false
                ], [
                    'message' => "Product expiring soon: {$product->name} (Expires: {$product->expiry_date->format('Y-m-d')})",
                    'priority' => 'high',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

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