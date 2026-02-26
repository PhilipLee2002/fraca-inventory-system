<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Purchase
 *
 * @property int $id
 * @property int $supplier_id
 * @property int $user_id
 * @property string $purchase_number
 * @property \Carbon\Carbon|null $purchase_date
 * @property float $total_amount
 * @property string $status
 * @property string|null $notes
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \App\Models\Supplier $supplier
 * @property \App\Models\User $user
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\PurchaseItem> $items
 */
class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id', 'user_id', 'purchase_number', 'total_amount',
        'notes', 'status', 'purchase_date',
    ];
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
