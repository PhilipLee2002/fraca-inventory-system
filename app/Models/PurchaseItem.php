<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PurchaseItem
 *
 * @property int $id
 * @property int $purchase_id
 * @property int $product_id
 * @property int $quantity
 * @property float $unit_price
 * @property float $total
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \App\Models\Purchase $purchase
 * @property \App\Models\Product $product
 */
class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id', 'product_id', 'quantity', 'unit_price', 'total',
        'tax_rate', 'discount',
    ];
    public function purchase()
{
    return $this->belongsTo(Purchase::class);
}

public function product()
{
    return $this->belongsTo(Product::class);
}

}
