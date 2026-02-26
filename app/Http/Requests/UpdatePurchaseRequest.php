<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdatePurchaseRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('purchase'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $purchaseId = $this->route('purchase')->id;

        return [
            'supplier_id' => 'sometimes|required|exists:suppliers,id',
            'purchase_date' => 'sometimes|required|date',
            'reference_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('purchases', 'reference_number')->ignore($purchaseId),
            ],
            'invoice_number' => 'nullable|string|max:100',
            'delivery_date' => 'sometimes|nullable|date|after_or_equal:purchase_date',
            'shipping_cost' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'status' => 'in:pending,received,partially_received,cancelled',

            'items' => 'sometimes|required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount' => 'nullable|numeric|min:0',
        ];
    }
}

