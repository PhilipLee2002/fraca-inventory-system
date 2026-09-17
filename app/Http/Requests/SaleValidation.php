<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class SaleValidation
{
    public static function apply(Validator $validator, FormRequest $request): void
    {
        $method = $request->input('payment_method', 'cash');
        if ($method === 'transfer' && ! trim((string) $request->input('reference_number'))) {
            $validator->errors()->add(
                'reference_number',
                'Enter the M-Pesa or Till reference so accounts can reconcile.'
            );
        }

        if (! $request->has('items') || $request->input('status') !== 'completed') {
            return;
        }

        $productIds = array_column($request->input('items'), 'product_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($request->input('items') as $index => $item) {
            $product = $products[$item['product_id']] ?? null;
            if ($product && $product->current_stock < $item['quantity']) {
                $validator->errors()->add(
                    "items.{$index}.quantity",
                    "Insufficient stock for {$product->name}. Available: {$product->current_stock}"
                );
            }
        }
    }
}
