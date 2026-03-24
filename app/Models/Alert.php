<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'type', 'message', 'is_read'];

    protected $casts = ['is_read' => 'boolean'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Create or update an alert for a product, avoiding duplicates.
     */
    public static function createForProduct(Product $product, string $type): void
    {
        // Only create if no unread alert of this type already exists for this product
        self::firstOrCreate(
            ['product_id' => $product->id, 'type' => $type, 'is_read' => false],
            ['message' => self::buildMessage($product, $type)]
        );
    }

    private static function buildMessage(Product $product, string $type): string
    {
        return match ($type) {
            'out_of_stock' => "Out of stock: {$product->name} has 0 units remaining",
            'low_stock'    => "Low stock: {$product->name} has {$product->current_stock} units (reorder level: {$product->reorder_level})",
            default        => "Alert for {$product->name}",
        };
    }
}
