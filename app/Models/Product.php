<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Product
 *
 * @property int $id
 * @property string $name
 * @property string|null $sku
 * @property string|null $description
 * @property float $cost_price
 * @property float $selling_price
 * @property int $current_stock
 * @property int|null $reorder_level
 * @property int|null $category_id
 * @property int|null $supplier_id
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'sku', 'description', 'cost_price', 'selling_price',
        'current_stock', 'reorder_level', 'category_id', 'supplier_id',
        'is_active', 'is_in_house', 'barcode',
    ];
    public function category()
{
    return $this->belongsTo(Category::class);
}

public function supplier()
{
    return $this->belongsTo(Supplier::class);
}

public function purchaseItems()
{
    return $this->hasMany(PurchaseItem::class);
}

public function saleItems()
{
    return $this->hasMany(SaleItem::class);
}

public function stockHistories()
{
    return $this->hasMany(StockHistory::class);
}

public function alerts()
{
    return $this->hasMany(Alert::class);
}

// Helper method to log stock movements to StockHistory
public function logStockMovement($transactionType, $quantityChange, $notes = null)
{
    return $this->stockHistories()->create([
        'transaction_type' => $transactionType,
        'quantity_change' => $quantityChange,
        'previous_quantity' => $this->current_stock - $quantityChange,
        'new_quantity' => $this->current_stock,
        'notes' => $notes,
    ]);
}

}
