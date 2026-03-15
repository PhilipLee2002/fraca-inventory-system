<?php

namespace App\Http\Requests;

class StoreSaleRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true; // Route middleware handles permission:create-sale
    }

    public function rules(): array
    {
        return [
            'customer_id'          => 'nullable|exists:customers,id',
            'sale_date'            => 'required|date',
            'payment_method'       => 'nullable|in:cash,card,transfer',
            'status'               => 'required|in:pending,completed,cancelled',
            'notes'                => 'nullable|string',

            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.unit_price'   => 'required|numeric|min:0',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->has('items')) return;

            $productIds = array_column($this->input('items'), 'product_id');
            $products   = \App\Models\Product::whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($this->input('items') as $index => $item) {
                $product = $products[$item['product_id']] ?? null;
                if ($product && $product->current_stock < $item['quantity']) {
                    $validator->errors()->add(
                        "items.{$index}.quantity",
                        "Insufficient stock for {$product->name}. Available: {$product->current_stock}"
                    );
                }
            }
        });
    }
}
