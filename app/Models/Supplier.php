<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    public function products()
{
    return $this->hasMany(Product::class);
}

public function purchases()
{
    return $this->hasMany(Purchase::class);
}

}
