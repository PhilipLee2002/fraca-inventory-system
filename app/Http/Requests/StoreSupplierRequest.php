<?php

namespace App\Http\Requests;

class StoreSupplierRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Route middleware handles permission:create-supplier
    }

    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255|unique:suppliers,email',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string',
        ];
    }
}
