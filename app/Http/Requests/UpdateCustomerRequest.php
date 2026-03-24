<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true; // Route middleware handles permission:edit-customer
    }

    public function rules(): array
    {
        $customerId = $this->route('customer')->id;

        return [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'email'      => ['nullable', 'email', 'max:255', Rule::unique('customers')->ignore($customerId)],
            'phone'      => 'nullable|string|max:20',
            'address'    => 'nullable|string',
            'notes'      => 'nullable|string',
            'is_active'  => 'sometimes|boolean',
        ];
    }
}

