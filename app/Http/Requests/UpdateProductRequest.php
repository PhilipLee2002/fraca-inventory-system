<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class UpdateProductRequest extends ApiRequest
{
    public function authorize()
    {
        return $this->user()->can('update', $this->route('product'));
    }

    public function rules()
    {
        $productId = $this->route('product')->id;

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('products')->ignore($productId),
            ],
            'sku' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($productId),
            ],
            'description' => 'nullable|string',
            'category_id' => 'sometimes|exists:categories,id',
            'supplier_id' => 'sometimes|exists:suppliers,id',
            'cost_price' => 'sometimes|numeric|min:0',
            'selling_price' => 'sometimes|numeric|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'minimum_stock' => 'nullable|integer|min:0',
            'unit_of_measure' => 'sometimes|string|max:50',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'sometimes|boolean',
            'image' => 'nullable|image|max:2048',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            // Get the product model instance from the route
            $product = $this->route('product');

            // Determine final cost and selling prices by taking request input first,
            // falling back to the product's current value if not provided
            $costPrice = $this->input('cost_price', $product->cost_price);
            $sellingPrice = $this->input('selling_price', $product->selling_price);

            // Only run the check if one of the relevant fields is being updated
            if ($this->has('cost_price') || $this->has('selling_price')) {
                if (isset($sellingPrice) && isset($costPrice) && $sellingPrice < $costPrice) {
                    $validator->errors()->add(
                        'selling_price',
                        'Selling price must be greater than or equal to the cost price.'
                    );
                }
            }
        });
    }
}
