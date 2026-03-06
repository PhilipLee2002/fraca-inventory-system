<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Sale
 *
 * @property int $id
 * @property int $customer_id
 * @property int $user_id
 * @property string $invoice_number
 * @property \Carbon\Carbon|null $sale_date
 * @property float $total_amount
 * @property string $payment_method
 * @property string $status
 * @property string|null $notes
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \App\Models\Customer $customer
 * @property \App\Models\User $user
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\SaleItem> $items
 */
class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', 'user_id', 'invoice_number', 'total_amount',
        'payment_method', 'notes', 'status', 'sale_date', 'due_date',
        'reference_number', 'shipping_cost', 'tax_amount', 'discount_amount',
        'payment_status', 'created_by',
    ];
    public function customer()
{
    return $this->belongsTo(Customer::class);
}

public function user()
{
    return $this->belongsTo(User::class);
}

public function items()
{
    return $this->hasMany(SaleItem::class);
}

public function saleItems()
{
    return $this->hasMany(SaleItem::class);
}

}
