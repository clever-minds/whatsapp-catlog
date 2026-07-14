<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;

class PublicProductController extends Controller
{
    public function show(Product $product)
    {
        $product->load(['category', 'unit']);
        return view('front.product.show', compact('product'));
    }
}
