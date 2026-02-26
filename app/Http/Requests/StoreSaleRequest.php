<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class StoreSaleRequest extends ApiRequest
{
    public function authorize()
    {
        return $this->user()->can('create', \App\Models\Sale::class);
    }

    public function rules()
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'sale_date' => 'required|date',
            'invoice_number' => 'nullable|string|max:100',
            'payment_method' => 'required|in:cash,card,transfer',
            'status' => 'required|in:pending,completed,cancelled',
            'notes' => 'nullable|string',
            
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('items')) {
                $productIds = array_column($this->input('items'), 'product_id');
                // Fetch all products at once and key by ID for easy lookup
                $products = \App\Models\Product::whereIn('id', $productIds)->get()->keyBy('id');

                foreach ($this->input('items') as $index => $item) {
                    if (isset($products[$item['product_id']])) {
                        $product = $products[$item['product_id']];
                        if ($product->current_stock < $item['quantity']) {
                            $validator->errors()->add(
                                "items.$index.quantity",
                                "Insufficient stock for {$product->name}. Available: {$product->current_stock}"
                            );
                        }
                    }
                }
            }
        });
    }
}
