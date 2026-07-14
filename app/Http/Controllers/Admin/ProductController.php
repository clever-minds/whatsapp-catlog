<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Services\WhatsAppCatalogService;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Product::with(['category', 'unit'])->select('products.*');
            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->addColumn('image', function ($row) {
                    if ($row->image) {
                        $url = asset('storage/' . $row->image);
                        return '<img src="' . $url . '" class="w-10 h-10 rounded-full" alt="Product Image">';
                    }
                    return '<div class="w-10 h-10 rounded-full bg-gray-200"></div>';
                })
                ->addColumn('category', function ($row) {
                    return optional($row->category)->name ?? '-';
                })
                ->addColumn('stock_unit', function ($row) {
                    return $row->stock . ' ' . (optional($row->unit)->short_name ?? optional($row->unit)->name ?? '');
                })
                ->editColumn('price', function ($row) {
                    return '$' . number_format($row->price, 2);
                })
                ->editColumn('is_active', function ($row) {
                    $color = $row->is_active ? 'green' : 'red';
                    $text = $row->is_active ? 'Active' : 'Inactive';
                    return '<span class="relative inline-block px-3 py-1 font-semibold text-' . $color . '-900 leading-tight">
                                <span aria-hidden class="absolute inset-0 bg-' . $color . '-200 opacity-50 rounded-full"></span>
                                <span class="relative">' . $text . '</span>
                            </span>';
                })
                ->addColumn('action', function ($row) {
                    $showUrl = route('public.product.show', $row->id);
                    $editUrl = route('products.edit', $row->id);
                    $deleteUrl = route('products.destroy', $row->id);

                    return '
                        <a href="' . $showUrl . '" target="_blank" class="text-green-600 hover:text-green-900 mr-3" title="View Public Page">
                            <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </a>
                        <a href="' . $editUrl . '" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                        <button type="button" onclick="openDeleteModal(\'' . $deleteUrl . '\')" class="text-red-600 hover:text-red-900">Delete</button>
                    ';
                })
                ->rawColumns(['image', 'is_active', 'action'])
                ->make(true);
        }
        return view('admin.products.index');
    }

    public function create()
    {
        $categories = Category::with('children')->whereNull('parent_id')->where('is_active', true)->get();
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
            // Save in storage/app/public/products
            $path = $request->file('image')->store('products', 'public');
    
            // Copy to public/storage/products
            $source = storage_path('app/public/' . $path);
            $destination = public_path('storage/' . $path);
    
            if (!File::exists(dirname($destination))) {
                File::makeDirectory(dirname($destination), 0755, true);
            }
    
            File::copy($source, $destination);
    
            $data['image'] = $path;
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
        $categories = Category::with('children')->whereNull('parent_id')->where('is_active', true)->get();
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
            // Save in storage/app/public/products
            $path = $request->file('image')->store('products', 'public');
    
            // Copy to public/storage/products
            $source = storage_path('app/public/' . $path);
            $destination = public_path('storage/' . $path);
    
            if (!File::exists(dirname($destination))) {
                File::makeDirectory(dirname($destination), 0755, true);
            }
    
            File::copy($source, $destination);
    
            $data['image'] = $path;
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
        // Delete from WhatsApp Catalog first
        try {
            $whatsappService = new WhatsAppCatalogService();
            $whatsappService->deleteProduct($product);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('WhatsApp Delete Failed: ' . $e->getMessage());
        }

        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted');
    }
}
