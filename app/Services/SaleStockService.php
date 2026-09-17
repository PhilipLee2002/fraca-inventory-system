<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockHistory;

class SaleStockService
{
    /**
     * Decrease stock for each sale line. Call only when the sale becomes completed.
     */
    public function decrementFor(Sale $sale): void
    {
        $sale->loadMissing('items.product');

        foreach ($sale->items as $item) {
            $this->changeStock(
                $item->product,
                -$item->quantity,
                $sale,
                "Sale: {$sale->invoice_number}"
            );
        }
    }

    /**
     * Put stock back when a completed sale is cancelled, edited, or deleted.
     */
    public function incrementFor(Sale $sale, string $reason): void
    {
        $sale->loadMissing('items.product');

        foreach ($sale->items as $item) {
            $this->changeStock(
                $item->product,
                $item->quantity,
                $sale,
                $reason
            );
        }
    }

    /**
     * @param  array<int, array{product_id:int, quantity:float|int}>  $items
     */
    public function assertAvailable(array $items): void
    {
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            if (! $product || $product->current_stock < $item['quantity']) {
                throw new \Exception(
                    'Insufficient stock for product: '.($product->name ?? 'Unknown')
                );
            }
        }
    }

    public function isCompleted(?string $status): bool
    {
        return $status === 'completed';
    }

    /**
     * Apply stock movement when status changes (no line-item rewrite).
     */
    public function syncStatusChange(Sale $sale, string $oldStatus, string $newStatus): void
    {
        $wasCompleted = $this->isCompleted($oldStatus);
        $nowCompleted = $this->isCompleted($newStatus);

        if ($wasCompleted && ! $nowCompleted) {
            $this->incrementFor($sale, "Sale {$newStatus}: {$sale->invoice_number}");
        }

        if (! $wasCompleted && $nowCompleted) {
            $this->assertAvailable($sale->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
            ])->all());
            $this->decrementFor($sale);
        }
    }

    private function changeStock(Product $product, int|float $delta, Sale $sale, string $notes): void
    {
        $previous = $product->current_stock;
        $product->increment('current_stock', $delta);
        $product->refresh();

        StockHistory::create([
            'product_id' => $product->id,
            'quantity_change' => $delta,
            'previous_quantity' => $previous,
            'new_quantity' => $previous + $delta,
            'transaction_type' => $delta < 0 ? 'sale' : 'adjustment',
            'reference_id' => $sale->id,
            'reference_type' => Sale::class,
            'notes' => $notes,
        ]);

        if ($product->current_stock <= 0) {
            Alert::createForProduct($product, 'out_of_stock');
        } elseif ($product->current_stock <= $product->reorder_level) {
            Alert::createForProduct($product, 'low_stock');
        }
    }
}
