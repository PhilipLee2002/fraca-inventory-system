<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
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

}
