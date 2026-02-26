<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateSaleRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('sale'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $saleId = $this->route('sale')->id;

        return [
            'customer_id' => 'sometimes|required|exists:customers,id',
            'sale_date' => 'sometimes|required|date',
            'reference_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('sales', 'reference_number')->ignore($saleId),
            ],
            'invoice_number' => 'nullable|string|max:100',
            'due_date' => 'nullable|date|after_or_equal:sale_date',
            'shipping_cost' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',

            'items' => 'sometimes|required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount' => 'nullable|numeric|min:0',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $sale = $this->route('sale');

            // Validate stock availability when updating sale items
            if ($this->has('items')) {
                $productIds = array_column($this->input('items'), 'product_id');
                $products = \App\Models\Product::whereIn('id', $productIds)->get()->keyBy('id');
                $originalItems = $sale->items->keyBy('product_id');

                foreach ($this->input('items') as $index => $item) {
                    if (isset($products[$item['product_id']])) {
                        $product = $products[$item['product_id']];
                        $originalQty = $originalItems[$item['product_id']]->quantity ?? 0;
                        $quantityChange = $item['quantity'] - $originalQty;

                        // Only check stock if quantity has increased
                        if ($quantityChange > 0 && $product->stock_quantity < $quantityChange) {
                            $validator->errors()->add(
                                "items.$index.quantity",
                                "Insufficient stock to increase quantity for {$product->name}. Only {$product->stock_quantity} more available."
                            );
                        }
                    }
                }
            }
        });
    }
}

