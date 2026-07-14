@extends('admin.layouts.app')

@section('content')
<h1 class="text-3xl font-bold mb-6">Edit Product</h1>

<div class="bg-white shadow rounded-lg p-6 max-w-2xl">
    <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Name *</label>
            <input type="text" name="name" value="{{ $product->name }}" class="w-full border p-2 rounded focus:outline-none focus:border-blue-500" required>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Description</label>
            <textarea name="description" rows="4" class="w-full border p-2 rounded focus:outline-none focus:border-blue-500">{{ $product->description }}</textarea>
        </div>
        @php
            $isSubcategory = $product->category && $product->category->parent_id !== null;
            $mainCatId = $isSubcategory ? $product->category->parent_id : ($product->category_id ?? '');
            $subCatId = $isSubcategory ? $product->category_id : '';
        @endphp
        <div class="flex gap-4 mb-4">
            <div class="w-1/2">
                <label class="block text-gray-700 font-bold mb-2">Main Category</label>
                <select id="main_category" class="w-full border p-2 rounded focus:outline-none focus:border-blue-500">
                    <option value="">-- Select Category --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ $mainCatId == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-1/2" id="sub_category_wrapper" style="display: none;">
                <label class="block text-gray-700 font-bold mb-2">Sub Category</label>
                <select id="sub_category" class="w-full border p-2 rounded focus:outline-none focus:border-blue-500">
                    <option value="">-- Select Sub Category --</option>
                </select>
            </div>
        </div>
        <input type="hidden" name="category_id" id="final_category_id" value="{{ $product->category_id }}">
        <div class="flex gap-4 mb-4">
            <div class="w-1/2">
                <label class="block text-gray-700 font-bold mb-2">Unit</label>
                <select name="unit_id" class="w-full border p-2 rounded focus:outline-none focus:border-blue-500">
                    <option value="">-- Select Unit --</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" {{ $product->unit_id == $unit->id ? 'selected' : '' }}>{{ $unit->name }} {{ $unit->short_name ? '('.$unit->short_name.')' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-1/2">
                <label class="block text-gray-700 font-bold mb-2">Stock *</label>
                <input type="number" name="stock" value="{{ $product->stock }}" class="w-full border p-2 rounded focus:outline-none focus:border-blue-500" required>
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Price ($)</label>
            <input type="number" step="0.01" name="price" value="{{ $product->price }}" class="w-full border p-2 rounded focus:outline-none focus:border-blue-500">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Image</label>
            <input type="file" name="image" class="w-full border p-2 rounded focus:outline-none focus:border-blue-500" accept="image/*">
            @if($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" class="w-20 h-20 mt-2 rounded" alt="Current Image">
            @endif
        </div>
        <div class="mb-4">
            <label class="inline-flex items-center">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="form-checkbox h-5 w-5 text-blue-600" {{ $product->is_active ? 'checked' : '' }}>
                <span class="ml-2 text-gray-700">Active</span>
            </label>
        </div>
        <div class="flex items-center gap-4">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Update Product</button>
            <a href="{{ route('public.product.show', $product) }}" target="_blank" class="text-green-600 hover:text-green-800 font-medium flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                View Public Page
            </a>
        </div>
    </form>
</div>

<script>
    const categoriesData = @json($categories);
    const mainCategorySelect = document.getElementById('main_category');
    const subCategorySelect = document.getElementById('sub_category');
    const subCategoryWrapper = document.getElementById('sub_category_wrapper');
    const finalCategoryId = document.getElementById('final_category_id');
    const initialSubCatId = '{{ $subCatId }}';

    function updateFinalCategoryId() {
        if (subCategorySelect.value) {
            finalCategoryId.value = subCategorySelect.value;
        } else {
            finalCategoryId.value = mainCategorySelect.value;
        }
    }

    function populateSubcategories(selectedId, selectedSubId = '') {
        subCategorySelect.innerHTML = '<option value="">-- Select Sub Category --</option>';
        if (selectedId) {
            const category = categoriesData.find(c => c.id == selectedId);
            if (category && category.children && category.children.length > 0) {
                category.children.forEach(child => {
                    const option = document.createElement('option');
                    option.value = child.id;
                    option.textContent = child.name;
                    if (child.id == selectedSubId) {
                        option.selected = true;
                    }
                    subCategorySelect.appendChild(option);
                });
                subCategoryWrapper.style.display = 'block';
            } else {
                subCategoryWrapper.style.display = 'none';
            }
        } else {
            subCategoryWrapper.style.display = 'none';
        }
    }

    mainCategorySelect.addEventListener('change', function() {
        populateSubcategories(this.value);
        updateFinalCategoryId();
    });

    subCategorySelect.addEventListener('change', updateFinalCategoryId);

    // Initialize on page load
    if (mainCategorySelect.value) {
        populateSubcategories(mainCategorySelect.value, initialSubCatId);
    }
</script>
@endsection
