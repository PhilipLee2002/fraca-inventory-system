<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;

class StoreProductRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true; // Route middleware handles permission:create-product
    }

    public function rules(): array
    {
        return [
            'name'          => 'required|string|max:255|unique:products,name',
            'sku'           => 'nullable|string|max:100|unique:products,sku',
            'barcode'       => 'nullable|string|max:100|unique:products,barcode',
            'description'   => 'nullable|string',
            'category_id'   => 'required|exists:categories,id',
            'supplier_id'   => 'nullable|exists:suppliers,id',
            'cost_price'    => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0|gte:cost_price',
            'current_stock' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'is_active'     => 'boolean',
            'is_in_house'   => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'selling_price.gte' => 'Selling price must be greater than or equal to cost price.',
        ];
    }
}
