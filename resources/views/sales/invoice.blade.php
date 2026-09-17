<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $sale->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 20px; margin: 0 0 4px; color: #7a1f2b; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f4eef0; }
        .right { text-align: right; }
        .meta { width: 100%; border: 0; margin-top: 12px; }
        .meta td { border: 0; padding: 2px 0; }
        .total { font-weight: bold; }
        .footer { margin-top: 28px; font-size: 11px; color: #666; }
    </style>
</head>
<body>
    <h1>{{ $company['name'] }}</h1>
    <div class="muted">{{ $company['address'] }}</div>
    <div class="muted">{{ $company['phone'] }} · {{ $company['email'] }}</div>

    <table class="meta">
        <tr>
            <td><strong>Invoice</strong><br>{{ $sale->invoice_number }}</td>
            <td><strong>Date</strong><br>{{ optional($sale->sale_date)->format('d M Y') ?? $sale->created_at->format('d M Y') }}</td>
            <td><strong>Status</strong><br>{{ ucfirst($sale->status) }}</td>
        </tr>
        <tr>
            <td>
                <strong>Customer</strong><br>
                @if($sale->customer)
                    {{ trim(($sale->customer->first_name ?? '').' '.($sale->customer->last_name ?? '')) ?: ($sale->customer->name ?? 'Customer') }}
                @else
                    Walk-in
                @endif
            </td>
            <td>
                <strong>Payment</strong><br>
                @php
                    $method = $sale->payment_method;
                    $label = $method === 'transfer' ? 'M-Pesa' : ($method === 'bank' ? 'Bank transfer' : ucfirst($method ?? 'Cash'));
                @endphp
                {{ $label }}
                @if($sale->reference_number)
                    <br>Ref: {{ $sale->reference_number }}
                @endif
            </td>
            <td>
                <strong>Served by</strong><br>
                {{ $sale->user->name ?? '—' }}
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="right">Qty</th>
                <th class="right">Unit price</th>
                <th class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
                <tr>
                    <td>{{ $item->product->name ?? 'Item' }}</td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">KSh {{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">KSh {{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td colspan="3" class="right">Total</td>
                <td class="right">KSh {{ number_format($sale->total_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    @if($sale->notes)
        <p><strong>Notes:</strong> {{ $sale->notes }}</p>
    @endif

    <p class="footer">Thank you for your business. This is not a KRA eTIMS tax invoice.</p>
</body>
</html>
