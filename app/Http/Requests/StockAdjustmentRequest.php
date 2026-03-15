<?php

namespace App\Http\Requests;

class StockAdjustmentRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true; // Route middleware handles permission:manage-stock
    }

    public function rules(): array
    {
        return [
            'product_id'      => 'required|exists:products,id',
            'quantity_change' => 'required|integer|not_in:0',
            'reason'          => 'required|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'quantity_change.not_in' => 'Quantity change cannot be zero.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled('product_id') && $this->filled('quantity_change')) {
                $product = \App\Models\Product::find($this->input('product_id'));
                if ($product && $this->input('quantity_change') < 0) {
                    $abs = abs($this->input('quantity_change'));
                    if ($product->current_stock < $abs) {
                        $validator->errors()->add(
                            'quantity_change',
                            "Cannot remove {$abs} units. Only {$product->current_stock} in stock."
                        );
                    }
                }
            }
        });
    }
}
