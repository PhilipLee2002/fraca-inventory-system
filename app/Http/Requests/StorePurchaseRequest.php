<?php

namespace App\Http\Requests;

class StorePurchaseRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true; // Route middleware handles permission:create-purchase
    }

    public function rules(): array
    {
        return [
            'supplier_id'          => 'required|exists:suppliers,id',
            'purchase_date'        => 'required|date',
            'payment_method'       => 'nullable|in:cash,card,transfer,credit',
            'status'               => 'required|in:pending,received,cancelled',
            'notes'                => 'nullable|string',

            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.unit_price'   => 'required|numeric|min:0',
        ];
    }
}
