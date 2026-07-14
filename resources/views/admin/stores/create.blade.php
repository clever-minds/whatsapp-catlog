@extends('admin.layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Add Store</h1>
    <a href="{{ route('stores.index') }}" class="text-gray-600 hover:text-gray-900 font-medium">&larr; Back to Stores</a>
</div>

<div class="bg-white shadow rounded-lg p-6 max-w-2xl">
    <form action="{{ route('stores.store') }}" method="POST">
        @csrf
        
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Store Name *</label>
            <input type="text" name="name" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. Downtown Store">
        </div>
        
        <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-1">
                <label class="block text-gray-700 font-bold mb-2">Country Code</label>
                <input type="text" name="country_code" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="+91" maxlength="5">
            </div>
            <div class="md:col-span-2">
                <label class="block text-gray-700 font-bold mb-2">WhatsApp Mobile Number</label>
                <input type="text" name="mobile_number" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. 9876543210" pattern="[0-9]+" title="Only numbers are allowed">
            </div>
        </div>
        
        <div class="mb-6">
            <label class="block text-gray-700 font-bold mb-2">Address</label>
            <textarea name="address" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Store address..."></textarea>
        </div>
        
        <div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded transition-colors">
                Save Store
            </button>
        </div>
    </form>
</div>
@endsection
