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
 * @property string|null $website_slug
 * @property string|null $description
 * @property float $cost_price
 * @property float $selling_price
 * @property int $current_stock
 * @property int|null $reorder_level
 * @property int|null $category_id
 * @property int|null $supplier_id
 * @property string|null $image
 * @property bool $is_active
 * @property bool $is_in_house
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'sku', 'website_slug', 'description', 'cost_price', 'selling_price',
        'current_stock', 'reorder_level', 'category_id', 'supplier_id',
        'is_active', 'is_in_house', 'barcode', 'image',
    ];

    protected $appends = ['image_url'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_in_house' => 'boolean',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (blank($this->image)) {
            return null;
        }

        if (preg_match('#^https?://#i', $this->image)) {
            return $this->image;
        }

        $base = rtrim((string) config('app.website_asset_url'), '/');
        $segments = array_map('rawurlencode', explode('/', str_replace('\\', '/', $this->image)));

        return $base.'/'.implode('/', $segments);
    }
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
