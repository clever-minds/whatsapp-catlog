<!DOCTYPE html>
<html>
<head>
    <title>New Order</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4caf50; color: white; padding: 10px 20px; text-align: center; }
        .details { margin: 20px 0; border: 1px solid #ddd; padding: 15px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background: #f4f4f4; }
        .total { text-align: right; font-weight: bold; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>New Order Received (#{{ $order->order_number }})</h2>
        </div>
        
        <div class="details">
            <p><strong>Customer Phone:</strong> {{ $order->customer_phone }}</p>
            <p><strong>Order Status:</strong> {{ ucfirst($order->status) }}</p>
            <p><strong>Store:</strong> {{ $order->store ? $order->store->name : 'N/A' }}</p>
        </div>

        <h3>Order Items:</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product ? $item->product->name : 'Unknown Product' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>${{ number_format($item->unit_price, 2) }}</td>
                    <td>${{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="total">
            @if($order->tax_amount > 0)
                <p>Tax ({{ $order->tax_amount }}%): ${{ number_format($order->total_amount - ($order->total_amount / (1 + ($order->tax_amount / 100))), 2) }}</p>
            @endif
            <p style="font-size: 1.2em;">Total: ${{ number_format($order->total_amount, 2) }}</p>
        </div>
        
        <p style="margin-top: 30px; font-size: 0.9em; color: #777;">
            Please log in to your admin panel to process this order and send the payment link.
        </p>
    </div>
</body>
</html>
