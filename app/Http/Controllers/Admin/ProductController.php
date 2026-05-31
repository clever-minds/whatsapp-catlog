<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Services\WhatsAppCatalogService;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with(['category', 'unit'])->latest()->paginate(10);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        $units = Unit::all();
        return view('admin.products.create', compact('categories', 'units'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'nullable|exists:units,id',
            'stock' => 'required|integer|min:0',
            'price' => 'nullable|numeric',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);
        
        // Sync with WhatsApp Catalog
        try {
            $whatsappService = new WhatsAppCatalogService();
            $whatsappService->syncProduct($product);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('WhatsApp Sync Failed: ' . $e->getMessage());
        }

        return redirect()->route('products.index')->with('success', 'Product created and synced successfully');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->get();
        $units = Unit::all();
        return view('admin.products.edit', compact('product', 'categories', 'units'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'nullable|exists:units,id',
            'stock' => 'required|integer|min:0',
            'price' => 'nullable|numeric',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        // Sync with WhatsApp Catalog
        try {
            $whatsappService = new WhatsAppCatalogService();
            $whatsappService->syncProduct($product);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('WhatsApp Sync Failed: ' . $e->getMessage());
        }

        return redirect()->route('products.index')->with('success', 'Product updated and synced successfully');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted');
    }
}
