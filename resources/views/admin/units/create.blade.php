@extends('admin.layouts.app')

@section('content')
<h1 class="text-3xl font-bold mb-6">Add Unit</h1>

<div class="bg-white shadow rounded-lg p-6 max-w-lg">
    <form action="{{ route('units.store') }}" method="POST">
        @csrf
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Name * (e.g. Kilograms)</label>
            <input type="text" name="name" class="w-full border p-2 rounded focus:outline-none focus:border-blue-500" required>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Short Name (e.g. kg)</label>
            <input type="text" name="short_name" class="w-full border p-2 rounded focus:outline-none focus:border-blue-500">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save Unit</button>
    </form>
</div>
@endsection
