<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    public function supplier()
{
    return $this->belongsTo(Supplier::class);
}

public function user()
{
    return $this->belongsTo(User::class);
}

public function items()
{
    return $this->hasMany(PurchaseItem::class);
}

}
