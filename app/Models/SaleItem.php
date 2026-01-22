<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    public function sale()
{
    return $this->belongsTo(Sale::class);
}

public function product()
{
    return $this->belongsTo(Product::class);
}

}
