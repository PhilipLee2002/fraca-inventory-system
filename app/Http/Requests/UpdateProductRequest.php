<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateProductRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true; // Route middleware handles permission:edit-product
    }

    public function rules(): array
    {
        $productId = $this->route('product')->id;

        return [
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('products')->ignore($productId)],
            'sku'  => ['sometimes', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($productId)],
            'barcode' => ['nullable', 'string', 'max:100', Rule::unique('products', 'barcode')->ignore($productId)],
            'description'   => 'nullable|string',
            'category_id'   => 'sometimes|exists:categories,id',
            'supplier_id'   => 'nullable|exists:suppliers,id',
            'cost_price'    => 'sometimes|numeric|min:0',
            'selling_price' => 'sometimes|numeric|min:0',
            'current_stock' => 'sometimes|integer|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'is_active'     => 'sometimes|boolean',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $product      = $this->route('product');
            $costPrice    = $this->input('cost_price', $product->cost_price);
            $sellingPrice = $this->input('selling_price', $product->selling_price);

            if (($this->has('cost_price') || $this->has('selling_price')) && $sellingPrice < $costPrice) {
                $validator->errors()->add('selling_price', 'Selling price must be >= cost price.');
            }
        });
    }
}
