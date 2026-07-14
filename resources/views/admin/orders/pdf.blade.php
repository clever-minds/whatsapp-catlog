<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 14px;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #25D366;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            color: #15803d;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #777;
        }
        .details {
            width: 100%;
            margin-bottom: 30px;
        }
        .details td {
            vertical-align: top;
            width: 50%;
        }
        .box {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
        }
        .box h3 {
            margin-top: 0;
            font-size: 16px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 10px;
            color: #444;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table.items th {
            background-color: #f2f2f2;
            border-bottom: 2px solid #ddd;
            padding: 10px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
        }
        table.items td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        table.items .text-right {
            text-align: right;
        }
        table.items .text-center {
            text-align: center;
        }
        .totals {
            width: 100%;
        }
        .totals td {
            padding: 5px 10px;
        }
        .totals-row td:first-child {
            text-align: right;
            font-weight: bold;
            width: 75%;
        }
        .totals-row td:last-child {
            text-align: right;
        }
        .grand-total td {
            font-size: 18px;
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 10px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #888;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Order #{{ $order->order_number }}</h1>
        <p>Date: {{ $order->created_at->format('M d, Y h:i A') }}</p>
    </div>

    <table class="details">
        <tr>
            <td style="padding-right: 15px;">
                <div class="box">
                    <h3>Customer Information</h3>
                    <p><strong>Name:</strong> {{ $order->customer_name ?: 'N/A' }}</p>
                    <p><strong>Phone:</strong> {{ $order->customer_phone ?: 'N/A' }}</p>
                    <p><strong>Email:</strong> {{ $order->customer_email ?: 'N/A' }}</p>
                </div>
            </td>
            <td style="padding-left: 15px;">
                <div class="box">
                    <h3>Order Details</h3>
                    <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
                    <p><strong>Store:</strong> {{ $order->store ? $order->store->name : 'N/A' }}</p>
                    @if($order->stripe_session_id)
                        <p><strong>Payment ID:</strong> {{ $order->stripe_session_id }}</p>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $subtotal = 0; @endphp
            @foreach($order->items as $item)
                @php 
                    $price = $item->unit_price ?? ($item->product ? $item->product->price : 0);
                    $lineTotal = $price * $item->quantity;
                    $subtotal += $lineTotal;
                @endphp
                <tr>
                    <td>
                        <strong>{{ $item->product ? $item->product->name : 'Unknown Product' }}</strong>
                        @if($item->product && $item->product->description)
                            <br><span style="font-size: 11px; color: #666;">{{ \Illuminate\Support\Str::limit($item->product->description, 50) }}</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($price, 2) }}</td>
                    <td class="text-right">${{ number_format($lineTotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr class="totals-row">
            <td>Subtotal:</td>
            <td>${{ number_format($subtotal, 2) }}</td>
        </tr>
        
        @if($order->tax_amount > 0)
            <tr class="totals-row">
                <td>Tax ({{ $order->tax_name ?: 'Tax' }} @if($order->tax_type == 'percent') {{ floatval($order->tax_amount) }}% @endif):</td>
                @php
                    $taxVal = $order->tax_type == 'percent' ? ($subtotal * $order->tax_amount / 100) : $order->tax_amount;
                @endphp
                <td>${{ number_format($taxVal, 2) }}</td>
            </tr>
        @elseif($taxPercent > 0)
            <tr class="totals-row">
                <td>Tax ({{ $taxName ?: 'Tax' }} {{ floatval($taxPercent) }}%):</td>
                <td>${{ number_format(($subtotal * $taxPercent) / 100, 2) }}</td>
            </tr>
        @endif

        <tr class="totals-row grand-total">
            <td>Grand Total:</td>
            <td>${{ number_format($order->total_amount, 2) }}</td>
        </tr>
    </table>

    <div class="footer">
        Generated on {{ now()->format('M d, Y H:i:s') }}
    </div>

</body>
</html>
