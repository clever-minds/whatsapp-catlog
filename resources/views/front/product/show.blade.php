<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->name }} - Product Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f9fafb; }
    </style>
</head>
<body class="antialiased text-gray-800">

    <div class="max-w-4xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden flex flex-col md:flex-row">
            
            <!-- Product Image -->
            <div class="md:w-1/2 bg-gray-100 flex items-center justify-center p-8">
                @if($product->image)
                    <img src="{{ asset('/public/storage/' . $product->image) }}" alt="{{ $product->name }}" class="max-w-full h-auto rounded-lg shadow-sm object-cover max-h-96">
                @else
                    <div class="text-gray-400 flex flex-col items-center">
                        <svg class="w-24 h-24 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span class="text-sm font-medium">No image available</span>
                    </div>
                @endif
            </div>

            <!-- Product Details -->
            <div class="md:w-1/2 p-8 md:p-12 flex flex-col justify-center">
                <div class="uppercase tracking-wide text-sm text-green-600 font-bold mb-2">
                    {{ $product->category ? $product->category->name : 'Uncategorized' }}
                </div>
                
                <h1 class="block mt-1 text-3xl leading-tight font-extrabold text-gray-900 mb-4">
                    {{ $product->name }}
                </h1>
                
                <div class="flex items-end gap-2 mb-6">
                </div>

                <div class="prose prose-sm text-gray-600 mb-8">
                    <p>{{ $product->description ?? 'No description provided for this product.' }}</p>
                </div>

                <div class="mt-auto">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-green-50 text-green-700 text-sm font-semibold border border-green-200">
                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        Available to order via WhatsApp
                    </div>
                </div>
            </div>

        </div>
        
        <div class="text-center mt-8 text-sm text-gray-500">
            &copy; {{ date('Y') }} WhatsApp Catalog Portal. All rights reserved.
        </div>
    </div>

</body>
</html>
