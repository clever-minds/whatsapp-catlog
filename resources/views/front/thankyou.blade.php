<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You - Order Confirmed</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
    </style>
</head>
<body class="antialiased flex items-center justify-center min-h-screen p-4">

    <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full overflow-hidden text-center">
        <div class="bg-green-500 py-8 px-4">
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-white mb-4">
                <svg class="h-10 w-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h1 class="text-3xl font-extrabold text-white">Order Submitted!</h1>
        </div>
        
        <div class="p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Thank You, {{ $order->customer_name ?? 'Customer' }}!</h2>
            <p class="text-gray-600 mb-6">Your order <span class="font-bold">#{{ $order->order_number }}</span> has been confirmed and was received successfully.</p>

            <p class="text-sm text-gray-500 mb-8">
                We have also sent you a confirmation on WhatsApp. We will process your order shortly.
            </p>

            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', env('WHATSAPP_PHONE_NUMBER', '')) }}" target="_blank" class="inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 shadow-sm transition-colors w-full">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                    <path fill-rule="evenodd" d="M12.003 2.012c-5.505 0-9.977 4.474-9.977 9.982 0 1.956.51 3.844 1.48 5.512l-1.427 5.21 5.333-1.398c1.62.887 3.456 1.354 5.344 1.354 5.503 0 9.975-4.473 9.975-9.981 0-5.507-4.472-9.98-9.975-9.98l-.253.301zm-5.068 7.378c-.287-.803-.59-82-.12-.818.156-.002.336 0 .498 0 .16 0 .422-.06.643.473.22.532.753 1.838.82 1.97.067.133.111.288.02.488-.088.2-.132.322-.266.478-.133.155-.282.342-.4.454-.132.133-.274.28-.12.546.155.267.69 1.139 1.481 1.84.811.723 1.666.966 1.933 1.099.266.133.422.11.577-.066.155-.178.666-.777.844-1.044.177-.267.355-.222.599-.133.244.088 1.554.733 1.82 866.267.133.4.178.4.266.023.238-.112 1.365-1.155 1.488-1.043.123-1.63-.585-2.222-1.92-5.748-1.577-6.035z" clip-rule="evenodd"/>
                </svg>
                Return to WhatsApp
            </a>
        </div>
    </div>

</body>
</html>
