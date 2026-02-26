<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockAdjustmentRequest extends ApiRequest
{
    public function authorize()
    {
        return $this->user()->can('adjust-stock', \App\Models\Product::class);
    }

    public function rules()
    {
        return [
            'product_id' => 'required|exists:products,id',
            'adjustment_type' => 'required|in:addition,subtraction,correction',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
            'reference' => 'nullable|string|max:100',
            'adjusted_date' => 'required|date',
            'notes' => 'nullable|string',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('product_id') && $this->has('adjustment_type') && $this->has('quantity')) {
                $product = \App\Models\Product::find($this->input('product_id'));
                
                if ($this->input('adjustment_type') === 'subtraction') {
                    $currentStock = $product->stock_quantity;
                    if ($currentStock < $this->input('quantity')) {
                        $validator->errors()->add(
                            'quantity',
                            "Cannot subtract more than available stock. Available: {$currentStock}"
                        );
                    }
                }
            }
        });
    }
}
