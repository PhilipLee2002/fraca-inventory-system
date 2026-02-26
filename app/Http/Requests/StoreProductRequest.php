<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;

class StoreProductRequest extends ApiRequest
{
    public function authorize()
    {
        return $this->user()->can('create', \App\Models\Product::class);
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:products,name',
            'sku' => 'required|string|max:100|unique:products,sku',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0|gte:cost_price',
            'reorder_level' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'selling_price.gte' => 'Selling price must be greater than or equal to cost price.',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            // Stock validations removed - now handled in controller
        });
    }
}
