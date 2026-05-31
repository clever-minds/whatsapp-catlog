@extends('admin.layouts.app')

@section('content')
<h1 class="text-3xl font-bold mb-6">Add Product</h1>

<div class="bg-white shadow rounded-lg p-6 max-w-2xl">
    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Name *</label>
            <input type="text" name="name" class="w-full border p-2 rounded focus:outline-none focus:border-blue-500" required>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Description</label>
            <textarea name="description" rows="4" class="w-full border p-2 rounded focus:outline-none focus:border-blue-500"></textarea>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Category</label>
            <select name="category_id" class="w-full border p-2 rounded focus:outline-none focus:border-blue-500">
                <option value="">-- Select Category --</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-4 mb-4">
            <div class="w-1/2">
                <label class="block text-gray-700 font-bold mb-2">Unit (e.g. kg, pcs)</label>
                <select name="unit_id" class="w-full border p-2 rounded focus:outline-none focus:border-blue-500">
                    <option value="">-- Select Unit --</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->name }} {{ $unit->short_name ? '('.$unit->short_name.')' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-1/2">
                <label class="block text-gray-700 font-bold mb-2">Stock *</label>
                <input type="number" name="stock" class="w-full border p-2 rounded focus:outline-none focus:border-blue-500" value="0" required>
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Price ($)</label>
            <input type="number" step="0.01" name="price" class="w-full border p-2 rounded focus:outline-none focus:border-blue-500">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Image</label>
            <input type="file" name="image" class="w-full border p-2 rounded focus:outline-none focus:border-blue-500" accept="image/*">
        </div>
        <div class="mb-4">
            <label class="inline-flex items-center">
                <input type="checkbox" name="is_active" value="1" class="form-checkbox h-5 w-5 text-blue-600" checked>
                <span class="ml-2 text-gray-700">Active</span>
            </label>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save Product</button>
    </form>
</div>
@endsection
