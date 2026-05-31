@extends('admin.layouts.app')

@section('content')
<h1 class="text-3xl font-bold mb-6">Edit Category</h1>

<div class="bg-white shadow rounded-lg p-6 max-w-lg">
    <form action="{{ route('categories.update', $category) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Name *</label>
            <input type="text" name="name" value="{{ $category->name }}" class="w-full border p-2 rounded focus:outline-none focus:border-blue-500" required>
        </div>
        <div class="mb-4">
            <label class="inline-flex items-center">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="form-checkbox h-5 w-5 text-blue-600" {{ $category->is_active ? 'checked' : '' }}>
                <span class="ml-2 text-gray-700">Active</span>
            </label>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Update Category</button>
    </form>
</div>
@endsection
