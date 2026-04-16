<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Route middleware handles permission:edit-supplier
    }

    public function rules(): array
    {
        $supplierId = $this->route('supplier')->id;

        return [
            'name'           => 'sometimes|required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email'          => ['sometimes', 'nullable', 'email', 'max:255', Rule::unique('suppliers')->ignore($supplierId)],
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string',
        ];
    }
}

