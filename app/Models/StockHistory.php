<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\StockHistory
 *
 * @property int $id
 * @property int $product_id
 * @property int $quantity_change
 * @property string $transaction_type
 * @property int|null $reference_id
 * @property string|null $notes
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \App\Models\Product $product
 */
class StockHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'quantity_change', 'transaction_type',
        'previous_quantity', 'new_quantity',
        'reference_id', 'reference_type', 'notes',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'reference_id')
            ->where('transaction_type', 'purchase');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'reference_id')
            ->where('transaction_type', 'sale');
    }
}

