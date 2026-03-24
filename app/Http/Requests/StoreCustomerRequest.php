<?php

namespace App\Http\Requests;

class StoreCustomerRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true; // Route middleware handles permission:create-customer
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'email'      => 'nullable|email|max:255|unique:customers,email',
            'phone'      => 'nullable|string|max:20',
            'address'    => 'nullable|string',
            'notes'      => 'nullable|string',
            'is_active'  => 'boolean',
        ];
    }
}
